<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Pengaturan\Domain\PenyediaEmail;
use Tests\Support\PembangunMailerPalsu;

beforeEach(function (): void {
    $this->admin = UserModel::factory()->admin()->create();
});

function isianEmail(array $timpa = []): array
{
    return array_replace_recursive([
        'aktif' => 'hostinger',
        'cadangan' => null,
        'pengirim_nama' => 'SICASN',
        'pengirim_alamat' => 'noreply@contoh.id',
        'alamat_balas' => null,
        'konfigurasi' => [
            'hostinger' => ['host' => 'smtp.hostinger.com', 'port' => 465, 'enkripsi' => 'ssl', 'username' => 'noreply@contoh.id', 'password' => 'rahasia-smtp-123'],
        ],
    ], $timpa);
}

it('melarang member membuka semua halaman pengaturan', function (string $jalur): void {
    $member = UserModel::factory()->create();
    masukSebagai($this, $member);

    $this->get($jalur)->assertForbidden();
})->with(['/admin/pengaturan/email', '/admin/pengaturan/whatsapp', '/admin/pengaturan/pembayaran', '/admin/pengaturan/situs']);

it('menampilkan katalog penyedia email termasuk Hostinger dengan preset', function (): void {
    $this->actingAs($this->admin)->get('/admin/pengaturan/email')->assertInertia(fn (Assert $h) => $h
        ->component('Admin/Pengaturan/Email')
        ->has('penyedia', count(PenyediaEmail::cases()))
        ->where('penyedia.0.kode', 'hostinger')
        ->where('penyedia.0.preset.host', 'smtp.hostinger.com')
        ->where('penyedia.0.preset.port', 465)
        ->where('pengaturan.siap', false));
});

it('menyimpan pengaturan Hostinger dengan kata sandi terenkripsi di database', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail())
        ->assertSessionHasNoErrors()
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');

    $mentah = DB::table('pengaturans')->where('grup', 'email')->where('kunci', 'smtp.hostinger')->first();

    expect($mentah->terenkripsi)->toBe(1)
        ->and($mentah->nilai)->not->toContain('rahasia-smtp-123')
        ->and($mentah->nilai)->not->toContain('smtp.hostinger.com')
        ->and(DB::table('pengaturans')->get()->toJson())->not->toContain('rahasia-smtp-123');

    $this->actingAs($this->admin)->get('/admin/pengaturan/email')->assertInertia(fn (Assert $h) => $h
        ->where('pengaturan.aktif', 'hostinger')
        ->where('pengaturan.siap', true)
        ->where('pengaturan.konfigurasi.hostinger.username', 'noreply@contoh.id')
        ->where('pengaturan.konfigurasi.hostinger.passwordTersimpan', true)
        ->missing('pengaturan.konfigurasi.hostinger.password'));
});

it('tidak pernah mengirim kata sandi SMTP kembali ke browser', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail());

    $isi = $this->actingAs($this->admin)->get('/admin/pengaturan/email')->getContent();

    expect($isi)->not->toContain('rahasia-smtp-123');
});

it('mempertahankan kata sandi lama bila kolom dikosongkan saat menyimpan ulang', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail());
    $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail(['konfigurasi' => ['hostinger' => ['password' => '', 'host' => 'smtp2.hostinger.com']]]))
        ->assertSessionHasNoErrors();

    pasangMailerPalsu();
    $this->actingAs($this->admin)->post('/admin/pengaturan/email/uji', ['tujuan' => 'tes@contoh.id'])
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses');
});

it('menolak penyimpanan tanpa kata sandi saat penyedia belum pernah dikonfigurasi', function (): void {
    $respons = $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail(['konfigurasi' => ['hostinger' => ['password' => '']]]));

    $respons->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal');
    expect(DB::table('pengaturans')->where('grup', 'email')->exists())->toBeFalse();
});

it('memvalidasi isian pengaturan email', function (array $timpa, string $kolom): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail($timpa))->assertSessionHasErrors($kolom);
})->with([
    'penyedia tak dikenal' => [['aktif' => 'tidak-ada'], 'aktif'],
    'cadangan sama dengan aktif' => [['cadangan' => 'hostinger'], 'cadangan'],
    'port di luar rentang' => [['konfigurasi' => ['hostinger' => ['port' => 70000]]], 'konfigurasi.hostinger.port'],
    'alamat pengirim salah' => [['pengirim_alamat' => 'salah'], 'pengirim_alamat'],
    'enkripsi tak dikenal' => [['konfigurasi' => ['hostinger' => ['enkripsi' => 'aneh']]], 'konfigurasi.hostinger.enkripsi'],
]);

it('mengirim email uji lewat penyedia aktif dan melaporkan hasilnya di dialog', function (): void {
    $palsu = pasangMailerPalsu();
    konfigurasiEmailTersimpan();

    $this->actingAs($this->admin)->post('/admin/pengaturan/email/uji', ['tujuan' => 'tes@contoh.id'])
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses' && str_contains($d['pesan'], 'hostinger'));

    $email = PembangunMailerPalsu::pesanTerkirim()[0]->getOriginalMessage();

    expect($palsu->dibangunUntuk)->toBe([PenyediaEmail::Hostinger])
        ->and($email->getTo()[0]->getAddress())->toBe('tes@contoh.id')
        ->and($email->getSubject())->toBe('Email uji SICASN');
});

