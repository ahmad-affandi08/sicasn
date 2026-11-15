<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FormasiRequest;
use App\Http\Resources\FormasiResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Formasi\Application\AmbilDetailFormasi;
use Src\Formasi\Application\AmbilOpsiFormasi;
use Src\Formasi\Application\SearchFormasi;
use Src\Pantauan\Application\AmbilStatusPantauan;

final class FormasiController extends Controller
{
    public function index(FormasiRequest $request, SearchFormasi $cari, AmbilOpsiFormasi $opsi, AmbilStatusPantauan $status): Response
    {
        $filter = $request->filter();
        $hasil = $cari->handle($filter);

        return Inertia::render('Formasi/Index', FormasiResource::halaman($hasil, $opsi->handle($filter)) + [
            'dipantau' => $status->handle((int) $request->user()->getAuthIdentifier(), array_map(fn ($i) => $i->formasi->id, $hasil->item)),
            'filter' => [
                'cari' => $filter->cari ?? '',
                'jenjang' => $filter->jenjang,
                'jurusan' => $filter->jurusan,
                'provinsi' => $filter->provinsi,
                'wilayah' => $filter->wilayah,
                'instansi' => $filter->instansi,
                'jenis' => $filter->jenisFormasi ?? '',
                'tingkat' => $filter->tingkat ?? '',
                'urut' => $filter->urut,
                'sertakan' => $filter->sertakanTanpaKualifikasi,
            ],
        ]);
    }

    public function tampil(Request $request, int $id, AmbilDetailFormasi $ambil, AmbilStatusPantauan $status): Response
    {
        $detail = $ambil->handle($id);

        abort_if($detail === null, 404);

        return Inertia::render('Formasi/Detail', FormasiResource::detail($detail) + ['dipantau' => $status->handle((int) $request->user()->getAuthIdentifier(), [$id]) !== []]);
    }
}
