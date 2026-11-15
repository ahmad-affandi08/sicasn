<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;

beforeEach(function (): void {
    $this->member = UserModel::factory()->create();
    masukSebagai($this, $this->member);
    muatSoalUji();
});

it('menghitung skor: TWK dan TIU lima per jawaban benar, TKP sesuai skor opsi, tanpa pengurangan', function (): void {
    $id = mulaiSebagai();
    jawabSemua($id, 'terbaik');

    $this->post("/latihan/{$id}/selesai")->assertRedirect("/latihan/{$id}/hasil");

    $baris = DB::table('percobaans')->find($id);

    expect([$baris->skor_twk, $baris->skor_tiu, $baris->skor_tkp])->toBe([150, 175, 225])
        ->and([$baris->jumlah_twk, $baris->jumlah_tiu, $baris->jumlah_tkp])->toBe([30, 35, 45]);
});

it('memberi nol untuk jawaban salah dan kosong pada TWK dan TIU, dan skor terendah tetap dihitung pada TKP', function (): void {
    $id = mulaiSebagai();
    jawabSemua($id, 'terburuk');

    $this->post("/latihan/{$id}/selesai");

    $baris = DB::table('percobaans')->find($id);

    expect([$baris->skor_twk, $baris->skor_tiu, $baris->skor_tkp])->toBe([0, 0, 45]);
});

it('menyatakan lulus pada simulasi bila semua kategori mencapai ambang resmi', function (): void {
    $id = mulaiSebagai();
    jawabSemua($id, 'terbaik');
    $this->post("/latihan/{$id}/selesai");

    $this->get("/latihan/{$id}/hasil")->assertOk()->assertInertia(fn (Assert $halaman) => $halaman
        ->component('Latihan/Hasil')
        ->where('hasil.lulus', true)
        ->where('hasil.total', 550)
        ->where('hasil.totalMaks', 550)
        ->where('hasil.kategori.0.ambangSetara', 65)
        ->where('hasil.kategori.0.tercapai', true)
        ->where('hasil.kategori.2.ambangSetara', 166));
});

it('menyatakan belum lulus bila satu kategori di bawah ambang', function (): void {
    $id = mulaiSebagai();
    jawabSemua($id, 'terbaik');
    $soalTiu = DB::table('percobaan_soals as ps')->join('soals as s', 's.id', '=', 'ps.soal_id')->where('ps.percobaan_id', $id)->where('s.kategori', 'tiu')->pluck('ps.id');
    DB::table('percobaan_soals')->whereIn('id', $soalTiu)->update(['opsi_id' => null]);
    $this->post("/latihan/{$id}/selesai");

    $this->get("/latihan/{$id}/hasil")->assertInertia(fn (Assert $halaman) => $halaman
        ->where('hasil.lulus', false)
        ->where('hasil.kategori.1.skor', 0)
        ->where('hasil.kategori.1.kosong', 35)
        ->where('hasil.kategori.1.tercapai', false)
        ->where('hasil.kategori.0.tercapai', true));
});

it('tidak menyatakan lulus atau tidak lulus pada latihan per kategori', function (): void {
    $id = mulaiSebagai('tiu');
    jawabSemua($id, 'terbaik');
    $this->post("/latihan/{$id}/selesai");

    $this->get("/latihan/{$id}/hasil")->assertInertia(fn (Assert $halaman) => $halaman
        ->where('hasil.lulus', null)
        ->has('hasil.kategori', 1)
        ->where('hasil.kategori.0.kategori', 'tiu')
        ->where('hasil.kategori.0.skor', 100)
        ->where('hasil.kategori.0.ambangSetara', 46));
});

it('menampilkan pembahasan, sumber, dan kunci pada hasil, dengan status benar, salah, dan kosong', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->orderBy('urutan')->get();
    $opsi = fn (int $soalId, string $urut) => DB::table('soal_opsis')->where('soal_id', $soalId)->orderBy('skor', $urut)->first()->id;

    DB::table('percobaan_soals')->where('id', $soal[0]->id)->update(['opsi_id' => $opsi($soal[0]->soal_id, 'desc')]);
    DB::table('percobaan_soals')->where('id', $soal[1]->id)->update(['opsi_id' => $opsi($soal[1]->soal_id, 'asc')]);
    $this->post("/latihan/{$id}/selesai");

    $this->get("/latihan/{$id}/hasil")->assertInertia(fn (Assert $halaman) => $halaman
        ->where('hasil.soal.0.status', 'benar')
        ->where('hasil.soal.1.status', 'salah')
        ->where('hasil.soal.2.status', 'kosong')
        ->where('hasil.kategori.0.benar', 1)
        ->where('hasil.kategori.0.salah', 1)
        ->where('hasil.kategori.0.kosong', 18)
        ->has('hasil.soal.0.pembahasan')
        ->where('hasil.soal.0.sumberNama', 'Buatan SICASN untuk uji')
        ->where('hasil.soal.0.lisensi', 'Asli SICASN')
        ->has('hasil.soal.0.opsi.0.skor'));
});

