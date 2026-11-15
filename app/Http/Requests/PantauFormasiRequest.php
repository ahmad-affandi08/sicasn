<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PantauFormasiRequest extends FormRequest
{
    public function rules(): array
    {
        return ['formasi_id' => ['required', 'integer', 'min:1']];
    }
}
