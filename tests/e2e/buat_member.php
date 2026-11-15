<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use Src\Identity\Domain\Peran;
use Src\Identity\Infrastructure\Persistence\UserModel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

UserModel::query()->updateOrCreate(['email' => 'member@uji.test'], [
    'name' => 'Member Uji',
    'password' => Hash::make('MemberUji12345'),
    'peran' => Peran::Member,
    'aktif' => true,
    'wajib_ganti_password' => false,
    'panduan_dilihat_at' => now(),
]);

echo 'member@uji.test siap', PHP_EOL;
