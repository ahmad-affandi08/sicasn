<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SimpanSitusRequest extends FormRequest
{
    public function rules(): array
    {
        return ['satu_sesi' => ['required', 'boolean']];
    }
}
