<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Src\Formasi\Application\FilterFormasi;
use Src\Formasi\Domain\TingkatPersaingan;

final class FormasiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cari' => ['nullable', 'string', 'max:100'],
            'jenjang' => ['nullable', 'array', 'max:12'],
            'jenjang.*' => ['string', 'regex:/^[A-Z0-9]{1,15}$/'],
            'jurusan' => ['nullable', 'array', 'max:10'],
            'jurusan.*' => ['integer', 'min:1'],
            'provinsi' => ['nullable', 'integer', 'min:1'],
            'wilayah' => ['nullable', 'integer', 'min:1'],
            'instansi' => ['nullable', 'integer', 'min:1'],
            'jenis' => ['nullable', 'string', 'max:60'],
            'tingkat' => ['nullable', 'in:'.implode(',', array_map(fn (TingkatPersaingan $t) => $t->value, TingkatPersaingan::cases()))],
            'urut' => ['nullable', 'in:'.implode(',', FilterFormasi::URUTAN)],
            'sertakan' => ['nullable', 'in:1'],
            'halaman' => ['nullable', 'integer', 'min:1', 'max:'.FilterFormasi::HALAMAN_MAKS],
        ];
    }

    public function filter(): FilterFormasi
    {
        return new FilterFormasi(
            cari: $this->filled('cari') ? trim((string) $this->input('cari')) : null,
            jenjang: array_values(array_unique((array) $this->input('jenjang', []))),
            jurusan: array_values(array_unique(array_map('intval', (array) $this->input('jurusan', [])))),
            provinsi: $this->filled('provinsi') ? (int) $this->input('provinsi') : null,
            wilayah: $this->filled('wilayah') ? (int) $this->input('wilayah') : null,
            instansi: $this->filled('instansi') ? (int) $this->input('instansi') : null,
            jenisFormasi: $this->filled('jenis') ? (string) $this->input('jenis') : null,
            tingkat: $this->filled('tingkat') ? (string) $this->input('tingkat') : null,
            urut: (string) ($this->input('urut') ?: FilterFormasi::URUTAN[0]),
            sertakanTanpaKualifikasi: $this->input('sertakan') === '1',
            halaman: (int) ($this->input('halaman') ?: 1),
        );
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(redirect()->route('formasi'));
    }
}
