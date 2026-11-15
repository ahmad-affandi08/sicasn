<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\Exception\BarisTidakValid;
use Src\DataImport\Domain\Exception\SumberBknGagal;
use Src\DataImport\Domain\StatusBatch;
use Src\Formasi\Application\FormasiBaru;
use Src\Formasi\Application\KatalogFormasi;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;
use Throwable;

final class SyncFormasiFromBkn
{
    private const TIPE = 'sinkron_bkn';

    private const UKURAN_POTONGAN = 500;

    private const MAKS_GALAT_TERSIMPAN = 100;

    private const BATAS_KEDALUWARSA = '-30 minutes';

    public function __construct(
        private readonly BknFormasiSource $sumber,
        private readonly KatalogFormasi $katalog,
        private readonly ImportBatchRepository $batch,
        private readonly EventTerdeteksiRepository $event,
        private readonly PengaturanSinkronStore $pengaturan,
        private readonly KebijakanJenisPengadaan $kebijakan,
        private readonly AuditLog $audit,
        private readonly Clock $clock,
    ) {}

    public function handle(?int $aktorId = null): HasilSinkron
    {
        $mulai = $this->clock->sekarang();

        $this->batch->tutupYangKedaluwarsa(self::TIPE, $mulai->modify(self::BATAS_KEDALUWARSA), $mulai);

        if ($this->batch->adaYangBerjalan(self::TIPE)) {
            return new HasilSinkron(false, 'Sinkron lain sedang berjalan. Coba lagi beberapa menit lagi.', [], null);
        }

        $url = $this->pengaturan->muat()->url;
        $batchId = $this->batch->mulai(self::TIPE, $url, $aktorId, $mulai);

        try {
            $sumber = $this->sumber->ambil($url);
        } catch (SumberBknGagal $galat) {
            return $this->gagal($batchId, $galat->getMessage());
        }

        try {
            return $this->proses($batchId, $sumber, $mulai, $aktorId);
        } catch (SumberBknGagal $galat) {
            return $this->gagal($batchId, $galat->getMessage());
        } catch (Throwable $galat) {
            $this->gagal($batchId, 'Kesalahan tak terduga saat memproses data: '.mb_substr($galat->getMessage(), 0, 200));

            throw $galat;
        } finally {
            $sumber->tutup();
        }
    }

    private function proses(int $batchId, BarisBknSumber $sumber, \DateTimeImmutable $mulai, ?int $aktorId): HasilSinkron
    {
        $hash = $sumber->hash();
        $terakhir = $this->batch->terakhirBerhasil(self::TIPE);
        $penarikanTertunda = (bool) ($terakhir?->ringkasan['penarikan_ditunda'] ?? false);

        if ($terakhir !== null && $terakhir->hashRespons === $hash && ! $penarikanTertunda) {
            $ringkasan = ['dilewati' => true, 'alasan' => 'Respons identik dengan sinkron sebelumnya.'];
            $this->batch->selesai($batchId, StatusBatch::Sukses, $ringkasan, $hash, $this->clock->sekarang());

            return new HasilSinkron(true, 'Tidak ada perubahan sejak sinkron terakhir.', $ringkasan, $batchId);
        }

        $aktif = $this->kebijakan->aktif();
        $aktifSebelum = $this->katalog->hitungAktif($aktif);
        $this->katalog->mulaiSesi();

        $hitung = ['total' => 0, 'aktif' => 0, 'diabaikan' => 0, 'gagal' => 0, 'baru' => 0, 'diubah' => 0, 'tak_berubah' => 0];
        $event = [];
        $galat = [];
        $antrean = [];

        foreach ($sumber->baris() as $indeks => $mentah) {
            $hitung['total']++;
            $nomor = (int) $indeks + 1;

            try {
                $formasi = PemetaBarisBkn::petakan(is_array($mentah) ? $mentah : []);
            } catch (BarisTidakValid $tidakValid) {
                $hitung['gagal']++;
                $this->catatGalat($galat, new GalatBaris($nomor, $tidakValid->kolom, $tidakValid->getMessage(), is_array($mentah) ? (string) ($mentah['id'] ?? '') : null));

                continue;
            }

            $this->catatEvent($event, $formasi, in_array($formasi->jenisPengadaan, $aktif, true));

            if (! in_array($formasi->jenisPengadaan, $aktif, true)) {
                $hitung['diabaikan']++;

                continue;
            }

            $hitung['aktif']++;
            $antrean[] = $formasi;

            if (count($antrean) >= self::UKURAN_POTONGAN) {
                $this->simpan($antrean, $mulai, $hitung);
                $antrean = [];
            }
        }

        $this->simpan($antrean, $mulai, $hitung);

        $eventBaru = $this->event->catat(array_map(
            fn (array $e) => new EventTeramati($e['id'], $e['nama'], array_keys($e['jenis']), $e['jumlah'], ! $e['aktif']),
            array_values($event),
        ), $mulai);

        [$ditarik, $ditunda] = $this->tarikYangHilang($mulai, $aktif, $aktifSebelum, $hitung, $penarikanTertunda && $terakhir?->hashRespons === $hash);

        $ringkasan = [
            'total_baris' => $hitung['total'],
            'baris_aktif' => $hitung['aktif'],
            'diabaikan' => $hitung['diabaikan'],
            'baru' => $hitung['baru'],
            'diubah' => $hitung['diubah'],
            'tak_berubah' => $hitung['tak_berubah'],
            'ditarik' => $ditarik,
            'gagal_baris' => $hitung['gagal'],
            'event_baru' => $eventBaru,
            'penarikan_ditunda' => $ditunda,
            'durasi_detik' => max(0, $this->clock->sekarang()->getTimestamp() - $mulai->getTimestamp()),
        ];

        $status = $hitung['gagal'] > 0 ? StatusBatch::Sebagian : StatusBatch::Sukses;

        $this->batch->catatGalat($batchId, $galat);
        $this->batch->selesai($batchId, $status, $ringkasan, $hash, $this->clock->sekarang());
        $this->audit->catat($aktorId, 'sinkron.selesai', null, ['batch' => $batchId, 'status' => $status->value]);

        return new HasilSinkron(true, $this->pesanSelesai($ringkasan), $ringkasan, $batchId);
    }

