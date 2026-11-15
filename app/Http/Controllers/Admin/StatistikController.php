<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PratinjauStatistikRequest;
use App\Http\Resources\StatistikResource;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as ResponsePolos;
use Inertia\Inertia;
use Inertia\Response;
use Src\DataImport\Application\AmbilPratinjauImpor;
use Src\DataImport\Application\AmbilRiwayatImporStatistik;
use Src\DataImport\Application\EksporGalatImpor;
use Src\DataImport\Application\KonfirmasiImporStatistik;
use Src\DataImport\Application\PratinjauImporStatistik;
use Src\DataImport\Application\RollbackBatchStatistik;
use Src\DataImport\Application\TemplateCsvStatistik;
use Src\DataImport\Domain\Exception\BatchTidakBisaDibatalkan;
use Src\DataImport\Domain\Exception\PratinjauTidakDitemukan;

final class StatistikController extends Controller
{
    public function index(AmbilRiwayatImporStatistik $riwayat): Response
    {
        $hasil = $riwayat->handle();

        return Inertia::render('Admin/Statistik/Index', [
            'riwayat' => array_map(StatistikResource::riwayat(...), $hasil['riwayat']),
            'totalBaris' => $hasil['totalBaris'],
        ]);
    }

    public function pratinjau(PratinjauStatistikRequest $request, PratinjauImporStatistik $pratinjau): RedirectResponse
    {
        $berkas = $request->file('berkas');
        $hasil = $pratinjau->handle((string) file_get_contents($berkas->getRealPath()), $berkas->getClientOriginalName());

        if ($hasil->token === null) {
            return back()->with('dialog', DialogFlash::gagal('Berkas belum bisa diimpor', $hasil->galatBerkas ?? 'Isi berkas tidak dapat dibaca.'));
        }

        return to_route('admin.statistik.pratinjau.tampil', ['token' => $hasil->token]);
    }

    public function tampilPratinjau(string $token, AmbilPratinjauImpor $ambil): Response|RedirectResponse
    {
        $hasil = $ambil->handle($token);

        if ($hasil === null) {
            return to_route('admin.statistik.index')->with('dialog', DialogFlash::peringatan('Pratinjau tidak ditemukan', PratinjauTidakDitemukan::untuk($token)->getMessage()));
        }

        return Inertia::render('Admin/Statistik/Pratinjau', ['pratinjau' => StatistikResource::pratinjau($hasil)]);
    }

    public function konfirmasi(Request $request, string $token, KonfirmasiImporStatistik $konfirmasi): RedirectResponse
    {
        try {
            $hasil = $konfirmasi->handle($token, (int) $request->user()->getAuthIdentifier());
        } catch (PratinjauTidakDitemukan $galat) {
            return to_route('admin.statistik.index')->with('dialog', DialogFlash::peringatan('Pratinjau tidak ditemukan', $galat->getMessage()));
        }

        return to_route('admin.statistik.index')->with('dialog', DialogFlash::sukses('Impor selesai', "{$hasil->baru} baris baru disimpan, {$hasil->duplikat} sudah ada, {$hasil->galat} baris dilewati."));
    }

    public function rollback(Request $request, int $id, RollbackBatchStatistik $rollback): RedirectResponse
    {
        try {
            $dihapus = $rollback->handle($id, (int) $request->user()->getAuthIdentifier());
        } catch (BatchTidakBisaDibatalkan $galat) {
            return back()->with('dialog', DialogFlash::gagal('Impor belum bisa dibatalkan', $galat->getMessage()));
        }

        return back()->with('dialog', DialogFlash::sukses('Impor dibatalkan', "{$dihapus} baris statistik dari impor ini sudah dihapus."));
    }

    public function galat(int $id, EksporGalatImpor $ekspor): ResponsePolos
    {
        $isi = $ekspor->handle($id);

        abort_if($isi === null, 404);

        return response($isi, 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"galat-impor-{$id}.csv\""]);
    }

    public function templateCsv(): ResponsePolos
    {
        return response("\u{FEFF}".TemplateCsvStatistik::isi(), 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="template-statistik-pelamar.csv"']);
    }
}
