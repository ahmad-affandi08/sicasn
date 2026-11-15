<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

interface OpsiFormasiRepository
{
    public function jenjang(array $jenis): array;

    public function provinsi(array $jenis): array;

    public function kabupatenKota(int $provinsiId, array $jenis): array;

    public function jenisFormasi(array $jenis): array;

    public function instansi(string $kataKunci, array $jenis, int $batas): array;

    public function jurusan(string $kataKunci, array $jenis, int $batas): array;

    public function namaInstansi(int $id): ?string;

    public function jurusanTerpilih(array $ids): array;
}