    private function simpan(array $antrean, \DateTimeImmutable $mulai, array &$hitung): void
    {
        if ($antrean === []) {
            return;
        }

        $hasil = $this->katalog->simpanBanyak($antrean, $mulai);
        $hitung['baru'] += $hasil->baru;
        $hitung['diubah'] += $hasil->diubah;
        $hitung['tak_berubah'] += $hasil->takBerubah;
    }

    private function tarikYangHilang(\DateTimeImmutable $mulai, array $aktif, int $aktifSebelum, array $hitung, bool $dikonfirmasiUlang): array
    {
        $penurunanWajar = $aktifSebelum === 0 || $aktifSebelum <= $hitung['aktif'] * 2;
        $galatWajar = $hitung['total'] >= $hitung['gagal'] * 100;

        if (($penurunanWajar || $dikonfirmasiUlang) && $galatWajar) {
            return [$this->katalog->tandaiDitarik($mulai, $aktif), false];
        }

        return [0, true];
    }

    private function catatEvent(array &$event, FormasiBaru $formasi, bool $aktif): void
    {
        $event[$formasi->eventId] ??= ['id' => $formasi->eventId, 'nama' => $formasi->namaEvent, 'jenis' => [], 'jumlah' => 0, 'aktif' => false];
        $event[$formasi->eventId]['jenis'][$formasi->jenisPengadaan->value] = true;
        $event[$formasi->eventId]['jumlah']++;
        $event[$formasi->eventId]['aktif'] = $event[$formasi->eventId]['aktif'] || $aktif;
    }

    private function catatGalat(array &$galat, GalatBaris $baris): void
    {
        if (count($galat) < self::MAKS_GALAT_TERSIMPAN) {
            $galat[] = $baris;
        }
    }

    private function gagal(int $batchId, string $pesan): HasilSinkron
    {
        $ringkasan = ['galat' => $pesan];
        $this->batch->selesai($batchId, StatusBatch::Gagal, $ringkasan, null, $this->clock->sekarang());

        return new HasilSinkron(false, $pesan, $ringkasan, $batchId);
    }

    private function pesanSelesai(array $ringkasan): string
    {
        $bagian = [
            "{$ringkasan['baris_aktif']} formasi CPNS diproses",
            "{$ringkasan['baru']} baru",
            "{$ringkasan['diubah']} diubah",
            "{$ringkasan['ditarik']} ditarik",
            "{$ringkasan['diabaikan']} diabaikan (bukan CPNS)",
        ];

        if ($ringkasan['gagal_baris'] > 0) {
            $bagian[] = "{$ringkasan['gagal_baris']} baris gagal";
        }

        if ($ringkasan['penarikan_ditunda']) {
            $bagian[] = 'penarikan formasi ditunda karena jumlah baris turun drastis';
        }

        return implode(', ', $bagian).'.';
    }
}
