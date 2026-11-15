<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Testing\TestResponse;
use Src\DataImport\Application\HasilSinkron;
use Src\DataImport\Application\KonfirmasiImporStatistik;
use Src\DataImport\Application\PratinjauImporStatistik;
use Src\DataImport\Application\SyncFormasiFromBkn;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Latihan\Application\MuatBankSoal;
use Src\Pengaturan\Application\InputKonfigurasiSmtp;
use Src\Pengaturan\Application\InputPengaturanEmail;
use Src\Pengaturan\Application\SimpanPengaturanEmail;
use Src\Pengaturan\Infrastructure\Mail\PembangunMailer;
use Tests\Support\PembangunMailerPalsu;
use Tests\TestCase;

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        $this->withoutVite();
        config([
            'queue.default' => 'sync',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'mail.default' => 'array',
            'hashing.bcrypt.rounds' => 4,
        ]);
        Sleep::fake();
    })
    ->in('Feature');

function masukSebagai(TestCase $uji, UserModel $pengguna, string $password = 'password'): void
{
    $uji->post('/masuk', ['email' => $pengguna->email, 'password' => $password])->assertSessionHasNoErrors();
    $uji->withCookie(config('session.cookie'), session()->getId());
}

function pasangMailerPalsu(array $gagalUntuk = []): PembangunMailerPalsu
{
    $palsu = new PembangunMailerPalsu($gagalUntuk);
    app()->instance(PembangunMailer::class, $palsu);

    return $palsu;
}

function konfigurasiEmailTersimpan(string $aktif = 'hostinger', ?string $cadangan = null): void
{
    $smtp = fn (string $host) => new InputKonfigurasiSmtp($host, 465, 'ssl', 'noreply@contoh.id', 'rahasia-smtp-123');

    $konfigurasi = [$aktif => $smtp('smtp.contoh.id')];

    if ($cadangan !== null) {
        $konfigurasi[$cadangan] = $smtp('smtp.cadangan.id');
    }

    app(SimpanPengaturanEmail::class)->handle(
        new InputPengaturanEmail($aktif, $cadangan, 'SICASN', 'noreply@contoh.id', null, $konfigurasi),
        1,
    );
}

function barisBkn(): array
{
    return json_decode((string) file_get_contents(__DIR__.'/Fixtures/bkn-cpns-sintetis.json'), true)['data'];
}

function fakeBkn(array $baris, int $status = 200): void
{
    Http::swap(new HttpFactory);
    Http::fake(['*' => Http::response(['status' => 200, 'error' => false, 'message' => 'success', 'data' => array_values($baris)], $status)]);
}

function fakeBknMentah(string $isi, int $status = 200, string $jenis = 'application/json'): void
{
    Http::swap(new HttpFactory);
    Http::fake(['*' => Http::response($isi, $status, ['Content-Type' => $jenis])]);
}

function sinkron(): HasilSinkron
{
    return app(SyncFormasiFromBkn::class)->handle(null);
}

function majukanWaktu(int $menit = 10): void
{
    Carbon::setTestNow(now()->addMinutes($menit));
}

function barisCpns(): array
{
    return array_slice(barisBkn(), 0, 5);
}

function jumlah(string $tabel, ?array $where = null): int
{
    $kueri = DB::table($tabel);

    foreach ($where ?? [] as $kolom => $nilai) {
        $kueri->where($kolom, $nilai);
    }

    return $kueri->count();
}

function berkasSementara(): int
{
    return count(glob(sys_get_temp_dir().'/bkn-*') ?: []);
}

