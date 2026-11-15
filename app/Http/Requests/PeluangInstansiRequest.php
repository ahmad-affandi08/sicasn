<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Src\Formasi\Application\FilterPeluang;
use Src\Formasi\Domain\TingkatPersaingan;
use Src\Formasi\Domain\TipeInstansi;

final class PeluangInstansiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cari' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'in:'.TipeInstansi::Pusat->value.','.TipeInstansi::Daerah->value.','.TipeInstansi::TidakDiketahui->value],
            'tingkat' => ['nullable', 'in:'.implode(',', array_map(fn (TingkatPersaingan $t) => $t->value, TingkatPersaingan::cases()))],
            'urut' => ['nullable', 'in:'.implode(',', FilterPeluang::URUTAN)],
            'halaman' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function filter(): FilterPeluang
    {
        return new FilterPeluang(
            cari: $this->filled('cari') ? trim((string) $this->input('cari')) : null,
            tipe: $this->filled('tipe') ? (string) $this->input('tipe') : null,
            tingkat: $this->filled('tingkat') ? (string) $this->input('tingkat') : null,
            urut: (string) ($this->input('urut') ?: FilterPeluang::URUTAN[0]),
            halaman: (int) ($this->input('halaman') ?: 1),
        );
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(redirect()->route('peluang-instansi'));
    }
}
