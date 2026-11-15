<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use DateTimeImmutable;

interface KatalogFormasi
{
    public function mulaiSesi(): void;

    public function simpanBanyak(array $formasi, DateTimeImmutable $sekarang): HasilSimpanFormasi;

    public function tandaiDitarik(DateTimeImmutable $terakhirDilihatSebelum, array $jenisPengadaan): int;

    public function hitungAktif(array $jenisPengadaan): int;

    public function hitungLokasiTidakTerpetakan(array $jenisPengadaan): array;

    public function hitungAktifPerInstansi(array $jenisPengadaan, array $instansiIds): array;
}
