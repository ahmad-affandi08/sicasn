<?php

declare(strict_types=1);

namespace Database\Factories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Identity\Domain\Peran;
use Src\Identity\Infrastructure\Persistence\UserModel;

final class UserFactory extends Factory
{
    protected $model = UserModel::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => self::$password ??= Hash::make('password'),
            'peran' => Peran::Member,
            'aktif' => true,
            'wajib_ganti_password' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): self
    {
        return $this->state(['peran' => Peran::Admin]);
    }

    public function nonaktif(?string $alasan = null): self
    {
        return $this->state(['aktif' => false, 'alasan_nonaktif' => $alasan]);
    }

    public function denganPasswordSementara(?DateTimeInterface $berlakuSampai = null): self
    {
        return $this->state([
            'wajib_ganti_password' => true,
            'password_sementara_berlaku_sampai' => $berlakuSampai ?? now()->addDays(7),
        ]);
    }
}
