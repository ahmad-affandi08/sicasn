<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Src\DataImport\Application\HasilSinkron;
use Src\DataImport\Application\KonfirmasiImporStatistik;
use Src\DataImport\Application\PratinjauImporStatistik;
use Src\DataImport\Application\SyncFormasiFromBkn;
use Src\Identity\Infrastructure\Persistence\UserModel;
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
    $kolom = ['tahun', 'jenis_pengadaan', 'cakupan', 'instansi', 'jumlah_formasi', 'jumlah_pelamar', 'jumlah_submit', 'jumlah_lolos_administrasi', 'tanggal_snapshot', 'status_angka', 'tingkat_data', 'keyakinan', 'sumber_nama', 'sumber_url', 'catatan'];
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
