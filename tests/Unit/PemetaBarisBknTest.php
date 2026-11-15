<?php

declare(strict_types=1);

use Src\DataImport\Application\PemetaBarisBkn;
use Src\DataImport\Domain\Exception\BarisTidakValid;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\TipeLokasi;

function barisValid(array $timpa = []): array
{
    return array_replace(barisBkn()[0], $timpa);
}

it('memetakan baris CPNS yang valid ke formasi baru', function (): void {
    $formasi = PemetaBarisBkn::petakan(barisValid());

    expect($formasi->jenisPengadaan)->toBe(JenisPengadaan::Cpns)
        ->and($formasi->namaInstansi)->toBe('Kementerian Agama')
        ->and($formasi->kodeJabatan)->toBe('JF-PENGHULU')
        ->and($formasi->lokasi->tipe)->toBe(TipeLokasi::Kabupaten)
        ->and($formasi->lokasi->nama)->toBe('Sleman')
        ->and($formasi->kuota)->toBe(3)
        ->and($formasi->usiaMin)->toBe(18)
        ->and($formasi->isDisabilitas)->toBeFalse()
        ->and($formasi->kualifikasi)->toHaveCount(3)
        ->and($formasi->persyaratan)->toHaveCount(3)
        ->and($formasi->persyaratan[2]['wajib'])->toBeFalse();
});

it('memetakan seluruh baris dari fixture bentuk asli BKN tanpa galat', function (): void {
    $contoh = json_decode((string) file_get_contents(dirname(__DIR__, 2).'/docs/samples/bkn-spf-sample.json'), true)['data'];

    foreach ($contoh as $baris) {
        $formasi = PemetaBarisBkn::petakan($baris);

        expect($formasi->namaInstansi)->toBe('Kementerian Sosial')->and($formasi->kuota)->toBeGreaterThan(0);
    }

    expect(PemetaBarisBkn::petakan($contoh[0])->kualifikasi)->toBe([])
        ->and(PemetaBarisBkn::petakan($contoh[1])->kualifikasi)->not->toBe([]);
});

it('menolak baris yang kehilangan kolom wajib', function (string $kolom): void {
    $baris = barisValid();
    unset($baris[$kolom]);

    PemetaBarisBkn::petakan($baris);
})->with(['id', 'event_id', 'instansi', 'nama_jabatan', 'jenis_pengadaan', 'lokasi_formasi', 'jumlah'])->throws(BarisTidakValid::class);

it('menolak kuota negatif atau bukan angka', function (mixed $jumlah): void {
    PemetaBarisBkn::petakan(barisValid(['jumlah' => $jumlah]));
})->with([-1, 'banyak', null])->throws(BarisTidakValid::class);

it('menolak id yang lebih panjang dari 36 karakter', function (): void {
    PemetaBarisBkn::petakan(barisValid(['id' => str_repeat('a', 37)]));
})->throws(BarisTidakValid::class);

it('melaporkan kolom yang bermasalah pada pengecualian', function (): void {
    try {
        PemetaBarisBkn::petakan(barisValid(['instansi' => '  ']));
    } catch (BarisTidakValid $galat) {
        expect($galat->kolom)->toBe('instansi');

        return;
    }

    throw new RuntimeException('Seharusnya melempar BarisTidakValid.');
});

it('menoleransi kolom opsional yang kosong atau bertipe tak terduga', function (): void {
    $formasi = PemetaBarisBkn::petakan(barisValid([
        'usia_min' => null,
        'usia_max' => 'tidak-ada',
        'penghasilan_min' => '5000000',
        'is_disabilitas' => '1',
        'persyaratan' => 'bukan-daftar',
        'nama_pendidikan' => '  ',
        'periode' => 0,
    ]));

    expect($formasi->usiaMin)->toBeNull()
        ->and($formasi->usiaMax)->toBeNull()
        ->and($formasi->penghasilanMin)->toBe(5000000)
        ->and($formasi->isDisabilitas)->toBeTrue()
        ->and($formasi->persyaratan)->toBe([])
        ->and($formasi->kualifikasi)->toBe([])
        ->and($formasi->periode)->toBe(1);
});

it('menghasilkan hash yang stabil dan berubah hanya bila isi berubah', function (): void {
    $a = PemetaBarisBkn::petakan(barisValid());
    $b = PemetaBarisBkn::petakan(barisValid());
    $kuotaBeda = PemetaBarisBkn::petakan(barisValid(['jumlah' => 99]));
    $syaratBeda = PemetaBarisBkn::petakan(barisValid(['persyaratan' => [['nama' => 'Syarat lain', 'mandatory' => 1]]]));

    expect($a->hash())->toBe($b->hash())
        ->and($a->hash())->not->toBe($kuotaBeda->hash())
        ->and($a->hash())->not->toBe($syaratBeda->hash());
});
