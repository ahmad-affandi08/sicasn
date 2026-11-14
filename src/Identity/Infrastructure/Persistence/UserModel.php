<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure\Persistence;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Src\Identity\Domain\Peran;

#[Hidden(['password', 'remember_token', 'sesi_aktif_id'])]
final class UserModel extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function adalahAdmin(): bool
    {
        return $this->peran === Peran::Admin;
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'peran' => Peran::class,
            'aktif' => 'boolean',
            'wajib_ganti_password' => 'boolean',
            'diaktifkan_at' => 'datetime',
            'dinonaktifkan_at' => 'datetime',
            'password_sementara_berlaku_sampai' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }
}
