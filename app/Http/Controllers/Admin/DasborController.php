<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SinkronResource;
use Inertia\Inertia;
use Inertia\Response;
use Src\DataImport\Application\AmbilEventBaru;
use Src\DataImport\Application\AmbilStatusSinkron;
use Src\Identity\Application\AmbilRingkasanPengguna;
use Src\Pengaturan\Application\AmbilKesiapan;

final class DasborController extends Controller
{
    public function __invoke(AmbilRingkasanPengguna $ringkasan, AmbilKesiapan $kesiapan, AmbilStatusSinkron $sinkron, AmbilEventBaru $eventBaru): Response
    {
        $pengguna = $ringkasan->handle();
        $siap = $kesiapan->handle();
        $status = $sinkron->handle();

        return Inertia::render('Admin/Dasbor', [
            'ringkasan' => ['aktif' => $pengguna->aktif, 'nonaktif' => $pengguna->nonaktif],
            'kesiapan' => ['email' => $siap->email, 'whatsapp' => $siap->whatsapp, 'pembayaran' => $siap->pembayaran, 'sinkron' => $status->terakhirBerhasil !== null],
            'sinkron' => ['terakhir' => $status->terakhirBerhasil === null ? null : SinkronResource::batch($status->terakhirBerhasil), 'formasiAktif' => $status->formasiAktif],
            'eventBaru' => array_map(SinkronResource::event(...), $eventBaru->handle()),
        ]);
    }
}
