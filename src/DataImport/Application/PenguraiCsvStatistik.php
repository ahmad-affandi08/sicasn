<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use DateTimeImmutable;
use Src\Formasi\Application\BarisStatistik;
use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;

final class PenguraiCsvStatistik
{
    public const KOLOM = ['tahun', 'jenis_pengadaan', 'cakupan', 'instansi', 'jumlah_formasi', 'jumlah_pelamar', 'jumlah_submit', 'jumlah_lolos_administrasi', 'tanggal_snapshot', 'status_angka', 'tingkat_data', 'keyakinan', 'sumber_nama', 'sumber_url', 'catatan'];

    public const KOLOM_WAJIB = ['tahun', 'cakupan', 'tanggal_snapshot', 'sumber_nama'];

    public const MAKS_BARIS = 5000;

    public const MAKS_GALAT = 1000;

    private const MAKS_ANGKA = 2000000000;

    public static function urai(string $isi, DateTimeImmutable $hariIni): HasilUraiCsv
    {
        $isi = self::normalkanEncoding($isi);

        if (trim($isi) === '') {
            return new HasilUraiCsv([], [], 0, 'Berkas kosong.');
        }

        $aliran = fopen('php://temp', 'r+');
        fwrite($aliran, $isi);
        rewind($aliran);

        $pemisah = self::deteksiPemisah($isi);
        $header = fgetcsv($aliran, 0, $pemisah, '"', '');
        $kolom = array_map(fn ($nama) => mb_strtolower(trim((string) $nama)), $header ?: []);
        $hilang = array_values(array_diff(self::KOLOM_WAJIB, $kolom));

        if ($hilang !== []) {
            fclose($aliran);

            return new HasilUraiCsv([], [], 0, 'Kolom wajib tidak ditemukan: '.implode(', ', $hilang).'.');
        }

        $baris = [];
        $galat = [];
        $dikenal = [];
        $total = 0;

        while (($rekaman = fgetcsv($aliran, 0, $pemisah, '"', '')) !== false) {
            if ($rekaman === [null] || self::kosong($rekaman)) {
                continue;
            }

            $total++;

            if ($total > self::MAKS_BARIS) {
                fclose($aliran);

                return new HasilUraiCsv([], [], $total, 'Berkas melebihi '.self::MAKS_BARIS.' baris data. Pecah menjadi beberapa berkas.');
            }

            $nilai = [];

            foreach ($kolom as $indeks => $nama) {
                $nilai[$nama] = trim((string) ($rekaman[$indeks] ?? ''));
            }

            $masalah = [];
            $hasil = self::petakan($nilai, $hariIni, $masalah);

            if ($hasil !== null) {
                $kunci = implode('|', [$hasil->tahun, $hasil->jenisPengadaan->value, $hasil->cakupan->value, mb_strtoupper((string) $hasil->namaInstansi), $hasil->tanggalSnapshot, mb_strtolower($hasil->sumberNama)]);

                if (isset($dikenal[$kunci])) {
                    $masalah[] = ['baris_ganda', 'Baris ini sama dengan baris '.$dikenal[$kunci].' (instansi, tanggal, dan sumber yang sama).'];
                } else {
                    $dikenal[$kunci] = $total + 1;
                }
            }

            if ($masalah === [] && $hasil !== null) {
                $baris[] = $hasil;

                continue;
            }

            foreach ($masalah as [$namaKolom, $pesan]) {
                if (count($galat) < self::MAKS_GALAT) {
                    $galat[] = new GalatBaris($total + 1, $namaKolom, $pesan, $nilai[$namaKolom] ?? null);
                }
            }
        }

        fclose($aliran);

        return new HasilUraiCsv($baris, $galat, $total, $total === 0 ? 'Berkas tidak memuat baris data.' : null);
    }

    private static function petakan(array $nilai, DateTimeImmutable $hariIni, array &$masalah): ?BarisStatistik
    {
        $tahun = self::bilangan($nilai['tahun'] ?? '', 'tahun', $masalah);

        if ($tahun !== null && ($tahun < 2000 || $tahun > 2100)) {
            $masalah[] = ['tahun', 'Tahun harus antara 2000 dan 2100.'];
        }

        $jenis = JenisPengadaan::tryFrom(mb_strtolower($nilai['jenis_pengadaan'] ?? '') ?: 'cpns');

        if ($jenis === null) {
            $masalah[] = ['jenis_pengadaan', 'Jenis pengadaan tidak dikenal.'];
        }

        $cakupan = Cakupan::tryFrom(mb_strtolower($nilai['cakupan'] ?? ''));

        if ($cakupan === null) {
            $masalah[] = ['cakupan', 'Cakupan harus nasional atau instansi.'];
        } elseif ($cakupan === Cakupan::Jabatan) {
            $masalah[] = ['cakupan', 'Cakupan jabatan belum didukung. Gunakan nasional atau instansi.'];
        }

        $instansi = NamaNormal::rapikan($nilai['instansi'] ?? '');

        if ($cakupan === Cakupan::Instansi && $instansi === '') {
            $masalah[] = ['instansi', 'Nama instansi wajib diisi untuk cakupan instansi.'];
        }

        if ($cakupan === Cakupan::Nasional && $instansi !== '') {
            $masalah[] = ['instansi', 'Kolom instansi harus kosong untuk cakupan nasional.'];
        }

        if (mb_strlen($instansi) > 255) {
            $masalah[] = ['instansi', 'Nama instansi terlalu panjang (maksimal 255 karakter).'];
        }

        $formasi = self::bilangan($nilai['jumlah_formasi'] ?? '', 'jumlah_formasi', $masalah);
        $pelamar = self::bilangan($nilai['jumlah_pelamar'] ?? '', 'jumlah_pelamar', $masalah);
        $submit = self::bilangan($nilai['jumlah_submit'] ?? '', 'jumlah_submit', $masalah);
        $lolos = self::bilangan($nilai['jumlah_lolos_administrasi'] ?? '', 'jumlah_lolos_administrasi', $masalah);

        $semuaKosong = trim(($nilai['jumlah_formasi'] ?? '').($nilai['jumlah_pelamar'] ?? '').($nilai['jumlah_submit'] ?? '').($nilai['jumlah_lolos_administrasi'] ?? '')) === '';

        if ($semuaKosong) {
            $masalah[] = ['jumlah_pelamar', 'Isi sedikitnya satu kolom jumlah.'];
        }

        if ($pelamar !== null && $submit !== null && $submit > $pelamar) {
            $masalah[] = ['jumlah_submit', 'Jumlah submit tidak boleh lebih besar dari jumlah pelamar.'];
        }

        if ($pelamar !== null && $lolos !== null && $lolos > $pelamar) {
            $masalah[] = ['jumlah_lolos_administrasi', 'Jumlah lolos administrasi tidak boleh lebih besar dari jumlah pelamar.'];
        }

        $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $nilai['tanggal_snapshot'] ?? '');

