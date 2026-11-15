<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

it('menarik formasi yang hilang dari BKN tanpa menghapusnya, dan mengaktifkannya lagi bila muncul kembali', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    majukanWaktu();
    fakeBkn(array_slice(barisBkn(), 0, 4));
    $hasil = sinkron();

    $id = '00000000-0000-4000-8000-000000000005';

    expect($hasil->ringkasan['ditarik'])->toBe(1)
        ->and(DB::table('formasis')->where('external_id', $id)->value('status'))->toBe('ditarik')
        ->and(jumlah('formasis'))->toBe(5);

    majukanWaktu();
    fakeBkn(barisBkn());
    sinkron();

    expect(DB::table('formasis')->where('external_id', $id)->value('status'))->toBe('aktif');
});

it('menunda penarikan bila jumlah baris turun drastis, lalu menarik setelah respons yang sama muncul lagi', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    majukanWaktu();
    fakeBkn(array_slice(barisBkn(), 0, 1));
    $pertama = sinkron();

    expect($pertama->ringkasan['penarikan_ditunda'])->toBeTrue()
        ->and($pertama->ringkasan['ditarik'])->toBe(0)
        ->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);

    majukanWaktu();
    fakeBkn(array_slice(barisBkn(), 0, 1));
    $kedua = sinkron();

    expect($kedua->ringkasan['penarikan_ditunda'])->toBeFalse()
        ->and($kedua->ringkasan['ditarik'])->toBe(4)
        ->and(jumlah('formasis', ['status' => 'aktif']))->toBe(1);
});

it('tidak menarik apa pun saat BKN mengirim daftar kosong sesaat', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    majukanWaktu();
    fakeBkn([]);
    $hasil = sinkron();

    expect($hasil->ringkasan['penarikan_ditunda'])->toBeTrue()
        ->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);
});

it('tidak menarik formasi bila terlalu banyak baris gagal diproses', function (): void {
    fakeBkn(barisBkn());
    sinkron();

    majukanWaktu();
    $baris = array_slice(barisBkn(), 0, 5);
    unset($baris[4]['instansi']);
    fakeBkn($baris);
    $hasil = sinkron();

    expect($hasil->ringkasan)->toMatchArray(['gagal_baris' => 1, 'ditarik' => 0, 'penarikan_ditunda' => true])
        ->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);
});

it('mencatat baris rusak sebagai galat tanpa menggagalkan baris yang valid', function (): void {
    $baris = barisBkn();
    unset($baris[1]['instansi']);
    $baris[2]['jumlah'] = -5;
    fakeBkn($baris);

    $hasil = sinkron();
    $galat = DB::table('import_errors')->orderBy('baris')->get();

    expect($hasil->berhasil)->toBeTrue()
        ->and(DB::table('import_batches')->value('status'))->toBe('sebagian')
        ->and($hasil->ringkasan['gagal_baris'])->toBe(2)
        ->and(jumlah('formasis'))->toBe(3)
        ->and($galat->pluck('baris')->all())->toBe([2, 3])
        ->and($galat->pluck('kolom')->all())->toBe(['instansi', 'jumlah']);
});

it('membatasi jumlah galat baris yang disimpan', function (): void {
    $baris = array_map(fn (int $i) => ['id' => "x-{$i}"], range(1, 250));
    fakeBkn($baris);

    $hasil = sinkron();

    expect($hasil->ringkasan['gagal_baris'])->toBe(250)->and(jumlah('import_errors'))->toBe(100);
});

