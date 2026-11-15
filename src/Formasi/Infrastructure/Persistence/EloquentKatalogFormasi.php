<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\FormasiBaru;
use Src\Formasi\Application\HasilSimpanFormasi;
use Src\Formasi\Application\KatalogFormasi;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\TipeInstansi;

final class EloquentKatalogFormasi implements KatalogFormasi
{
    private const FORMAT_WAKTU = 'Y-m-d H:i:s';

    private array $instansi = [];

    private array $jabatan = [];

    private array $wilayah = [];

    private array $jurusan = [];

    private array $kualifikasiSet = [];

    private array $persyaratan = [];

    public function mulaiSesi(): void
    {
        $this->instansi = $this->jabatan = $this->wilayah = $this->jurusan = $this->kualifikasiSet = $this->persyaratan = [];
    }

    public function simpanBanyak(array $formasi, DateTimeImmutable $sekarang): HasilSimpanFormasi
    {
        $unik = [];

        foreach ($formasi as $butir) {
            $unik[$butir->externalId] = $butir;
        }

        return DB::transaction(fn (): HasilSimpanFormasi => $this->simpanDalamTransaksi(array_values($unik), $sekarang->format(self::FORMAT_WAKTU)));
    }

    public function tandaiDitarik(DateTimeImmutable $terakhirDilihatSebelum, array $jenisPengadaan): int
    {
        return DB::table('formasis')
            ->where('status', 'aktif')
            ->whereIn('jenis_pengadaan', $this->nilaiJenis($jenisPengadaan))
            ->where('last_seen_at', '<', $terakhirDilihatSebelum->format(self::FORMAT_WAKTU))
            ->update(['status' => 'ditarik', 'updated_at' => $terakhirDilihatSebelum->format(self::FORMAT_WAKTU)]);
    }

    public function hitungAktif(array $jenisPengadaan): int
    {
        return DB::table('formasis')->where('status', 'aktif')->whereIn('jenis_pengadaan', $this->nilaiJenis($jenisPengadaan))->count();
    }

    private function simpanDalamTransaksi(array $formasi, string $waktu): HasilSimpanFormasi
    {
        $this->siapkanInstansi($formasi, $waktu);
        $this->siapkanJabatan($formasi, $waktu);
        $this->siapkanWilayah($formasi, $waktu);
        $this->siapkanKualifikasi($formasi, $waktu);
        $this->siapkanPersyaratan($formasi, $waktu);

        $ada = DB::table('formasis')->whereIn('external_id', array_map(fn (FormasiBaru $f) => $f->externalId, $formasi))->get(['id', 'external_id', 'hash_baris'])->keyBy('external_id');

        $baru = [];
        $diubah = [];
        $takBerubah = [];

        foreach ($formasi as $butir) {
            $hash = $butir->hash();
            $lama = $ada->get($butir->externalId);

            match (true) {
                $lama === null => $baru[] = $butir,
                $lama->hash_baris !== $hash => $diubah[$lama->id] = $butir,
                default => $takBerubah[] = $lama->id,
            };
        }

        if ($takBerubah !== []) {
            DB::table('formasis')->whereIn('id', $takBerubah)->update(['last_seen_at' => $waktu, 'status' => 'aktif']);
        }

        foreach ($diubah as $id => $butir) {
            DB::table('formasis')->where('id', $id)->update($this->baris($butir, $waktu, false) + ['status' => 'aktif']);
        }

        if ($baru !== []) {
            DB::table('formasis')->insert(array_map(fn (FormasiBaru $f) => $this->baris($f, $waktu, true), $baru));
        }

        $this->simpanPersyaratan($baru, $diubah, $waktu);

        return new HasilSimpanFormasi(count($baru), count($diubah), count($takBerubah));
    }