        if ($tanggal === false || $tanggal->format('Y-m-d') !== ($nilai['tanggal_snapshot'] ?? '')) {
            $masalah[] = ['tanggal_snapshot', 'Tanggal harus berformat TTTT-BB-HH, misalnya 2024-09-17.'];
        } elseif ($tanggal > $hariIni) {
            $masalah[] = ['tanggal_snapshot', 'Tanggal snapshot tidak boleh di masa depan.'];
        }

        $status = StatusAngka::tryFrom(mb_strtolower($nilai['status_angka'] ?? '') ?: 'sementara');

        if ($status === null) {
            $masalah[] = ['status_angka', 'Status angka harus sementara atau akhir.'];
        }

        $tingkat = TingkatData::tryFrom(mb_strtoupper($nilai['tingkat_data'] ?? '') ?: 'A');

        if ($tingkat === null) {
            $masalah[] = ['tingkat_data', 'Tingkat data harus A, B, atau C.'];
        }

        $keyakinan = Keyakinan::tryFrom(mb_strtolower($nilai['keyakinan'] ?? '') ?: 'turunan');

        if ($keyakinan === null) {
            $masalah[] = ['keyakinan', 'Keyakinan harus resmi atau turunan.'];
        }

        $sumber = NamaNormal::rapikan($nilai['sumber_nama'] ?? '');

        if ($sumber === '' || mb_strlen($sumber) > 255) {
            $masalah[] = ['sumber_nama', 'Nama sumber wajib diisi (maksimal 255 karakter).'];
        }

        $url = trim($nilai['sumber_url'] ?? '');

        if ($url !== '' && (filter_var($url, FILTER_VALIDATE_URL) === false || ! preg_match('#^https?://#i', $url) || mb_strlen($url) > 500)) {
            $masalah[] = ['sumber_url', 'Tautan sumber harus berupa URL http atau https yang valid.'];
        }

        $catatan = NamaNormal::rapikan($nilai['catatan'] ?? '');

        if (mb_strlen($catatan) > 255) {
            $masalah[] = ['catatan', 'Catatan terlalu panjang (maksimal 255 karakter).'];
        }

        if ($masalah !== []) {
            return null;
        }

        return new BarisStatistik(
            (int) $tahun,
            $jenis,
            $cakupan,
            $cakupan === Cakupan::Instansi ? $instansi : null,
            $formasi,
            $pelamar,
            $submit,
            $lolos,
            $tanggal->format('Y-m-d'),
            $status,
            $tingkat,
            $keyakinan,
            $sumber,
            $url === '' ? null : $url,
            $catatan === '' ? null : $catatan,
        );
    }

    private static function bilangan(string $nilai, string $kolom, array &$masalah): ?int
    {
        $nilai = trim($nilai);

        if ($nilai === '') {
            return null;
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $nilai) === 1) {
            $nilai = str_replace('.', '', $nilai);
        }

        if (preg_match('/^\d+$/', $nilai) !== 1 || (int) $nilai > self::MAKS_ANGKA) {
            $masalah[] = [$kolom, 'Harus berupa bilangan bulat nol atau lebih (tanpa pecahan, tanda minus, atau huruf).'];

            return null;
        }

        return (int) $nilai;
    }

    private static function normalkanEncoding(string $isi): string
    {
        if (str_starts_with($isi, "\xEF\xBB\xBF")) {
            $isi = substr($isi, 3);
        }

        return mb_check_encoding($isi, 'UTF-8') ? $isi : mb_convert_encoding($isi, 'UTF-8', 'Windows-1252');
    }

    private static function deteksiPemisah(string $isi): string
    {
        $barisPertama = strtok($isi, "\r\n") ?: '';
        $hitung = [',' => substr_count($barisPertama, ','), ';' => substr_count($barisPertama, ';'), "\t" => substr_count($barisPertama, "\t")];
        arsort($hitung);

        return array_key_first($hitung) ?: ',';
    }

    private static function kosong(array $rekaman): bool
    {
        return trim(implode('', array_map(fn ($sel) => (string) $sel, $rekaman))) === '';
    }
}
