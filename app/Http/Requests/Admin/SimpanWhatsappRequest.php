<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SimpanWhatsappRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nomor' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-()]+$/'],
            'nama_tampilan' => ['nullable', 'string', 'max:60'],
            'template_pesan' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return ['nomor.regex' => 'Nomor hanya boleh berisi angka, spasi, tanda plus, atau tanda hubung.'];
    }
}
