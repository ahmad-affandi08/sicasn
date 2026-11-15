<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Src\Identity\Application\TandaiPanduanDilihat;

final class PanduanController extends Controller
{
    public function __invoke(Request $request, TandaiPanduanDilihat $tandai): RedirectResponse
    {
        $tandai->handle((int) $request->user()->getAuthIdentifier());

        return back();
    }
}