function csvStatistik(array $baris): string
{
    $kolom = ['tahun', 'jenis_pengadaan', 'cakupan', 'instansi', 'kode_jabatan', 'nama_jabatan', 'lokasi', 'jumlah_formasi', 'jumlah_pelamar', 'jumlah_submit', 'jumlah_lolos_administrasi', 'tanggal_snapshot', 'status_angka', 'tingkat_data', 'keyakinan', 'sumber_nama', 'sumber_url', 'catatan'];
    $dasar = array_fill_keys($kolom, '') + [];
    $baku = ['tahun' => '2024', 'jenis_pengadaan' => 'cpns', 'cakupan' => 'instansi', 'tanggal_snapshot' => '2024-09-17', 'status_angka' => 'akhir', 'tingkat_data' => 'A', 'keyakinan' => 'turunan', 'sumber_nama' => 'Sumber Uji'];
    $aliran = fopen('php://temp', 'r+');
    fputcsv($aliran, $kolom, ',', '"', '');

    foreach ($baris as $butir) {
        $gabung = array_merge($dasar, $baku, $butir);
        fputcsv($aliran, array_map(fn (string $k) => $gabung[$k], $kolom), ',', '"', '');
    }

    rewind($aliran);

    return (string) stream_get_contents($aliran);
}

function muatStatistik(array $baris): int
{
    $pratinjau = app(PratinjauImporStatistik::class)->handle(csvStatistik($baris), 'uji.csv');

    return app(KonfirmasiImporStatistik::class)->handle((string) $pratinjau->token, null)->batchId;
}

function barisFormasi(string $kunci, array $ubah = []): array
{
    $dasar = barisBkn()[0];

    return array_merge($dasar, [
        'id' => sprintf('00000000-0000-4000-8100-%012d', crc32($kunci) % 1000000000000),
        'jabatan_kode' => 'JF-'.strtoupper(substr(md5($kunci), 0, 6)),
        'nama_jabatan' => $kunci,
    ], $ubah);
}

function siapkanFormasi(): void
{
    $baris = [
        barisFormasi('Penghulu Ahli Pertama', ['instansi' => 'Kementerian Agama', 'nama_pendidikan' => 'S-1 HUKUM / S-1 SYARIAH / D-IV HUKUM', 'lokasi_formasi' => 'Kab. Sleman', 'jumlah' => 3]),
        barisFormasi('Guru Madya', ['instansi' => 'Kementerian Agama', 'nama_pendidikan' => 'S-1 PENDIDIKAN BAHASA INDONESIA', 'lokasi_formasi' => 'Kota Malang', 'jumlah' => 10]),
        barisFormasi('Analis Kebijakan', ['instansi' => 'Kementerian Hukum dan HAM', 'nama_pendidikan' => 'D-IV SEMUA JURUSAN / S-1 SEMUA JURUSAN', 'lokasi_formasi' => 'Prov. Jawa Tengah', 'jumlah' => 20, 'jenis_formasi' => 'Disabilitas', 'is_disabilitas' => true]),
        barisFormasi('Statistisi Ahli Pertama', ['instansi' => 'Badan Pusat Statistik', 'nama_pendidikan' => 'S-1 STATISTIKA', 'lokasi_formasi' => 'Kab. Temanggung', 'jumlah' => 5]),
        barisFormasi('Pengelola Umum', ['instansi' => 'Kementerian Agama', 'nama_pendidikan' => null, 'lokasi_formasi' => 'Kab. Sleman', 'jumlah' => 2]),
        barisFormasi('Perawat Terampil', ['instansi' => 'Pemerintah Kabupaten Sleman', 'nama_pendidikan' => 'D-III KEPERAWATAN', 'lokasi_formasi' => 'Kab. Sleman', 'jumlah' => 7]),
    ];

    fakeBkn($baris);
    sinkron();
}

function siapkanStatistikFormasi(): void
{
    muatStatistik([
        ['instansi' => 'Kementerian Agama', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '2500'],
        ['instansi' => 'Kementerian Hukum dan HAM', 'jumlah_formasi' => '100', 'jumlah_pelamar' => '100'],
    ]);
}

