<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

use InvalidArgumentException;
use Src\Pengaturan\Domain\PengaturanSitus;

final readonly class AturUrlSitus
{
    public function __construct(private PengaturanSitusStore $store) {}

    public function handle(string $url): void
    {
        $bersih = rtrim(trim($url), '/');

        if (! filter_var($bersih, FILTER_VALIDATE_URL) || ! in_array(parse_url($bersih, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new InvalidArgumentException('Alamat situs harus diawali http:// atau https://.');
        }

        $saatIni = $this->store->muat();
        $this->store->simpan(new PengaturanSitus($saatIni->satuSesi, $bersih));
    }
}
