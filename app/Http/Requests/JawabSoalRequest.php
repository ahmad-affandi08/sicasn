<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class JawabSoalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'soal_id' => ['required', 'integer', 'min:1'],
            'opsi_id' => ['nullable', 'integer', 'min:1'],
            'ragu' => ['required', 'boolean'],
        ];
    }
}
