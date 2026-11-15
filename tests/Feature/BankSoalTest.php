<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Latihan\Application\MuatBankSoal;
use Src\Latihan\Domain\Exception\SoalTidakValid;

beforeEach(function (): void {
    $this->member = UserModel::factory()->create();
    masukSebagai($this, $this->member);
    muatSoalUji();
});

it('idempoten: memuat berkas yang sama tidak menambah atau mengubah apa pun', function (): void {
    $baris = [barisSoal('tiu', 900, 1)];

    $pertama = app(MuatBankSoal::class)->handle($baris);
    $kedua = app(MuatBankSoal::class)->handle($baris);

    expect($pertama->baru)->toBe(1)->and($kedua->baru)->toBe(0)->and($kedua->diperbarui)->toBe(0);
});

it('menonaktifkan soal yang tidak lagi ada di berkas', function (): void {
    $hasil = app(MuatBankSoal::class)->handle([barisSoal('tiu', 1, 0)]);

    expect($hasil->dinonaktifkan)->toBe(129)->and(DB::table('soals')->where('aktif', true)->count())->toBe(1);
});

it('memperbarui isi soal tanpa mengganti id opsi sehingga jawaban lama tetap utuh', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals as ps')->join('soals as s', 's.id', '=', 'ps.soal_id')->where('ps.percobaan_id', $id)->first(['ps.id as ps_id', 's.id as soal_id', 's.pertanyaan']);
    $opsi = DB::table('soal_opsis')->where('soal_id', $soal->soal_id)->where('label', 'B')->first();
    DB::table('percobaan_soals')->where('id', $soal->ps_id)->update(['opsi_id' => $opsi->id]);

    $nomor = (int) preg_replace('/\D/', '', str_replace('Pertanyaan tiu nomor ', '', $soal->pertanyaan));
    $ubah = barisSoal('tiu', $nomor, $nomor % 5);
    $ubah['pembahasan'] = 'Pembahasan diperbaiki.';
    $ubah['opsi'][1]['teks'] = 'Teks B diperbaiki';
    $semua = [$ubah];

    foreach (DB::table('soals')->where('id', '!=', $soal->soal_id)->get() as $lain) {
        $kategori = $lain->kategori;
        $n = (int) preg_replace('/\D/', '', $lain->pertanyaan);
        $semua[] = barisSoal($kategori, $n, $n % 5);
    }

    $hasil = app(MuatBankSoal::class)->handle($semua);

    expect($hasil->diperbarui)->toBe(1)
        ->and(DB::table('soals')->where('id', $soal->soal_id)->value('pembahasan'))->toBe('Pembahasan diperbaiki.')
        ->and(DB::table('soal_opsis')->find($opsi->id)->teks)->toBe('Teks B diperbaiki')
        ->and(DB::table('percobaan_soals')->where('id', $soal->ps_id)->value('opsi_id'))->toBe($opsi->id);
});

it('mengaktifkan kembali soal yang pernah dinonaktifkan bila muncul lagi di berkas', function (): void {
    app(MuatBankSoal::class)->handle([barisSoal('tiu', 1, 1)]);
    expect(DB::table('soals')->where('aktif', true)->count())->toBe(1);

    $semua = [];

    foreach (['twk' => 40, 'tiu' => 40, 'tkp' => 50] as $kategori => $jumlah) {
        foreach (range(1, $jumlah) as $n) {
            $semua[] = barisSoal($kategori, $n, $n % 5);
        }
    }

    $hasil = app(MuatBankSoal::class)->handle($semua);

    expect($hasil->diperbarui)->toBe(129)->and(DB::table('soals')->where('aktif', true)->count())->toBe(130);
});

it('menolak seluruh berkas bila ada pertanyaan kembar', function (): void {
    expect(fn () => app(MuatBankSoal::class)->handle([barisSoal('tiu', 1, 0), barisSoal('tiu', 1, 2)]))->toThrow(SoalTidakValid::class);
});

it('menolak soal dengan kategori yang tidak dikenal', function (): void {
    $baris = barisSoal('tiu', 1, 0);
    $baris['kategori'] = 'skb';

    expect(fn () => app(MuatBankSoal::class)->handle([$baris]))->toThrow(SoalTidakValid::class);
});
