<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;

beforeEach(function (): void {
    $this->member = UserModel::factory()->create();
    masukSebagai($this, $this->member);
    siapkanFormasi();
    siapkanStatistikFormasi();
});

function pantau(int $formasiId)
{
    return test()->post('/pantauan', ['formasi_id' => $formasiId]);
}

function idPantauan(): array
{
    return DB::table('pantauans')->where('user_id', test()->member->id)->pluck('formasi_id')->map(fn ($id) => (int) $id)->all();
}

it('hanya bisa dibuka setelah login', function (): void {
    $this->post('/keluar');
    auth()->forgetGuards();

    $this->withCookie(config('session.cookie'), '')->get('/pantauan')->assertRedirect('/masuk');
    $this->withCookie(config('session.cookie'), '')->post('/pantauan', ['formasi_id' => 1])->assertRedirect('/masuk');
});

it('menampilkan daftar kosong', function (): void {
    $this->get('/pantauan')->assertInertia(fn (Assert $h) => $h
        ->component('Pantauan/Index')
        ->where('item', [])
        ->where('batas.perbandingan', 4)
        ->where('batas.catatan', 500));
});

it('memantau formasi dan menampilkannya lengkap dengan peluang', function (): void {
    $id = idFormasi('Penghulu Ahli Pertama');

    pantau($id)->assertRedirect()->assertSessionHasNoErrors();

    expect(idPantauan())->toBe([$id]);

    $this->get('/pantauan')->assertInertia(fn (Assert $h) => $h
        ->has('item', 1)
        ->where('item.0.id', $id)
        ->where('item.0.jabatan', 'Penghulu Ahli Pertama')
        ->where('item.0.instansi', 'Kementerian Agama')
        ->where('item.0.catatan', null)
        ->where('item.0.status', 'aktif')
        ->where('item.0.peluang.tingkat', 'ketat')
        ->where('item.0.peluangJabatan', null));
});

it('idempoten: memantau formasi yang sama dua kali tidak menggandakan', function (): void {
    $id = idFormasi('Guru Madya');

    pantau($id);
    pantau($id)->assertSessionHasNoErrors();

    expect(idPantauan())->toBe([$id])->and(jumlah('pantauans'))->toBe(1);
});

it('menolak formasi yang tidak ada dengan dialog dan tanpa menyimpan', function (): void {
    pantau(999999)->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'peringatan' && str_contains($d['pesan'], 'tidak ditemukan'));

    expect(jumlah('pantauans'))->toBe(0);
    $this->post('/pantauan', ['formasi_id' => 'abc'])->assertSessionHasErrors('formasi_id');
    $this->post('/pantauan', [])->assertSessionHasErrors('formasi_id');
});

