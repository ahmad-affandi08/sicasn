<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BandingkanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'array', 'max:10'],
            'id.*' => ['integer', 'min:1'],
        ];
    }

    public function ids(): array
    {
        return array_map('intval', (array) $this->input('id', []));
    }
}