    private function baris(FormasiBaru $f, string $waktu, bool $baru): array
    {
        $lokasi = $f->lokasi;
        $wilayahId = $lokasi->terpetakanKeWilayah() ? ($this->wilayah[$lokasi->tipe->value.'|'.NamaNormal::dari($lokasi->nama)] ?? null) : null;
        $setId = $f->teksKualifikasi === null ? null : ($this->kualifikasiSet[sha1($f->teksKualifikasi)] ?? null);

        $data = [
            'event_id' => $f->eventId,
            'nama_event' => $f->namaEvent,
            'rekrutmen_id' => $f->rekrutmenId,
            'instansi_id' => $this->instansi[NamaNormal::dari($f->namaInstansi)],
            'jabatan_id' => $this->jabatan[$f->kodeJabatan.'|'.NamaNormal::dari($f->namaJabatan)],
            'jenis_pengadaan' => $f->jenisPengadaan->value,
            'jenis_formasi' => $f->jenisFormasi,
            'periode' => min(255, $f->periode),
            'lokasi_teks' => mb_substr($lokasi->teksAsli, 0, 255),
            'tipe_lokasi' => $lokasi->tipe->value,
            'wilayah_id' => $wilayahId,
            'kuota' => $f->kuota,
            'usia_min' => $f->usiaMin,
            'usia_max' => $f->usiaMax,
            'penghasilan_min' => $f->penghasilanMin,
            'penghasilan_max' => $f->penghasilanMax,
            'jobdesc' => $f->jobdesc,
            'is_disabilitas' => $f->isDisabilitas,
            'kualifikasi_set_id' => $setId,
            'kualifikasi_tersedia' => $f->kualifikasi !== [],
            'hash_baris' => $f->hash(),
            'last_seen_at' => $waktu,
            'updated_at' => $waktu,
        ];

        return $baru ? $data + ['external_id' => $f->externalId, 'first_seen_at' => $waktu, 'created_at' => $waktu, 'status' => 'aktif'] : $data;
    }

    private function simpanPersyaratan(array $baru, array $diubah, string $waktu): void
    {
        $ids = [];

        if ($baru !== []) {
            $ids = DB::table('formasis')->whereIn('external_id', array_map(fn (FormasiBaru $f) => $f->externalId, $baru))->pluck('id', 'external_id')->all();
        }

        if ($diubah !== []) {
            DB::table('formasi_persyaratans')->whereIn('formasi_id', array_keys($diubah))->delete();
        }

        $pivot = [];

        foreach ($baru as $butir) {
            $this->tambahPivot($pivot, $ids[$butir->externalId], $butir);
        }

        foreach ($diubah as $id => $butir) {
            $this->tambahPivot($pivot, $id, $butir);
        }

        foreach (array_chunk($pivot, 1000) as $potongan) {
            DB::table('formasi_persyaratans')->insert($potongan);
        }
    }

    private function tambahPivot(array &$pivot, int $formasiId, FormasiBaru $butir): void
    {
        foreach ($butir->persyaratan as $urutan => $syarat) {
            $pivot[] = [
                'formasi_id' => $formasiId,
                'persyaratan_id' => $this->persyaratan[sha1($syarat['uraian'])],
                'wajib' => $syarat['wajib'],
                'urutan' => $urutan + 1,
            ];
        }
    }