it('beralih ke penyedia cadangan secara otomatis bila penyedia aktif gagal', function (): void {
    $palsu = pasangMailerPalsu([PenyediaEmail::Hostinger]);
    konfigurasiEmailTersimpan('hostinger', 'gmail');

    $this->actingAs($this->admin)->post('/admin/pengaturan/email/uji', ['tujuan' => 'tes@contoh.id'])
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'sukses' && str_contains($d['pesan'], 'gmail'));

    expect($palsu->dibangunUntuk)->toBe([PenyediaEmail::Hostinger, PenyediaEmail::Gmail])
        ->and(PembangunMailerPalsu::pesanTerkirim())->toHaveCount(1);
});

it('melaporkan galat tiap penyedia bila semuanya gagal', function (): void {
    pasangMailerPalsu([PenyediaEmail::Hostinger, PenyediaEmail::Gmail]);
    konfigurasiEmailTersimpan('hostinger', 'gmail');

    $this->actingAs($this->admin)->post('/admin/pengaturan/email/uji', ['tujuan' => 'tes@contoh.id'])
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal'
            && str_contains($d['pesan'], 'Hostinger:')
            && str_contains($d['pesan'], 'Gmail / Google Workspace:'));
});

it('menolak email uji bila penyedia belum dikonfigurasi', function (): void {
    $this->actingAs($this->admin)->post('/admin/pengaturan/email/uji', ['tujuan' => 'tes@contoh.id'])
        ->assertSessionHas('dialog', fn (array $d) => $d['jenis'] === 'gagal' && str_contains($d['pesan'], 'belum dikonfigurasi'));
});

it('menyimpan nomor WhatsApp dalam format 62 dan membagikan tautannya ke seluruh situs', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/whatsapp', [
        'nomor' => '0812-3456-7890',
        'nama_tampilan' => 'Kak Rina',
        'template_pesan' => 'Halo {nama_admin}, saya mau daftar.',
    ])->assertSessionHasNoErrors();

    auth()->logout();

    $this->get('/')->assertInertia(fn (Assert $h) => $h->where('whatsappUrl', 'https://wa.me/6281234567890?text=Halo%20Kak%20Rina%2C%20saya%20mau%20daftar.'));
});

it('menolak nomor WhatsApp yang tidak valid', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/whatsapp', ['nomor' => '123'])->assertSessionHasErrors('nomor');
    $this->actingAs($this->admin)->put('/admin/pengaturan/whatsapp', ['nomor' => 'abc'])->assertSessionHasErrors('nomor');
});

it('menyembunyikan tombol WhatsApp bila nomor dikosongkan', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/whatsapp', ['nomor' => '081234567890']);
    $this->actingAs($this->admin)->put('/admin/pengaturan/whatsapp', ['nomor' => '']);

    $this->actingAs($this->admin)->get('/')->assertInertia(fn (Assert $h) => $h->where('whatsappUrl', null));
});

it('menampilkan harga dan catatan paket di beranda, rekening hanya bila diizinkan', function (): void {
    $isian = [
        'rekening' => [['bank' => 'BCA', 'nomor' => '1234567890', 'atas_nama' => 'PT Contoh'], ['bank' => '', 'nomor' => '', 'atas_nama' => '']],
        'harga_teks' => 'Rp 99.000 / 3 bulan',
        'catatan' => 'Akun aktif setelah bukti transfer dikirim.',
        'tampilkan_rekening_publik' => false,
    ];

    $this->actingAs($this->admin)->put('/admin/pengaturan/pembayaran', $isian)->assertSessionHasNoErrors();
    auth()->logout();

    $this->get('/')->assertInertia(fn (Assert $h) => $h
        ->where('pembayaran.hargaTeks', 'Rp 99.000 / 3 bulan')
        ->where('pembayaran.rekening', []));

    $this->actingAs($this->admin)->put('/admin/pengaturan/pembayaran', [...$isian, 'tampilkan_rekening_publik' => true]);
    auth()->logout();

    $this->get('/')->assertInertia(fn (Assert $h) => $h
        ->has('pembayaran.rekening', 1)
        ->where('pembayaran.rekening.0.bank', 'BCA'));
});

it('tidak menampilkan info pembayaran di beranda bila belum diisi', function (): void {
    $this->get('/')->assertInertia(fn (Assert $h) => $h->where('pembayaran', null));
});

it('menyimpan pengaturan satu sesi per akun', function (): void {
    $this->actingAs($this->admin)->get('/admin/pengaturan/situs')->assertInertia(fn (Assert $h) => $h->where('pengaturan.satuSesi', true));

    $this->actingAs($this->admin)->put('/admin/pengaturan/situs', ['satu_sesi' => false])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->get('/admin/pengaturan/situs')->assertInertia(fn (Assert $h) => $h->where('pengaturan.satuSesi', false));
});

it('mencatat perubahan pengaturan di audit log tanpa menyimpan rahasia', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail());

    $audit = DB::table('audit_logs')->where('aksi', 'pengaturan.email_disimpan')->first();

    expect($audit)->not->toBeNull()->and($audit->rincian)->not->toContain('rahasia-smtp-123');
});

it('menandai kesiapan di dasbor setelah semua pengaturan diisi', function (): void {
    $this->actingAs($this->admin)->put('/admin/pengaturan/email', isianEmail());
    $this->actingAs($this->admin)->put('/admin/pengaturan/whatsapp', ['nomor' => '081234567890']);
    $this->actingAs($this->admin)->put('/admin/pengaturan/pembayaran', ['rekening' => [['bank' => 'BCA', 'nomor' => '123', 'atas_nama' => 'X']], 'harga_teks' => 'Rp 1']);

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $h) => $h
        ->where('kesiapan', ['email' => true, 'whatsapp' => true, 'pembayaran' => true]));
});
