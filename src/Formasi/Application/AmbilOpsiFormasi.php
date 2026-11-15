<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\UrutanJenjang;

final readonly class AmbilOpsiFormasi
{
    public function __construct(
        private OpsiFormasiRepository $opsi,
        private KebijakanFormasiAktif $kebijakan,
    ) {}

    public function handle(FilterFormasi $filter): OpsiFormasi
    {
        $jenis = $this->kebijakan->aktif();
        $namaInstansi = $filter->instansi === null ? null : $this->opsi->namaInstansi($filter->instansi);

        return new OpsiFormasi(
            UrutanJenjang::urutkan($this->opsi->jenjang($jenis)),
            $this->opsi->provinsi($jenis),
            $filter->provinsi === null ? [] : $this->opsi->kabupatenKota($filter->provinsi, $jenis),
            $this->opsi->jenisFormasi($jenis),
            $namaInstansi === null ? null : new OpsiPilihan((string) $filter->instansi, $namaInstansi),
            $this->opsi->jurusanTerpilih($filter->jurusan),
        );
    }
}
