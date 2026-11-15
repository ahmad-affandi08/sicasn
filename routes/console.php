<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;
use Src\DataImport\Application\AmbilPengaturanSinkron;
use Src\DataImport\Domain\FrekuensiSinkron;

$frekuensiSaatIni = fn (): FrekuensiSinkron => resolve(AmbilPengaturanSinkron::class)->handle()->frekuensi;

Schedule::command('sicasn:sync-formasi')
    ->name('sinkron-harian')
    ->dailyAt('02:00')
    ->when(fn (): bool => $frekuensiSaatIni() === FrekuensiSinkron::Harian)
    ->withoutOverlapping();

Schedule::command('sicasn:sync-formasi')
    ->name('sinkron-enam-jam')
    ->everySixHours()
    ->when(fn (): bool => $frekuensiSaatIni() === FrekuensiSinkron::EnamJam)
    ->withoutOverlapping();

Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->name('antrean')
    ->everyMinute()
    ->withoutOverlapping();
