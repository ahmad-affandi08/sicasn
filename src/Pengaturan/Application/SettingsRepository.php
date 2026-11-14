<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

interface SettingsRepository
{
    public function ambilGrup(string $grup): array;

    public function simpanBanyak(string $grup, array $nilai, array $kunciRahasia = []): void;
}
