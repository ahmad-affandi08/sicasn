<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BuatPenggunaRequest;
use App\Http\Requests\Admin\NonaktifkanRequest;
use App\Http\Resources\PenggunaResource;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Identity\Application\ActivateMember;
use Src\Identity\Application\AmbilPengguna;
use Src\Identity\Application\AmbilRiwayatAudit;
use Src\Identity\Application\CreateMemberAccount;
use Src\Identity\Application\DaftarPengguna;
use Src\Identity\Application\DeactivateMember;
use Src\Identity\Application\PenggunaBaru;
use Src\Identity\Application\ResendCredentials;
use Src\Identity\Domain\Exception\EmailSudahTerdaftar;
use Src\Identity\Domain\Exception\TidakBolehMenonaktifkan;

final class PenggunaController extends Controller
{
    public function index(Request $request, DaftarPengguna $daftar): Response
    {
        $status = (string) $request->query('status', 'semua');
        $halaman = $daftar->handle($request->query('cari'), match ($status) {
            'aktif' => true,
            'nonaktif' => false,
            default => null,
        }, (int) $request->query('halaman', 1));

        return Inertia::render('Admin/Pengguna/Index', [
            'pengguna' => array_map(PenggunaResource::dari(...), $halaman->item),
            'paginasi' => ['total' => $halaman->total, 'halaman' => $halaman->halaman, 'terakhir' => $halaman->halamanTerakhir],
            'filter' => ['cari' => (string) $request->query('cari', ''), 'status' => $status],
        ]);
    }

    public function buat(): Response
    {
        return Inertia::render('Admin/Pengguna/Buat');
    }

    public function simpan(BuatPenggunaRequest $request, CreateMemberAccount $buat): RedirectResponse
    {
        try {
            $id = $buat->handle(new PenggunaBaru(
                (string) $request->input('nama'),
                (string) $request->input('email'),
                $request->filled('no_whatsapp') ? (string) $request->input('no_whatsapp') : null,
                $request->filled('catatan_pembayaran') ? (string) $request->input('catatan_pembayaran') : null,
                route('masuk'),
                (int) $request->user()->getAuthIdentifier(),
            ));
        } catch (EmailSudahTerdaftar $galat) {
            return back()->withErrors(['email' => $galat->getMessage()])->withInput();
        }

        return redirect()->route('admin.pengguna.tampil', $id)->with('dialog', DialogFlash::sukses('Akun berhasil dibuat', 'Email berisi kata sandi sementara sedang dikirim ke pengguna. Pantau statusnya di halaman ini.'));
    }

    public function tampil(int $id, AmbilPengguna $ambil, AmbilRiwayatAudit $riwayat): Response
    {
        return Inertia::render('Admin/Pengguna/Tampil', [
            'pengguna' => PenggunaResource::dari($ambil->handle($id)),
            'riwayat' => array_map(fn ($c) => ['aksi' => $c->aksi, 'aktor' => $c->aktor, 'rincian' => $c->rincian, 'waktu' => $c->waktu], $riwayat->handle($id)),
        ]);
    }

    public function kirimUlang(int $id, Request $request, ResendCredentials $kirimUlang): RedirectResponse
    {
        $kirimUlang->handle($id, (int) $request->user()->getAuthIdentifier(), route('masuk'));

        return back()->with('dialog', DialogFlash::sukses('Kredensial baru dikirim', 'Kata sandi sementara yang baru sedang dikirim lewat email. Kata sandi lama tidak berlaku lagi.'));
    }

    public function nonaktifkan(int $id, NonaktifkanRequest $request, DeactivateMember $nonaktifkan): RedirectResponse
    {
        try {
            $nonaktifkan->handle($id, (int) $request->user()->getAuthIdentifier(), $request->input('alasan'));
        } catch (TidakBolehMenonaktifkan $galat) {
            return back()->with('dialog', DialogFlash::gagal('Tidak dapat menonaktifkan', $galat->getMessage()));
        }

        return back()->with('dialog', DialogFlash::sukses('Akun dinonaktifkan', 'Pengguna ini tidak bisa masuk sampai Anda mengaktifkannya kembali.'));
    }

    public function aktifkan(int $id, Request $request, ActivateMember $aktifkan): RedirectResponse
    {
        $aktifkan->handle($id, (int) $request->user()->getAuthIdentifier());

        return back()->with('dialog', DialogFlash::sukses('Akun diaktifkan', 'Pengguna ini bisa masuk kembali.'));
    }
}
