<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Identity\Application\CreateAdminAccount;
use Src\Identity\Domain\Exception\EmailSudahTerdaftar;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class BuatAdmin extends Command
{
    protected $signature = 'sicasn:buat-admin {--nama=} {--email=} {--password=}';

    protected $description = 'Membuat akun admin';

    public function handle(CreateAdminAccount $buat): int
    {
        $nama = (string) ($this->option('nama') ?: text('Nama admin', required: true));
        $email = (string) ($this->option('email') ?: text('Email admin', required: true, validate: fn (string $nilai) => filter_var($nilai, FILTER_VALIDATE_EMAIL) ? null : 'Format email belum benar.'));
        $kataSandi = (string) ($this->option('password') ?: password('Kata sandi admin (minimal 10 karakter)', required: true, validate: fn (string $nilai) => mb_strlen($nilai) >= 10 ? null : 'Minimal 10 karakter.'));

        if (mb_strlen($kataSandi) < 10) {
            $this->error('Kata sandi minimal 10 karakter.');

            return self::FAILURE;
        }

        try {
            $buat->handle($nama, $email, $kataSandi);
        } catch (EmailSudahTerdaftar $galat) {
            $this->error($galat->getMessage());

            return self::FAILURE;
        }

        $this->info("Admin {$email} berhasil dibuat.");

        return self::SUCCESS;
    }
}
