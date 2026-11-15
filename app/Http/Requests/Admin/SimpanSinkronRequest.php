<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\DataImport\Domain\FrekuensiSinkron;

final class SimpanSinkronRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url:https', 'max:255'],
            'frekuensi' => ['required', Rule::enum(FrekuensiSinkron::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'Alamat sumber data wajib diisi.',
            'url.url' => 'Alamat sumber data harus berupa URL https yang valid.',
            'frekuensi.required' => 'Pilih frekuensi sinkron.',
        ];
    }
}
