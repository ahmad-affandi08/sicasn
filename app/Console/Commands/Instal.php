<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\BankSoalSeeder;
use Database\Seeders\StatistikPelamarSeeder;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Src\DataImport\Application\SyncFormasiFromBkn;
use Src\Identity\Application\AdaAdmin;
use Src\Pengaturan\Application\AmbilKesiapan;
use Src\Pengaturan\Application\AmbilPengaturanSitus;
use Src\Pengaturan\Application\AturUrlSitus;
use Src\Shared\Application\PeriksaLingkungan;

use function Laravel\Prompts\text;

final class Instal extends Command
{
    protected $signature = 'sicasn:install {--nama=} {--email=} {--password=} {--url=} {--lewati-sinkron}';

    protected $description = 'Menyiapkan SICASN di mesin baru: pemeriksaan lingkungan, migrasi, admin pertama, data awal, dan sinkron BKN';

    public function handle(
        PeriksaLingkungan $periksa,
        AdaAdmin $adaAdmin,
        AmbilPengaturanSitus $situs,
        AturUrlSitus $aturUrl,
        SyncFormasiFromBkn $sinkron,
        AmbilKesiapan $kesiapan,
    ): int {
        $this->components->info('Instalasi SICASN');

        $this->siapkanBerkasEnv();

        if (! $this->tampilkan($periksa->sebelumDatabase(), $periksa)) {
            $this->components->error('Perbaiki butir yang gagal di atas, lalu jalankan perintah ini lagi.');

            return self::FAILURE;
        }

        if (blank(config('app.key'))) {
            $this->call('key:generate', ['--force' => true]);
        }

        if (! $this->tampilkan([$periksa->database()], $periksa)) {
            $this->components->error('Isi DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, dan DB_PASSWORD di berkas .env, lalu jalankan perintah ini lagi.');

            return self::FAILURE;
        }

        $this->components->task('Menjalankan migrasi', fn (): bool => $this->callSilently('migrate', ['--force' => true]) === self::SUCCESS);

        if (! $adaAdmin->handle() && ! $this->buatAdmin()) {
            return self::FAILURE;
        }

        $this->aturUrl($situs->handle()->url, $aturUrl);

        $this->components->task('Memuat statistik pelamar CPNS 2024', fn (): bool => $this->callSilently('db:seed', ['--class' => StatistikPelamarSeeder::class, '--force' => true]) === self::SUCCESS);

        $this->components->task('Memuat bank soal latihan SKD', fn (): bool => $this->callSilently('db:seed', ['--class' => BankSoalSeeder::class, '--force' => true]) === self::SUCCESS);

        $this->jalankanSinkron($sinkron);
        $this->cetakDaftarPeriksa($kesiapan);

        return self::SUCCESS;
    }

    private function siapkanBerkasEnv(): void
    {
        if (! file_exists(base_path('.env')) && file_exists(base_path('.env.example'))) {
            copy(base_path('.env.example'), base_path('.env'));
            $this->components->warn('Berkas .env dibuat dari .env.example. Isi data database di dalamnya bila belum benar.');
        }
    }

    private function tampilkan(array $pemeriksaan, PeriksaLingkungan $periksa): bool
    {
        foreach ($pemeriksaan as $butir) {
            $this->components->twoColumnDetail($butir->nama, $butir->lolos ? "<fg=green>OK</> {$butir->keterangan}" : "<fg=red>GAGAL</> {$butir->keterangan}");
        }

        return $periksa->semuaLolos($pemeriksaan);
    }

    private function buatAdmin(): bool
    {
        if (! $this->input->isInteractive() && ($this->option('email') === null || $this->option('password') === null || $this->option('nama') === null)) {
            $this->components->error('Mode tanpa interaksi memerlukan opsi --nama, --email, dan --password.');

            return false;
        }

        $opsi = array_filter([
            '--nama' => $this->option('nama'),
            '--email' => $this->option('email'),
            '--password' => $this->option('password'),
        ]);

        return $this->call('sicasn:buat-admin', $opsi) === self::SUCCESS;
    }

    private function aturUrl(?string $tersimpan, AturUrlSitus $aturUrl): void
    {
        $url = $this->option('url');

        if ($url === null && $tersimpan === null && $this->input->isInteractive()) {
            $url = text('Alamat situs (contoh https://sicasn.example.com)', default: (string) config('app.url'));
        }

        if ($url === null) {
            return;
        }

        try {
            $aturUrl->handle((string) $url);
            $this->components->info("Alamat situs disimpan: {$url}");
        } catch (InvalidArgumentException $galat) {
            $this->components->warn($galat->getMessage().' Alamat situs bisa diatur nanti di Admin > Pengaturan > Situs.');
        }
    }

    private function jalankanSinkron(SyncFormasiFromBkn $sinkron): void
    {
        if ($this->option('lewati-sinkron')) {
            $this->components->warn('Sinkron BKN dilewati. Jalankan "php artisan sicasn:sync-formasi" nanti.');

            return;
        }

        $hasil = $sinkron->handle();
        $hasil->berhasil
            ? $this->components->info("Sinkron BKN: {$hasil->pesan}")
            : $this->components->warn("Sinkron BKN belum berhasil: {$hasil->pesan} Instalasi tetap lengkap; sinkron akan dicoba otomatis oleh penjadwal.");
    }

    private function cetakDaftarPeriksa(AmbilKesiapan $kesiapan): void
    {
        $siap = $kesiapan->handle();

        $this->newLine();
        $this->components->info('Daftar periksa');
        $this->components->twoColumnDetail('Email pengirim kredensial', $siap->email ? '<fg=green>Siap</>' : '<fg=yellow>Belum diatur</> (Admin > Pengaturan > Email)');
        $this->components->twoColumnDetail('Nomor WhatsApp admin', $siap->whatsapp ? '<fg=green>Siap</>' : '<fg=yellow>Belum diisi</> (Admin > Pengaturan > WhatsApp)');
        $this->components->twoColumnDetail('Harga dan rekening', $siap->pembayaran ? '<fg=green>Siap</>' : '<fg=yellow>Belum diisi</> (Admin > Pengaturan > Pembayaran)');
        $this->components->twoColumnDetail('Penjadwal (cron)', '<fg=yellow>Pastikan aktif</> tiap menit: php artisan schedule:run');
        $this->newLine();
        $this->line('Masuk ke /masuk memakai akun admin, lalu selesaikan butir yang belum siap di dasbor.');
    }
}
