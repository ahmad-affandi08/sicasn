<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Src\Pantauan\Domain\BatasPantauan;

final class CatatanPantauanRequest extends FormRequest
{
    public function rules(): array
    {
        return ['catatan' => ['nullable', 'string', 'max:'.BatasPantauan::MAKS_CATATAN]];
    }

    public function messages(): array
    {
        return ['catatan.max' => 'Catatan maksimal '.BatasPantauan::MAKS_CATATAN.' karakter.'];
    }
}
