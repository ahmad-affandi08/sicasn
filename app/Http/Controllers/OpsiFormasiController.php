<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\FormasiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Formasi\Application\AmbilKabupatenKota;
use Src\Formasi\Application\CariOpsiInstansi;
use Src\Formasi\Application\CariOpsiJurusan;

final class OpsiFormasiController extends Controller
{
    public function instansi(Request $request, CariOpsiInstansi $cari): JsonResponse
    {
        return $this->balas($cari->handle($this->kataKunci($request)));
    }

    public function jurusan(Request $request, CariOpsiJurusan $cari): JsonResponse
    {
        return $this->balas($cari->handle($this->kataKunci($request)));
    }

    public function kabupatenKota(int $id, AmbilKabupatenKota $ambil): JsonResponse
    {
        return $this->balas($ambil->handle($id));
    }

    private function kataKunci(Request $request): string
    {
        return mb_substr((string) $request->query('q', ''), 0, 100);
    }

    private function balas(array $opsi): JsonResponse
    {
        return response()->json(['opsi' => array_map(FormasiResource::pilihan(...), $opsi)]);
    }
}