it('membatasi jumlah pantauan per pengguna', function (): void {
    fakeBkn(array_map(fn (int $i) => barisFormasi("Jabatan Batas {$i}"), range(1, 101)));
    sinkron();
    $ids = DB::table('formasis')->limit(100)->pluck('id')->all();

    foreach ($ids as $id) {
        DB::table('pantauans')->insert(['user_id' => $this->member->id, 'formasi_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
    }

    $lain = DB::table('formasis')->whereNotIn('id', $ids)->value('id');

    pantau((int) $lain)->assertSessionHas('dialog', fn (array $d) => str_contains($d['pesan'], 'penuh'));

    expect(jumlah('pantauans'))->toBe(100);
});

it('menghentikan pantauan tanpa mengganggu pantauan lain atau pengguna lain', function (): void {
    $a = idFormasi('Penghulu Ahli Pertama');
    $b = idFormasi('Guru Madya');
    $lain = UserModel::factory()->create();
    DB::table('pantauans')->insert(['user_id' => $lain->id, 'formasi_id' => $a, 'created_at' => now(), 'updated_at' => now()]);

    pantau($a);
    pantau($b);
    $this->delete("/pantauan/{$a}")->assertRedirect();

    expect(idPantauan())->toBe([$b])->and(DB::table('pantauans')->where('user_id', $lain->id)->count())->toBe(1);

    $this->delete('/pantauan/999999')->assertRedirect()->assertSessionHasNoErrors();
});

it('tidak menampilkan pantauan milik pengguna lain', function (): void {
    $lain = UserModel::factory()->create();
    DB::table('pantauans')->insert(['user_id' => $lain->id, 'formasi_id' => idFormasi('Guru Madya'), 'catatan' => 'rahasia pengguna lain', 'created_at' => now(), 'updated_at' => now()]);

    $this->get('/pantauan')->assertInertia(fn (Assert $h) => $h->where('item', []));
    $this->delete('/pantauan/'.idFormasi('Guru Madya'));

    expect(DB::table('pantauans')->where('user_id', $lain->id)->count())->toBe(1);
});

it('menyimpan catatan pribadi, merapikannya, dan mengosongkannya', function (): void {
    $id = idFormasi('Penghulu Ahli Pertama');
    pantau($id);

    $this->put("/pantauan/{$id}/catatan", ['catatan' => "  Cek syarat   usia\tdulu  "])->assertSessionHasNoErrors();

    expect(DB::table('pantauans')->value('catatan'))->toBe('Cek syarat usia dulu');

    $this->get('/pantauan')->assertInertia(fn (Assert $h) => $h->where('item.0.catatan', 'Cek syarat usia dulu'));

    $this->put("/pantauan/{$id}/catatan", ['catatan' => '   '])->assertSessionHasNoErrors();

    expect(DB::table('pantauans')->value('catatan'))->toBeNull();
});

it('menolak catatan lebih dari 500 karakter', function (): void {
    $id = idFormasi('Guru Madya');
    pantau($id);

    $this->put("/pantauan/{$id}/catatan", ['catatan' => str_repeat('a', 501)])->assertSessionHasErrors('catatan');

    expect(DB::table('pantauans')->value('catatan'))->toBeNull();
    $this->put("/pantauan/{$id}/catatan", ['catatan' => str_repeat('a', 500)])->assertSessionHasNoErrors();
});

it('tidak bisa mengubah catatan formasi yang tidak dipantau', function (): void {
    $this->put('/pantauan/'.idFormasi('Guru Madya').'/catatan', ['catatan' => 'x'])->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');

    expect(jumlah('pantauans'))->toBe(0);
});

it('menandai formasi yang dipantau pada daftar dan detail formasi', function (): void {
    $id = idFormasi('Penghulu Ahli Pertama');
    pantau($id);

    $this->get('/formasi')->assertInertia(fn (Assert $h) => $h->where('dipantau', [$id]));
    $this->get("/formasi/{$id}")->assertInertia(fn (Assert $h) => $h->where('dipantau', true));
    $this->get('/formasi/'.idFormasi('Guru Madya'))->assertInertia(fn (Assert $h) => $h->where('dipantau', false));
});

it('tetap menampilkan formasi yang sudah ditarik dengan statusnya', function (): void {
    $id = idFormasi('Guru Madya');
    pantau($id);
    DB::table('formasis')->where('id', $id)->update(['status' => 'ditarik']);

    $this->get('/pantauan')->assertInertia(fn (Assert $h) => $h->where('item.0.status', 'ditarik'));
});

it('menghapus pantauan bila formasinya dihapus dari database', function (): void {
    $id = idFormasi('Guru Madya');
    pantau($id);

    DB::table('formasi_persyaratans')->where('formasi_id', $id)->delete();
    DB::table('formasis')->where('id', $id)->delete();

    expect(jumlah('pantauans'))->toBe(0);
});

it('mengurutkan dari yang terbaru dipantau', function (): void {
    $a = idFormasi('Penghulu Ahli Pertama');
    $b = idFormasi('Guru Madya');
    DB::table('pantauans')->insert([
        ['user_id' => $this->member->id, 'formasi_id' => $a, 'created_at' => '2026-11-01 10:00:00', 'updated_at' => now()],
        ['user_id' => $this->member->id, 'formasi_id' => $b, 'created_at' => '2026-11-02 10:00:00', 'updated_at' => now()],
    ]);

    $this->get('/pantauan')->assertInertia(fn (Assert $h) => $h->where('item.0.id', $b)->where('item.1.id', $a));
});

it('membandingkan dua sampai empat formasi dalam urutan yang diminta', function (): void {
    $ids = [idFormasi('Penghulu Ahli Pertama'), idFormasi('Guru Madya'), idFormasi('Analis Kebijakan'), idFormasi('Statistisi Ahli Pertama')];

    foreach ($ids as $id) {
        pantau($id);
    }

    $this->get("/pantauan/bandingkan?id[]={$ids[2]}&id[]={$ids[0]}")->assertInertia(fn (Assert $h) => $h
        ->component('Pantauan/Bandingkan')
        ->has('item', 2)
        ->where('item.0.id', $ids[2])
        ->where('item.1.id', $ids[0])
        ->where('ambang.longgarMaks', 5));

    $this->get('/pantauan/bandingkan?'.http_build_query(['id' => $ids]))->assertInertia(fn (Assert $h) => $h->has('item', 4));
});

it('menolak perbandingan dengan jumlah formasi di luar 2 sampai 4', function (): void {
    $ids = [idFormasi('Penghulu Ahli Pertama'), idFormasi('Guru Madya'), idFormasi('Analis Kebijakan'), idFormasi('Statistisi Ahli Pertama'), idFormasi('Pengelola Umum')];

    foreach ($ids as $id) {
        pantau($id);
    }

    $this->get("/pantauan/bandingkan?id[]={$ids[0]}")->assertRedirect('/pantauan')->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'peringatan');
    $this->get('/pantauan/bandingkan?'.http_build_query(['id' => $ids]))->assertRedirect('/pantauan');
    $this->get("/pantauan/bandingkan?id[]={$ids[0]}&id[]={$ids[0]}")->assertRedirect('/pantauan');
    $this->get('/pantauan/bandingkan')->assertRedirect();
});

it('menolak membandingkan formasi yang tidak ada di daftar pantauan sendiri', function (): void {
    $a = idFormasi('Penghulu Ahli Pertama');
    $b = idFormasi('Guru Madya');
    pantau($a);

    $this->get("/pantauan/bandingkan?id[]={$a}&id[]={$b}")->assertRedirect('/pantauan')->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'peringatan');
});

it('menolak id perbandingan yang bukan angka', function (): void {
    $this->get('/pantauan/bandingkan?id[]=abc&id[]=2')->assertRedirect();
});

it('membatasi laju permintaan memantau', function (): void {
    $id = idFormasi('Guru Madya');

    foreach (range(1, 60) as $i) {
        pantau($id)->assertRedirect();
    }

    pantau($id)->assertStatus(429);
});

it('admin juga bisa memantau', function (): void {
    $admin = UserModel::factory()->admin()->create();
    $this->actingAs($admin)->post('/pantauan', ['formasi_id' => idFormasi('Guru Madya')])->assertSessionHasNoErrors();

    expect(DB::table('pantauans')->where('user_id', $admin->id)->count())->toBe(1);
});
