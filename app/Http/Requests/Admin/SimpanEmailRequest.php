<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Pengaturan\Application\InputKonfigurasiSmtp;
use Src\Pengaturan\Application\InputPengaturanEmail;
use Src\Pengaturan\Domain\Enkripsi;
use Src\Pengaturan\Domain\PenyediaEmail;

final class SimpanEmailRequest extends FormRequest
{
    public function rules(): array
    {
        $penyedia = array_column(PenyediaEmail::cases(), 'value');

        return [
            'aktif' => ['required', Rule::in($penyedia)],
            'cadangan' => ['nullable', Rule::in($penyedia), 'different:aktif'],
            'pengirim_nama' => ['required', 'string', 'max:100'],
            'pengirim_alamat' => ['required', 'string', 'email', 'max:190'],
            'alamat_balas' => ['nullable', 'string', 'email', 'max:190'],
            'konfigurasi' => ['required', 'array'],
            'konfigurasi.*.host' => ['required', 'string', 'max:190'],
            'konfigurasi.*.port' => ['required', 'integer', 'between:1,65535'],
            'konfigurasi.*.enkripsi' => ['required', Rule::enum(Enkripsi::class)],
            'konfigurasi.*.username' => ['required', 'string', 'max:190'],
            'konfigurasi.*.password' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'aktif.required' => 'Pilih penyedia email aktif.',
            'cadangan.different' => 'Penyedia cadangan harus berbeda dari penyedia aktif.',
            'pengirim_nama.required' => 'Nama pengirim wajib diisi.',
            'pengirim_alamat.required' => 'Alamat email pengirim wajib diisi.',
            'pengirim_alamat.email' => 'Format alamat pengirim belum benar.',
            'alamat_balas.email' => 'Format alamat balas belum benar.',
            'konfigurasi.*.host.required' => 'Host SMTP wajib diisi.',
            'konfigurasi.*.port.required' => 'Port wajib diisi.',
            'konfigurasi.*.port.between' => 'Port harus antara 1 dan 65535.',
            'konfigurasi.*.username.required' => 'Username wajib diisi.',
        ];
    }

    public function keInput(): InputPengaturanEmail
    {
        $konfigurasi = [];

        foreach ((array) $this->input('konfigurasi') as $kode => $isi) {
            $konfigurasi[(string) $kode] = new InputKonfigurasiSmtp(
                (string) $isi['host'],
                (int) $isi['port'],
                (string) $isi['enkripsi'],
                (string) $isi['username'],
                isset($isi['password']) ? (string) $isi['password'] : null,
            );
        }

        return new InputPengaturanEmail(
            (string) $this->input('aktif'),
            $this->filled('cadangan') ? (string) $this->input('cadangan') : null,
            (string) $this->input('pengirim_nama'),
            (string) $this->input('pengirim_alamat'),
            $this->filled('alamat_balas') ? (string) $this->input('alamat_balas') : null,
            $konfigurasi,
        );
    }
}