    private function siapkanInstansi(array $formasi, string $waktu): void
    {
        $butuh = [];

        foreach ($formasi as $f) {
            $kunci = NamaNormal::dari($f->namaInstansi);

            if (! isset($this->instansi[$kunci])) {
                $butuh[$kunci] = $f->namaInstansi;
            }
        }

        $this->lengkapi($this->instansi, 'instansis', 'nama_normal', $butuh, fn (string $nama, string $kunci) => [
            'nama' => mb_substr($nama, 0, 255),
            'nama_normal' => $kunci,
            'tipe' => TipeInstansi::dariNama($nama)->value,
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);
    }

    private function siapkanJabatan(array $formasi, string $waktu): void
    {
        $butuh = [];

        foreach ($formasi as $f) {
            $kunci = $f->kodeJabatan.'|'.NamaNormal::dari($f->namaJabatan);

            if (! isset($this->jabatan[$kunci])) {
                $butuh[$kunci] = [$f->kodeJabatan, $f->namaJabatan];
            }
        }

        if ($butuh === []) {
            return;
        }

        $nama = array_unique(array_map(fn (array $b) => NamaNormal::dari($b[1]), $butuh));

        $this->isiCache($this->jabatan, DB::table('jabatans')->whereIn('nama_normal', $nama)->get(['id', 'kode', 'nama_normal']), fn ($b) => $b->kode.'|'.$b->nama_normal);

        $baru = [];

        foreach ($butuh as $kunci => [$kode, $namaAsli]) {
            if (! isset($this->jabatan[$kunci])) {
                $baru[] = ['kode' => mb_substr($kode, 0, 40), 'nama' => mb_substr($namaAsli, 0, 255), 'nama_normal' => NamaNormal::dari($namaAsli), 'created_at' => $waktu, 'updated_at' => $waktu];
            }
        }

        if ($baru !== []) {
            DB::table('jabatans')->insertOrIgnore($baru);
            $this->isiCache($this->jabatan, DB::table('jabatans')->whereIn('nama_normal', $nama)->get(['id', 'kode', 'nama_normal']), fn ($b) => $b->kode.'|'.$b->nama_normal);
        }
    }

    private function siapkanWilayah(array $formasi, string $waktu): void
    {
        $butuh = [];

        foreach ($formasi as $f) {
            if ($f->lokasi->terpetakanKeWilayah()) {
                $kunci = $f->lokasi->tipe->value.'|'.NamaNormal::dari($f->lokasi->nama);

                if (! isset($this->wilayah[$kunci])) {
                    $butuh[$kunci] = [$f->lokasi->tipe->value, $f->lokasi->nama];
                }
            }
        }

        if ($butuh === []) {
            return;
        }

        $nama = array_unique(array_map(fn (array $b) => NamaNormal::dari($b[1]), $butuh));
        $baca = fn () => DB::table('wilayahs')->whereIn('nama_normal', $nama)->get(['id', 'tingkat', 'nama_normal']);

        $this->isiCache($this->wilayah, $baca(), fn ($w) => $w->tingkat.'|'.$w->nama_normal);

        $baru = [];

        foreach ($butuh as $kunci => [$tingkat, $namaAsli]) {
            if (! isset($this->wilayah[$kunci])) {
                $baru[] = ['nama' => mb_substr($namaAsli, 0, 255), 'nama_normal' => NamaNormal::dari($namaAsli), 'tingkat' => $tingkat, 'created_at' => $waktu, 'updated_at' => $waktu];
            }
        }

        if ($baru !== []) {
            DB::table('wilayahs')->insertOrIgnore($baru);
            $this->isiCache($this->wilayah, $baca(), fn ($w) => $w->tingkat.'|'.$w->nama_normal);
        }
    }

    private function siapkanKualifikasi(array $formasi, string $waktu): void
    {
        $butuhSet = [];
        $butuhJurusan = [];

        foreach ($formasi as $f) {
            if ($f->teksKualifikasi === null) {
                continue;
            }

            $hash = sha1($f->teksKualifikasi);

            if (! isset($this->kualifikasiSet[$hash])) {
                $butuhSet[$hash] = $f;

                foreach ($f->kualifikasi as $k) {
                    if ($k->jurusan !== null && ! isset($this->jurusan[$k->jurusan])) {
                        $butuhJurusan[$k->jurusan] = $k->jurusan;
                    }
                }
            }
        }

        $this->lengkapi($this->jurusan, 'jurusans', 'nama_normal', $butuhJurusan, fn (string $nama, string $kunci) => [
            'nama' => mb_substr($nama, 0, 255),
            'nama_normal' => mb_substr($kunci, 0, 255),
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);

        if ($butuhSet === []) {
            return;
        }

        $hashes = array_keys($butuhSet);
        $this->kualifikasiSet += DB::table('kualifikasi_sets')->whereIn('hash', $hashes)->pluck('id', 'hash')->all();

        $belum = array_diff_key($butuhSet, $this->kualifikasiSet);

        if ($belum === []) {
            return;
        }

        DB::table('kualifikasi_sets')->insertOrIgnore(array_map(fn (FormasiBaru $f, string $hash) => ['hash' => $hash, 'teks' => $f->teksKualifikasi, 'created_at' => $waktu, 'updated_at' => $waktu], $belum, array_keys($belum)));
        $this->kualifikasiSet += DB::table('kualifikasi_sets')->whereIn('hash', array_keys($belum))->pluck('id', 'hash')->all();

        $item = [];

        foreach ($belum as $hash => $f) {
            foreach ($f->kualifikasi as $k) {
                $item[] = [
                    'kualifikasi_set_id' => $this->kualifikasiSet[$hash],
                    'jenjang' => mb_substr($k->jenjang, 0, 20),
                    'jurusan_id' => $k->jurusan === null ? null : $this->jurusan[$k->jurusan],
                    'semua_jurusan' => $k->semuaJurusan,
                ];
            }
        }

        foreach (array_chunk($item, 1000) as $potongan) {
            DB::table('kualifikasi_set_items')->insert($potongan);
        }
    }

    private function siapkanPersyaratan(array $formasi, string $waktu): void
    {
        $butuh = [];

        foreach ($formasi as $f) {
            foreach ($f->persyaratan as $syarat) {
                $hash = sha1($syarat['uraian']);

                if (! isset($this->persyaratan[$hash])) {
                    $butuh[$hash] = $syarat['uraian'];
                }
            }
        }

        if ($butuh === []) {
            return;
        }

        foreach (array_chunk($butuh, 300, true) as $potongan) {
            $this->persyaratan += DB::table('persyaratans')->whereIn('hash', array_keys($potongan))->pluck('id', 'hash')->all();
        }

        $belum = array_diff_key($butuh, $this->persyaratan);

        if ($belum === []) {
            return;
        }

        foreach (array_chunk($belum, 300, true) as $potongan) {
            DB::table('persyaratans')->insertOrIgnore(array_map(fn (string $uraian, string $hash) => ['hash' => $hash, 'uraian' => $uraian, 'created_at' => $waktu, 'updated_at' => $waktu], $potongan, array_keys($potongan)));
            $this->persyaratan += DB::table('persyaratans')->whereIn('hash', array_keys($potongan))->pluck('id', 'hash')->all();
        }
    }

    private function lengkapi(array &$cache, string $tabel, string $kolomKunci, array $butuh, callable $barisBaru): void
    {
        if ($butuh === []) {
            return;
        }

        foreach (array_chunk($butuh, 300, true) as $potongan) {
            $cache += DB::table($tabel)->whereIn($kolomKunci, array_keys($potongan))->pluck('id', $kolomKunci)->all();
        }

        $belum = array_diff_key($butuh, $cache);

        foreach (array_chunk($belum, 300, true) as $potongan) {
            DB::table($tabel)->insertOrIgnore(array_map($barisBaru, $potongan, array_keys($potongan)));
            $cache += DB::table($tabel)->whereIn($kolomKunci, array_keys($potongan))->pluck('id', $kolomKunci)->all();
        }
    }

    private function isiCache(array &$cache, iterable $baris, callable $kunci): void
    {
        foreach ($baris as $b) {
            $cache[$kunci($b)] = $b->id;
        }
    }

    private function nilaiJenis(array $jenisPengadaan): array
    {
        return array_map(fn (JenisPengadaan $jenis) => $jenis->value, $jenisPengadaan);
    }
}
