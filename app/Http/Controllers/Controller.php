<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function penggunaId(Request $request): int
    {
        return (int) $request->user()->getAuthIdentifier();
    }
}
