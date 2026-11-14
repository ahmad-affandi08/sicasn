<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class NonaktifkanRequest extends FormRequest
{
    public function rules(): array
    {
        return ['alasan' => ['nullable', 'string', 'max:255']];
    }
}
