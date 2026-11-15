<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Src\Pengaturan\Application\AmbilPengaturanSitus;

function opsiInstal(array $tambahan = []): array
{
    return [
        '--nama' => 'Admin Pertama',
        '--email' => 'admin@contoh.id',
        '--password' => 'KataSandiKuat123',
        '--url' => 'https://sicasn.contoh.id',
        '--lewati-sinkron' => true,
        ...$tambahan,
    ];
}

it('menyiapkan instalasi baru: admin pertama, alamat situs, dan statistik awal', function (): void {
    $this->artisan('sicasn:install', opsiInstal())->assertSuccessful();

    expect(DB::table('users')->where('email', 'admin@contoh.id')->value('peran'))->toBe('admin')
        ->and(app(AmbilPengaturanSitus::class)->handle()->url)->toBe('https://sicasn.contoh.id')
        ->and(DB::table('statistik_pelamars')->count())->toBeGreaterThan(90)
        ->and(DB::table('soals')->where('aktif', true)->count())->toBeGreaterThan(400);
});

it('aman diulang: tidak membuat admin kedua dan tidak menggandakan statistik', function (): void {
    $this->artisan('sicasn:install', opsiInstal())->assertSuccessful();
    $statistik = DB::table('statistik_pelamars')->count();

    $this->artisan('sicasn:install', opsiInstal(['--email' => 'lain@contoh.id']))->assertSuccessful();

    expect(DB::table('users')->where('peran', 'admin')->count())->toBe(1)
        ->and(DB::table('statistik_pelamars')->count())->toBe($statistik);
});

it('menjalankan sinkron BKN pertama dan mencetak ringkasannya, termasuk saat belum ada formasi CPNS', function (): void {
    fakeBkn(array_values(array_filter(barisBkn(), fn (array $baris) => strtoupper((string) $baris['jenis_pengadaan']) !== 'CPNS')));

    $this->artisan('sicasn:install', opsiInstal(['--lewati-sinkron' => false]))
        ->expectsOutputToContain('Sinkron BKN')
        ->assertSuccessful();

    expect(DB::table('import_batches')->where('tipe', 'sinkron_bkn')->count())->toBe(1)
        ->and(DB::table('formasis')->count())->toBe(0);
});

it('tetap selesai dengan peringatan bila sumber BKN tidak terjangkau saat instalasi', function (): void {
    fakeBknMentah('Forbidden', 403, 'text/html');

    $this->artisan('sicasn:install', opsiInstal(['--lewati-sinkron' => false]))
        ->expectsOutputToContain('Sinkron BKN belum berhasil')
        ->assertSuccessful();

    expect(DB::table('users')->where('peran', 'admin')->count())->toBe(1);
});

it('mencetak daftar periksa yang mengarahkan admin ke dasbor', function (): void {
    $this->artisan('sicasn:install', opsiInstal())
        ->expectsOutputToContain('Daftar periksa')
        ->expectsOutputToContain('Penjadwal (cron)')
        ->assertSuccessful();
});

it('gagal dengan pesan jelas bila mode tanpa interaksi tidak membawa data admin', function (): void {
    $this->artisan('sicasn:install', ['--lewati-sinkron' => true, '--no-interaction' => true])
        ->expectsOutputToContain('--nama, --email, dan --password')
        ->assertFailed();

    expect(DB::table('users')->count())->toBe(0);
});

it('menolak kata sandi admin yang terlalu pendek', function (): void {
    $this->artisan('sicasn:install', opsiInstal(['--password' => 'pendek']))->assertFailed();

    expect(DB::table('users')->count())->toBe(0);
});

it('tidak membatalkan instalasi hanya karena alamat situs tidak valid', function (): void {
    $this->artisan('sicasn:install', opsiInstal(['--url' => 'ftp://contoh.id']))
        ->expectsOutputToContain('Alamat situs harus diawali')
        ->assertSuccessful();

    expect(app(AmbilPengaturanSitus::class)->handle()->url)->toBeNull();
});
