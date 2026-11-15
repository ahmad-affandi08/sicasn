<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FormasiRequest;
use App\Http\Resources\FormasiResource;
use Inertia\Inertia;
use Inertia\Response;
use Src\Formasi\Application\AmbilDetailFormasi;
use Src\Formasi\Application\AmbilOpsiFormasi;
use Src\Formasi\Application\SearchFormasi;

final class FormasiController extends Controller
{
    public function index(FormasiRequest $request, SearchFormasi $cari, AmbilOpsiFormasi $opsi): Response
    {
        $filter = $request->filter();

        return Inertia::render('Formasi/Index', FormasiResource::halaman($cari->handle($filter), $opsi->handle($filter)) + [
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

    public function tampil(int $id, AmbilDetailFormasi $ambil): Response
    {
        $detail = $ambil->handle($id);

        abort_if($detail === null, 404);

        return Inertia::render('Formasi/Detail', FormasiResource::detail($detail));
    }
}
