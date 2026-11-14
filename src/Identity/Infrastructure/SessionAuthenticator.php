<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Src\Identity\Application\Authenticator;
use Src\Identity\Domain\HasilLogin;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Shared\Application\Clock;

final class SessionAuthenticator implements Authenticator
{
    private static ?string $hashPalsu = null;

    public function __construct(private readonly Clock $clock) {}

    public function coba(string $email, string $password, bool $ingat): HasilLogin
    {
        $pengguna = UserModel::query()->where('email', $email)->first();

        if ($pengguna === null) {
            Hash::check($password, self::$hashPalsu ??= Hash::make('palsu'));

            return HasilLogin::KredensialSalah;
        }

        if (! Hash::check($password, $pengguna->password)) {
            return HasilLogin::KredensialSalah;
        }

        if (! $pengguna->aktif) {
            return HasilLogin::AkunNonaktif;
        }

        $batas = $pengguna->password_sementara_berlaku_sampai;

        if ($pengguna->wajib_ganti_password && $batas !== null && $batas->lessThan($this->clock->sekarang())) {
            return HasilLogin::PasswordSementaraKedaluwarsa;
        }

        Auth::login($pengguna, $ingat);
        session()->regenerate();

        $pengguna->forceFill([
            'last_login_at' => $this->clock->sekarang(),
            'sesi_aktif_id' => session()->getId(),
        ])->save();

        return HasilLogin::Berhasil;
    }

    public function keluar(): void
    {
        $pengguna = Auth::user();

        if ($pengguna instanceof UserModel) {
            $pengguna->forceFill(['sesi_aktif_id' => null])->save();
        }

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
