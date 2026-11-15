<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class PratinjauStatistikRequest extends FormRequest
{
    public function rules(): array
    {
        return ['berkas' => ['required', 'file', 'extensions:csv,txt', 'max:2048']];
    }

    public function messages(): array
    {
        return [
            'berkas.required' => 'Pilih berkas CSV terlebih dahulu.',
            'berkas.file' => 'Berkas tidak dapat dibaca. Coba pilih ulang.',
            'berkas.extensions' => 'Berkas harus berformat CSV.',
            'berkas.max' => 'Ukuran berkas maksimal 2 MB.',
            'berkas.uploaded' => 'Unggahan gagal. Periksa ukuran berkas dan coba lagi.',
        ];
    }
}
