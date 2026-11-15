<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\BankSoalSeeder;
use Illuminate\Console\Command;

final class MuatSoal extends Command
{
    protected $signature = 'sicasn:muat-soal';

    protected $description = 'Memuat bank soal latihan SKD dari berkas bawaan; aman diulang dan hanya memperbarui yang berubah';

    public function handle(): int
    {
        return $this->call('db:seed', ['--class' => BankSoalSeeder::class, '--force' => true]);
    }
}
