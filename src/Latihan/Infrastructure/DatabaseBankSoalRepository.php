<?php

declare(strict_types=1);

namespace Src\Latihan\Infrastructure;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Latihan\Application\BankSoalRepository;
use Src\Latihan\Application\HasilMuatBankSoal;
use Src\Latihan\Domain\KategoriSoal;
use Src\Latihan\Domain\OpsiBaru;
use Src\Latihan\Domain\SoalBaru;

final class DatabaseBankSoalRepository implements BankSoalRepository
{
    private const FORMAT = 'Y-m-d H:i:s';

    public function jumlahAktifPerKategori(): array
    {
        $hasil = array_fill_keys(array_map(fn (KategoriSoal $k) => $k->value, KategoriSoal::cases()), 0);

        foreach (DB::table('soals')->where('aktif', true)->selectRaw('kategori, count(*) as jumlah')->groupBy('kategori')->get() as $baris) {
            $hasil[$baris->kategori] = (int) $baris->jumlah;
        }

        return $hasil;
    }

    public function idAcak(KategoriSoal $kategori, int $jumlah, array $hindari): array
    {
        $dasar = fn () => DB::table('soals')->where('aktif', true)->where('kategori', $kategori->value);

        $segar = $dasar()->when($hindari !== [], fn ($q) => $q->whereNotIn('id', $hindari))->inRandomOrder()->limit($jumlah)->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count($segar) >= $jumlah) {
            return $segar;
        }

        $tambahan = $dasar()->whereIn('id', $hindari)->inRandomOrder()->limit($jumlah - count($segar))->pluck('id')->map(fn ($id) => (int) $id)->all();

        return [...$segar, ...$tambahan];
    }

    public function muat(array $soal, DateTimeImmutable $sekarang): HasilMuatBankSoal
    {
        $waktu = $sekarang->format(self::FORMAT);
        $baru = 0;
        $diperbarui = 0;

        DB::transaction(function () use ($soal, $waktu, &$baru, &$diperbarui): void {
            foreach ($soal as $butir) {
                $ada = DB::table('soals')->where('kode', $butir->kode())->first(['id', 'hash_isi', 'aktif']);
                $hash = $this->hashIsi($butir);

                if ($ada === null) {
                    $this->sisipkan($butir, $hash, $waktu);
                    $baru++;

                    continue;
                }

                if ($ada->hash_isi !== $hash || ! $ada->aktif) {
                    $this->perbarui((int) $ada->id, $butir, $hash, $waktu);
                    $diperbarui++;
                }
            }
        });

        $kodeAktif = array_map(fn (SoalBaru $butir) => $butir->kode(), $soal);
        $dinonaktifkan = DB::table('soals')->where('aktif', true)->whereNotIn('kode', $kodeAktif)->update(['aktif' => false, 'updated_at' => $waktu]);

        return new HasilMuatBankSoal($baru, $diperbarui, $dinonaktifkan);
    }

    private function sisipkan(SoalBaru $butir, string $hash, string $waktu): void
    {
        $id = DB::table('soals')->insertGetId([...$this->kolomSoal($butir, $hash, $waktu), 'kode' => $butir->kode(), 'created_at' => $waktu]);

        DB::table('soal_opsis')->insert(array_map(fn (OpsiBaru $opsi) => $this->kolomOpsi($id, $opsi, $waktu) + ['created_at' => $waktu], $butir->opsi));
    }

    private function perbarui(int $id, SoalBaru $butir, string $hash, string $waktu): void
    {
        DB::table('soals')->where('id', $id)->update([...$this->kolomSoal($butir, $hash, $waktu), 'aktif' => true]);

        foreach ($butir->opsi as $opsi) {
            DB::table('soal_opsis')->where('soal_id', $id)->where('label', $opsi->label)->update(['teks' => $opsi->teks, 'skor' => $opsi->skor, 'updated_at' => $waktu]);
        }
    }

    private function kolomSoal(SoalBaru $butir, string $hash, string $waktu): array
    {
        return [
            'hash_isi' => $hash,
            'kategori' => $butir->kategori->value,
            'subkategori' => $butir->subkategori,
            'pertanyaan' => $butir->pertanyaan,
            'pembahasan' => $butir->pembahasan,
            'sumber_nama' => $butir->sumberNama,
            'sumber_url' => $butir->sumberUrl,
            'lisensi' => $butir->lisensi,
            'aktif' => true,
            'updated_at' => $waktu,
        ];
    }

    private function kolomOpsi(int $soalId, OpsiBaru $opsi, string $waktu): array
    {
        return ['soal_id' => $soalId, 'label' => $opsi->label, 'teks' => $opsi->teks, 'skor' => $opsi->skor, 'updated_at' => $waktu];
    }

    private function hashIsi(SoalBaru $butir): string
    {
        return sha1((string) json_encode([
            $butir->subkategori, $butir->pertanyaan, $butir->pembahasan, $butir->sumberNama, $butir->sumberUrl, $butir->lisensi,
            array_map(fn (OpsiBaru $opsi) => [$opsi->label, $opsi->teks, $opsi->skor], $butir->opsi),
        ], JSON_UNESCAPED_UNICODE));
    }
}
