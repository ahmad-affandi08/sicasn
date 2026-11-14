<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class GantiPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password_lama' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'different:password_lama', Password::min(10)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
            'password.min' => 'Kata sandi baru minimal 10 karakter.',
            'password.letters' => 'Kata sandi baru harus mengandung huruf.',
            'password.numbers' => 'Kata sandi baru harus mengandung angka.',
        ];
    }
}
