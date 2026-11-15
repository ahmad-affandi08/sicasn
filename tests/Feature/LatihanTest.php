<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Application\SettingsRepository;

beforeEach(function (): void {
    $this->member = UserModel::factory()->create();
    masukSebagai($this, $this->member);
    muatSoalUji();
});

it('mengalihkan tamu ke halaman masuk', function (): void {
    auth()->logout();

    $this->get('/latihan')->assertRedirect('/masuk');
});

it('menampilkan beranda latihan dengan jumlah soal tersedia, ambang resmi, dan keempat mode', function (): void {
    $this->get('/latihan')->assertOk()->assertInertia(fn (Assert $halaman) => $halaman
        ->component('Latihan/Index')
        ->where('ketersediaan', ['twk' => 40, 'tiu' => 40, 'tkp' => 50])
        ->where('ambang.twk.resmi', 65)
        ->where('ambang.tiu.resmi', 80)
        ->where('ambang.tkp.resmi', 166)
        ->has('mode', 4)
        ->where('percobaanBerjalanId', null)
        ->where('riwayat', []));
});

it('memulai simulasi lengkap dengan 30 TWK, 35 TIU, dan 45 TKP serta batas 100 menit', function (): void {
    $id = mulaiSebagai();

    $perKategori = DB::table('percobaan_soals as ps')->join('soals as s', 's.id', '=', 'ps.soal_id')->where('ps.percobaan_id', $id)->groupBy('s.kategori')->selectRaw('s.kategori as k, count(*) as n')->pluck('n', 'k');
    $baris = DB::table('percobaans')->find($id);

    expect($perKategori->all())->toBe(['tiu' => 35, 'tkp' => 45, 'twk' => 30])
        ->and(strtotime($baris->batas_selesai_at) - strtotime($baris->mulai_at))->toBe(6000);
});

it('mengurutkan soal TWK, lalu TIU, lalu TKP seperti CAT', function (): void {
    $id = mulaiSebagai();

    $urutan = DB::table('percobaan_soals as ps')->join('soals as s', 's.id', '=', 'ps.soal_id')->where('ps.percobaan_id', $id)->orderBy('ps.urutan')->pluck('s.kategori')->all();

    expect(array_slice($urutan, 0, 30))->each->toBe('twk')
        ->and(array_slice($urutan, 30, 35))->each->toBe('tiu')
        ->and(array_slice($urutan, 65))->each->toBe('tkp');
});

it('tidak membocorkan kunci jawaban atau pembahasan saat mengerjakan', function (): void {
    $id = mulaiSebagai();

    $respons = $this->get("/latihan/{$id}")->assertOk();
    $isi = json_encode($respons->viewData('page')['props']['percobaan']);

    expect($isi)->not->toContain('skor')->and($isi)->not->toContain('pembahasan')->and($isi)->not->toContain('Pembahasan');
    $respons->assertInertia(fn (Assert $halaman) => $halaman
        ->component('Latihan/Kerjakan')
        ->has('percobaan.soal', 110)
        ->where('percobaan.mode', 'simulasi')
        ->where('percobaan.soal.0.opsi.0.label', 'A'));
});

it('menyesuaikan jumlah soal dan durasi bila bank lebih kecil dari komposisi resmi', function (): void {
    DB::table('soals')->where('kategori', 'twk')->where('id', '>', DB::table('soals')->where('kategori', 'twk')->min('id') + 9)->update(['aktif' => false]);

    $id = mulaiSebagai();

    $jumlahTwk = DB::table('percobaan_soals as ps')->join('soals as s', 's.id', '=', 'ps.soal_id')->where('ps.percobaan_id', $id)->where('s.kategori', 'twk')->count();
    $total = DB::table('percobaan_soals')->where('percobaan_id', $id)->count();
    $baris = DB::table('percobaans')->find($id);

    expect($jumlahTwk)->toBe(10)
        ->and($total)->toBe(90)
        ->and(strtotime($baris->batas_selesai_at) - strtotime($baris->mulai_at))->toBe((int) ceil(90 * 100 * 60 / 110));
});

