<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PeluangInstansiRequest;
use App\Http\Resources\PeluangResource;
use Inertia\Inertia;
use Inertia\Response;
use Src\Formasi\Application\AmbilPeluangInstansi;

final class PeluangInstansiController extends Controller
{
    public function __invoke(PeluangInstansiRequest $request, AmbilPeluangInstansi $ambil): Response
    {
        $filter = $request->filter();

        return Inertia::render('PeluangInstansi', PeluangResource::dari($ambil->handle($filter)) + [
            'filter' => ['cari' => $filter->cari ?? '', 'tipe' => $filter->tipe ?? '', 'tingkat' => $filter->tingkat ?? '', 'urut' => $filter->urut],
        ]);
    }
}