function idFormasi(string $jabatan): int
{
    return (int) DB::table('formasis')->join('jabatans', 'jabatans.id', '=', 'formasis.jabatan_id')->where('jabatans.nama', $jabatan)->value('formasis.id');
}

function siapkanStatistikJabatan(): void
{
    muatStatistik([
        ['cakupan' => 'jabatan', 'instansi' => 'Kementerian Agama', 'nama_jabatan' => 'penghulu  ahli pertama', 'jumlah_formasi' => '30', 'jumlah_lolos_administrasi' => '600', 'tanggal_snapshot' => '2024-09-19', 'tingkat_data' => 'B', 'sumber_nama' => 'Pengumuman Uji B'],
        ['cakupan' => 'jabatan', 'instansi' => 'Kementerian Agama', 'nama_jabatan' => 'Guru Madya', 'lokasi' => 'Kota Surabaya', 'jumlah_formasi' => '10', 'jumlah_lolos_administrasi' => '90', 'tanggal_snapshot' => '2024-09-19', 'tingkat_data' => 'B', 'sumber_nama' => 'Pengumuman Uji B'],
        ['cakupan' => 'jabatan', 'instansi' => 'Kementerian Hukum dan HAM', 'nama_jabatan' => 'Analis Kebijakan', 'lokasi' => 'Prov. Jawa Tengah', 'jumlah_lolos_administrasi' => '50', 'tanggal_snapshot' => '2024-09-19', 'tingkat_data' => 'B', 'sumber_nama' => 'Pengumuman Uji B'],
    ]);
}

function barisSoal(string $kategori, int $nomor, int $posisiBenar = 0): array
{
    $label = ['A', 'B', 'C', 'D', 'E'];
    $skorTkp = [5, 4, 3, 2, 1];

    return [
        'kategori' => $kategori,
        'subkategori' => 'uji',
        'pertanyaan' => "Pertanyaan {$kategori} nomor {$nomor}",
        'pembahasan' => "Pembahasan {$kategori} nomor {$nomor}",
        'sumber_nama' => 'Buatan SICASN untuk uji',
        'sumber_url' => null,
        'lisensi' => 'Asli SICASN',
        'opsi' => array_map(fn (int $i) => [
            'label' => $label[$i],
            'teks' => "Opsi {$label[$i]} soal {$kategori} {$nomor}",
            'skor' => $kategori === 'tkp' ? $skorTkp[($i - $posisiBenar + 5) % 5] : ($i === $posisiBenar ? 5 : 0),
        ], range(0, 4)),
    ];
}

function muatSoalUji(int $twk = 40, int $tiu = 40, int $tkp = 50): void
{
    $baris = [];

    foreach (['twk' => $twk, 'tiu' => $tiu, 'tkp' => $tkp] as $kategori => $jumlah) {
        foreach (range(1, $jumlah) as $nomor) {
            $baris[] = barisSoal($kategori, $nomor, $nomor % 5);
        }
    }

    app(MuatBankSoal::class)->handle($baris);
}

function mulaiSebagai(string $mode = 'simulasi'): int
{
    test()->post('/latihan', ['mode' => $mode])->assertRedirect();

    return (int) DB::table('percobaans')->orderByDesc('id')->value('id');
}

function jawabJson(object $uji, string $url, array $data): TestResponse
{
    return $uji->put($url, $data, ['Accept' => 'application/json']);
}

function jawabSemua(int $percobaanId, string $strategi): void
{
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $percobaanId)->pluck('soal_id');

    foreach ($soal as $soalId) {
        $urut = DB::table('soal_opsis')->where('soal_id', $soalId);
        $opsi = match ($strategi) {
            'terbaik' => $urut->orderByDesc('skor')->first(),
            'terburuk' => $urut->orderBy('skor')->first(),
        };

        DB::table('percobaan_soals')->where('percobaan_id', $percobaanId)->where('soal_id', $soalId)->update(['opsi_id' => $opsi->id]);
    }
}
