<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SimpanPersainganRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'longgar_maks' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'ketat_min' => ['required', 'numeric', 'gt:longgar_maks', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'longgar_maks.required' => 'Batas Longgar wajib diisi.',
            'longgar_maks.numeric' => 'Batas Longgar harus berupa angka.',
            'longgar_maks.gt' => 'Batas Longgar harus lebih besar dari 0.',
            'longgar_maks.max' => 'Batas Longgar maksimal 10.000.',
            'ketat_min.required' => 'Batas Ketat wajib diisi.',
            'ketat_min.numeric' => 'Batas Ketat harus berupa angka.',
            'ketat_min.gt' => 'Batas Ketat harus lebih besar dari batas Longgar.',
            'ketat_min.max' => 'Batas Ketat maksimal 10.000.',
        ];
    }
}
