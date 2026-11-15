<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\DataImport\Application\SyncFormasiFromBkn;

final class SinkronFormasi extends Command
{
    protected $signature = 'sicasn:sync-formasi';

    protected $description = 'Menyinkronkan formasi CPNS dari API publik BKN';

    public function handle(SyncFormasiFromBkn $sinkron): int
    {
        $hasil = $sinkron->handle();

        $hasil->berhasil ? $this->info($hasil->pesan) : $this->error($hasil->pesan);

        return $hasil->berhasil ? self::SUCCESS : self::FAILURE;
    }
}