it('mengalihkan hasil yang belum selesai kembali ke halaman mengerjakan', function (): void {
    $id = mulaiSebagai('tiu');

    $this->get("/latihan/{$id}/hasil")->assertRedirect("/latihan/{$id}");
});

it('mengalihkan halaman mengerjakan yang sudah selesai ke hasil', function (): void {
    $id = mulaiSebagai('tiu');
    $this->post("/latihan/{$id}/selesai");

    $this->get("/latihan/{$id}")->assertRedirect("/latihan/{$id}/hasil");
});

it('menyelesaikan otomatis saat waktu habis dan menyimpan skor dari jawaban yang sudah masuk', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->orderBy('urutan')->limit(4)->get();

    foreach ($soal as $s) {
        $benar = DB::table('soal_opsis')->where('soal_id', $s->soal_id)->orderByDesc('skor')->first();
        jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $s->soal_id, 'opsi_id' => $benar->id, 'ragu' => false])->assertOk();
    }

    $this->travel(1801)->seconds();

    $this->get("/latihan/{$id}")->assertRedirect("/latihan/{$id}/hasil");

    expect(DB::table('percobaans')->find($id)->skor_tiu)->toBe(20)
        ->and(DB::table('percobaans')->find($id)->selesai_at)->not->toBeNull();
});

it('menutup percobaan kedaluwarsa saat memulai latihan baru lalu membuat yang baru', function (): void {
    $lama = mulaiSebagai('tiu');
    $this->travel(1801)->seconds();

    $baru = mulaiSebagai('twk');

    expect($baru)->not->toBe($lama)->and(DB::table('percobaans')->find($lama)->selesai_at)->not->toBeNull();
});

it('menyelesaikan percobaan dua kali tidak mengubah hasil pertama', function (): void {
    $id = mulaiSebagai('tiu');
    jawabSemua($id, 'terbaik');
    $this->post("/latihan/{$id}/selesai");
    $pertama = DB::table('percobaans')->find($id);

    $this->travel(60)->seconds();
    DB::table('percobaan_soals')->where('percobaan_id', $id)->update(['opsi_id' => null]);
    $this->post("/latihan/{$id}/selesai");

    expect(DB::table('percobaans')->find($id)->selesai_at)->toBe($pertama->selesai_at)
        ->and(DB::table('percobaans')->find($id)->skor_tiu)->toBe($pertama->skor_tiu);
});

it('mencantumkan percobaan selesai pada riwayat beranda, terbaru lebih dulu', function (): void {
    $pertama = mulaiSebagai('twk');
    $this->post("/latihan/{$pertama}/selesai");
    $this->travel(5)->minutes();
    $kedua = mulaiSebagai('tiu');
    jawabSemua($kedua, 'terbaik');
    $this->post("/latihan/{$kedua}/selesai");

    $this->get('/latihan')->assertInertia(fn (Assert $halaman) => $halaman
        ->has('riwayat', 2)
        ->where('riwayat.0.id', $kedua)
        ->where('riwayat.0.modeLabel', 'Latihan TIU')
        ->where('riwayat.0.skor.tiu', 100)
        ->where('riwayat.0.lulus', null)
        ->where('riwayat.1.id', $pertama)
        ->where('percobaanBerjalanId', null));
});

it('hanya menampilkan riwayat milik pengguna sendiri', function (): void {
    $id = mulaiSebagai('tiu');
    $this->post("/latihan/{$id}/selesai");

    $lain = UserModel::factory()->create();
    auth()->logout();
    masukSebagai($this, $lain);

    $this->get('/latihan')->assertInertia(fn (Assert $halaman) => $halaman->where('riwayat', []));
});