it('tidak pernah memilih soal yang dinonaktifkan', function (): void {
    DB::table('soals')->where('kategori', 'tiu')->update(['aktif' => false]);
    DB::table('soals')->where('kategori', 'tiu')->limit(0);

    $this->post('/latihan', ['mode' => 'tiu'])->assertSessionHas('dialog');
    expect(DB::table('percobaans')->count())->toBe(0);
});

it('memberi dialog ramah saat bank soal suatu kategori kosong', function (): void {
    DB::table('soals')->where('kategori', 'tkp')->update(['aktif' => false]);

    $this->post('/latihan', ['mode' => 'simulasi'])->assertRedirect()->assertSessionHas('dialog.judul', 'Soal belum tersedia');
});

it('memulai latihan per kategori dengan 20 soal dan 30 menit', function (): void {
    $id = mulaiSebagai('twk');
    $baris = DB::table('percobaans')->find($id);

    expect(DB::table('percobaan_soals')->where('percobaan_id', $id)->count())->toBe(20)
        ->and(strtotime($baris->batas_selesai_at) - strtotime($baris->mulai_at))->toBe(1800);
});

it('menolak mode yang tidak dikenal', function (): void {
    $this->post('/latihan', ['mode' => 'ngawur'])->assertSessionHasErrors('mode');
});

it('melanjutkan percobaan yang sedang berjalan alih-alih membuat yang baru', function (): void {
    $pertama = mulaiSebagai('twk');
    $kedua = mulaiSebagai('tiu');

    expect($kedua)->toBe($pertama)->and(DB::table('percobaans')->count())->toBe(1);
    $this->get('/latihan')->assertInertia(fn (Assert $halaman) => $halaman->where('percobaanBerjalanId', $pertama));
});

it('menghindari soal dari tiga percobaan terakhir selama masih ada soal lain', function (): void {
    $pertama = mulaiSebagai('tiu');
    $this->post("/latihan/{$pertama}/selesai");
    $kedua = mulaiSebagai('tiu');

    $idPertama = DB::table('percobaan_soals')->where('percobaan_id', $pertama)->pluck('soal_id')->all();
    $idKedua = DB::table('percobaan_soals')->where('percobaan_id', $kedua)->pluck('soal_id')->all();

    expect(array_intersect($idPertama, $idKedua))->toBe([]);
});

it('menyimpan jawaban dan tanda ragu lalu menampilkannya saat halaman dimuat ulang', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->orderBy('urutan')->first();
    $opsi = DB::table('soal_opsis')->where('soal_id', $soal->soal_id)->where('label', 'C')->first();

    jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => $opsi->id, 'ragu' => true])->assertOk()->assertJsonStructure(['sisaDetik']);

    $this->get("/latihan/{$id}")->assertInertia(fn (Assert $halaman) => $halaman
        ->where('percobaan.soal.0.opsiDipilihId', $opsi->id)
        ->where('percobaan.soal.0.ragu', true));
});

it('mengizinkan menghapus jawaban dengan opsi kosong', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->first();
    $opsi = DB::table('soal_opsis')->where('soal_id', $soal->soal_id)->first();

    jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => $opsi->id, 'ragu' => false])->assertOk();
    jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => null, 'ragu' => false])->assertOk();

    expect(DB::table('percobaan_soals')->where('id', $soal->id)->value('opsi_id'))->toBeNull();
});

it('menolak opsi milik soal lain dan soal yang bukan bagian percobaan', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->first();
    $opsiLain = DB::table('soal_opsis')->where('soal_id', '!=', $soal->soal_id)->first();
    $soalLuar = DB::table('soals')->whereNotIn('id', DB::table('percobaan_soals')->where('percobaan_id', $id)->pluck('soal_id'))->first();
    $opsiLuar = DB::table('soal_opsis')->where('soal_id', $soalLuar->id)->first();

    jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => $opsiLain->id, 'ragu' => false])->assertStatus(422);
    jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soalLuar->id, 'opsi_id' => $opsiLuar->id, 'ragu' => false])->assertStatus(422);
});

