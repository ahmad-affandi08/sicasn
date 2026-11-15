<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class AmbilPratinjauImpor
{
    public function __construct(private PenyimpananPratinjau $penyimpanan) {}

    public function handle(string $token): ?PratinjauImpor
    {
        $data = $this->penyimpanan->ambil($token);

        return $data === null ? null : PetaPratinjau::dari($token, $data);
    }
}
