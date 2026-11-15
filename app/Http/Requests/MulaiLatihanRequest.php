<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Latihan\Domain\ModeLatihan;

final class MulaiLatihanRequest extends FormRequest
{
    public function rules(): array
    {
        return ['mode' => ['required', Rule::enum(ModeLatihan::class)]];
    }
}
