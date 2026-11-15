<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure;

use JsonMachine\Exception\JsonMachineException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Src\DataImport\Application\BarisBknSumber;
use Src\DataImport\Domain\Exception\SumberBknGagal;

final class BerkasBarisBkn implements BarisBknSumber
{
    public function __construct(private readonly string $jalur) {}

    public function hash(): string
    {
        return hash_file('sha256', $this->jalur) ?: '';
    }

    public function baris(): iterable
    {
        try {
            yield from Items::fromFile($this->jalur, ['pointer' => '/data', 'decoder' => new ExtJsonDecoder(true)]);
        } catch (JsonMachineException $galat) {
            throw SumberBknGagal::skemaTidakDikenal('JSON terpotong atau rusak ('.mb_substr($galat->getMessage(), 0, 120).')');
        }
    }

    public function tutup(): void
    {
        if (is_file($this->jalur)) {
            unlink($this->jalur);
        }
    }
}