it('menolak sinkron paralel selama ada batch yang masih berjalan', function (): void {
    DB::table('import_batches')->insert(['tipe' => 'sinkron_bkn', 'status' => 'berjalan', 'mulai_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    fakeBkn(barisBkn());

    $hasil = sinkron();

    expect($hasil->berhasil)->toBeFalse()->and($hasil->pesan)->toContain('sedang berjalan')->and(jumlah('formasis'))->toBe(0);
});

it('menutup otomatis batch yang menggantung lebih dari 30 menit lalu melanjutkan', function (): void {
    DB::table('import_batches')->insert(['tipe' => 'sinkron_bkn', 'status' => 'berjalan', 'mulai_at' => now()->subMinutes(45), 'created_at' => now(), 'updated_at' => now()]);
    fakeBkn(barisBkn());

    $hasil = sinkron();

    expect($hasil->berhasil)->toBeTrue()
        ->and(DB::table('import_batches')->where('id', 1)->value('status'))->toBe('gagal')
        ->and(jumlah('formasis'))->toBe(5);
});

describe('kegagalan sumber data: data lama tetap utuh', function (): void {
    beforeEach(function (): void {
        fakeBkn(barisBkn());
        sinkron();
        majukanWaktu();
    });

    it('menghentikan sinkron saat BKN menolak (403) tanpa mencoba ulang', function (int $status): void {
        fakeBknMentah('{"message":"blocked"}', $status);

        $hasil = sinkron();

        expect($hasil->berhasil)->toBeFalse()
            ->and($hasil->pesan)->toContain("HTTP {$status}")
            ->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);

        Http::assertSentCount(1);
    })->with([401, 403, 429]);

    it('mencoba ulang saat server BKN galat (5xx) lalu mencatat gagal', function (): void {
        fakeBknMentah('galat', 503);

        $hasil = sinkron();

        expect($hasil->berhasil)->toBeFalse()
            ->and($hasil->pesan)->toContain('HTTP 503')
            ->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);

        Http::assertSentCount(3);
    });

    it('menggagalkan sinkron saat koneksi terputus', function (): void {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $hasil = sinkron();

        expect($hasil->berhasil)->toBeFalse()->and($hasil->pesan)->toContain('koneksi gagal')->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);
    });

    it('menolak halaman HTML (tantangan keamanan) yang dikembalikan dengan status 200', function (): void {
        fakeBknMentah('<html><body>Verifikasi keamanan</body></html>', 200, 'text/html');

        $hasil = sinkron();

        expect($hasil->berhasil)->toBeFalse()->and($hasil->pesan)->toContain('bukan JSON')->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);
    });

    it('menolak respons yang menandai error', function (): void {
        fakeBknMentah('{"status":500,"error":true,"message":"gagal","data":[]}');

        $hasil = sinkron();

        expect($hasil->berhasil)->toBeFalse()->and($hasil->pesan)->toContain('tidak dikenal')->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);
    });

    it('menolak respons yang kolom datanya bukan daftar', function (): void {
        fakeBknMentah('{"status":200,"error":false,"message":"ok","data":{"a":1}}');

        $hasil = sinkron();

        expect($hasil->berhasil)->toBeFalse()->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5);
    });

    it('menggagalkan sinkron bila JSON terpotong di tengah jalan dan tidak menarik formasi apa pun', function (): void {
        $utuh = json_encode(['status' => 200, 'error' => false, 'message' => 'success', 'data' => barisBkn()], JSON_THROW_ON_ERROR);
        fakeBknMentah(substr($utuh, 0, (int) (strlen($utuh) * 0.7)));

        $hasil = sinkron();

        expect($hasil->berhasil)->toBeFalse()
            ->and($hasil->pesan)->toContain('terpotong atau rusak')
            ->and(jumlah('formasis', ['status' => 'aktif']))->toBe(5)
            ->and(DB::table('import_batches')->orderByDesc('id')->value('status'))->toBe('gagal');
    });

    it('mencatat galat sumber pada ringkasan batch', function (): void {
        fakeBknMentah('x', 403);
        sinkron();

        $ringkasan = json_decode((string) DB::table('import_batches')->orderByDesc('id')->value('ringkasan'), true);

        expect($ringkasan['galat'])->toContain('HTTP 403');
    });
});
