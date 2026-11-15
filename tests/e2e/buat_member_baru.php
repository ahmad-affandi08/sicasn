<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use Src\Identity\Domain\Peran;
use Src\Identity\Infrastructure\Persistence\UserModel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

UserModel::query()->updateOrCreate(['email' => 'baru@uji.test'], [
    'name' => 'Member Baru',
    'password' => Hash::make('MemberBaru12345'),
    'peran' => Peran::Member,
    'aktif' => true,
    'wajib_ganti_password' => false,
    'panduan_dilihat_at' => null,
]);

echo 'baru@uji.test siap', PHP_EOL;
