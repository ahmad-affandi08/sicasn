<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UjiEmailRequest extends FormRequest
{
    public function rules(): array
    {
        return ['tujuan' => ['required', 'string', 'email', 'max:190']];
    }

    public function messages(): array
    {
        return [
            'tujuan.required' => 'Alamat email tujuan wajib diisi.',
            'tujuan.email' => 'Format email tujuan belum benar.',
        ];
    }
}