it('memvalidasi bentuk permintaan jawaban', function (): void {
    $id = mulaiSebagai('tiu');

    jawabJson($this, "/latihan/{$id}/jawaban", ['opsi_id' => 'x'])->assertStatus(422)->assertJsonValidationErrors(['soal_id', 'opsi_id', 'ragu']);
});

it('menolak jawaban setelah waktu habis lewat toleransi lima detik', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->first();
    $opsi = DB::table('soal_opsis')->where('soal_id', $soal->soal_id)->first();
    $kirim = fn () => jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => $opsi->id, 'ragu' => false]);

    $this->travel(1800 + 4)->seconds();
    $kirim()->assertOk();

    $this->travel(2)->seconds();
    $kirim()->assertStatus(409);
});

it('menolak jawaban pada percobaan yang sudah diselesaikan', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->first();
    $opsi = DB::table('soal_opsis')->where('soal_id', $soal->soal_id)->first();

    $this->post("/latihan/{$id}/selesai")->assertRedirect("/latihan/{$id}/hasil");

    jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => $opsi->id, 'ragu' => false])->assertStatus(409);
});

it('menyembunyikan percobaan pengguna lain: 404 untuk membuka, menjawab, menyelesaikan, dan melihat hasil', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->first();
    $opsi = DB::table('soal_opsis')->where('soal_id', $soal->soal_id)->first();
    $this->post("/latihan/{$id}/selesai");

    $lain = UserModel::factory()->create();
    auth()->logout();
    masukSebagai($this, $lain);

    $this->get("/latihan/{$id}")->assertNotFound();
    $this->get("/latihan/{$id}/hasil")->assertNotFound();
    $this->post("/latihan/{$id}/selesai")->assertNotFound();
    jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => $opsi->id, 'ragu' => false])->assertNotFound();
});

it('memakai ambang dari pengaturan bila admin mengubahnya', function (): void {
    app(SettingsRepository::class)->simpanBanyak('latihan', ['passing_twk' => '90', 'passing_tiu' => '100', 'passing_tkp' => '200']);

    $this->get('/latihan')->assertInertia(fn (Assert $halaman) => $halaman->where('ambang.twk.resmi', 90)->where('ambang.tiu.resmi', 100)->where('ambang.tkp.resmi', 200));
});

it('kembali ke ambang awal bila pengaturan rusak', function (): void {
    app(SettingsRepository::class)->simpanBanyak('latihan', ['passing_twk' => 'abc', 'passing_tiu' => '-5', 'passing_tkp' => '']);

    $this->get('/latihan')->assertInertia(fn (Assert $halaman) => $halaman->where('ambang.twk.resmi', 65)->where('ambang.tiu.resmi', 80)->where('ambang.tkp.resmi', 166));
});

it('membatasi laju penyimpanan jawaban dan permulaan latihan', function (): void {
    $id = mulaiSebagai('tiu');
    $this->post("/latihan/{$id}/selesai");

    foreach (range(1, 20) as $i) {
        $this->post('/latihan', ['mode' => 'tiu']);
        $this->post("/latihan/{$id}/selesai");
    }

    $this->post('/latihan', ['mode' => 'tiu'])->assertStatus(429);
});

it('tidak membiarkan banyaknya jawaban yang disimpan menghambat pemulaian latihan berikutnya', function (): void {
    $id = mulaiSebagai('tiu');
    $soal = DB::table('percobaan_soals')->where('percobaan_id', $id)->first();
    $opsi = DB::table('soal_opsis')->where('soal_id', $soal->soal_id)->first();

    foreach (range(1, 40) as $i) {
        jawabJson($this, "/latihan/{$id}/jawaban", ['soal_id' => $soal->soal_id, 'opsi_id' => $opsi->id, 'ragu' => false])->assertOk();
    }

    $this->post("/latihan/{$id}/selesai")->assertRedirect();
    $this->post('/latihan', ['mode' => 'twk'])->assertRedirect()->assertSessionMissing('dialog');
});
