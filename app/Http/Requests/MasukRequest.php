<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class MasukRequest extends FormRequest
{
    private const BATAS_PERCOBAAN = 5;

    private const DETIK_KUNCI = 60;

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'ingat' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    public function pastikanTidakTerbatasi(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciBatas(), self::BATAS_PERCOBAAN)) {
            return;
        }

        $detik = RateLimiter::availableIn($this->kunciBatas());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
        ]);
    }

    public function catatGagal(): void
    {
        RateLimiter::hit($this->kunciBatas(), self::DETIK_KUNCI);
    }

    public function bersihkanBatas(): void
    {
        RateLimiter::clear($this->kunciBatas());
    }

    private function kunciBatas(): string
    {
        return 'masuk:'.mb_strtolower((string) $this->input('email')).'|'.$this->ip();
    }
}
