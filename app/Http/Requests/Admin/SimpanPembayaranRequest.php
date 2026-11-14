<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SimpanPembayaranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rekening' => ['nullable', 'array', 'max:5'],
            'rekening.*.bank' => ['nullable', 'string', 'max:60'],
            'rekening.*.nomor' => ['nullable', 'string', 'max:40'],
            'rekening.*.atas_nama' => ['nullable', 'string', 'max:100'],
            'harga_teks' => ['nullable', 'string', 'max:120'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'tampilkan_rekening_publik' => ['boolean'],
        ];
    }
}
