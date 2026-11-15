<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\Lokasi;

final readonly class FormasiBaru
{
    public function __construct(
        public string $externalId,
        public string $eventId,
        public string $namaEvent,
        public ?string $rekrutmenId,
        public string $namaInstansi,
        public string $kodeJabatan,
        public string $namaJabatan,
        public JenisPengadaan $jenisPengadaan,
        public string $jenisFormasi,
        public int $periode,
        public Lokasi $lokasi,
        public int $kuota,
        public ?int $usiaMin,
        public ?int $usiaMax,
        public ?int $penghasilanMin,
        public ?int $penghasilanMax,
        public ?string $jobdesc,
        public ?bool $isDisabilitas,
        public ?string $teksKualifikasi,
        public array $kualifikasi,
        public array $persyaratan,
    ) {}

    public function hash(): string
    {
        return sha1(json_encode([
            $this->eventId, $this->namaEvent, $this->rekrutmenId, $this->namaInstansi, $this->kodeJabatan, $this->namaJabatan,
            $this->jenisPengadaan->value, $this->jenisFormasi, $this->periode, $this->lokasi->teksAsli, $this->kuota,
            $this->usiaMin, $this->usiaMax, $this->penghasilanMin, $this->penghasilanMax, $this->jobdesc, $this->isDisabilitas,
            $this->teksKualifikasi, $this->persyaratan,
        ], JSON_THROW_ON_ERROR));
    }
}
