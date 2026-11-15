<?php

declare(strict_types=1);

use Src\DataImport\Application\AgregasiLampiran;
use Src\DataImport\Application\MetadataLampiran;
use Src\DataImport\Application\PenguraiCsvStatistik;
use Src\DataImport\Application\PolaBarisLampiran;
use Src\DataImport\Domain\Exception\PolaLampiranTidakValid;
use Src\DataImport\Domain\Exception\TotalLampiranTidakSesuai;

const POLA_UJI = '/^\s*\d+\s+REG\d+\s+PESERTA UJI \d+\s{2,}(?<jabatan>.+?)\s{2,}(?<lokasi>Kab\.\s.+|Kota\s.+)\s+MS\s*$/u';

function lampiranUji(): array
{
    return [
        'NO  NO REG  NAMA  JABATAN  LOKASI  KET',
        '1  REG001  PESERTA UJI 1  Analis Kebijakan Ahli Pertama  Kab. Sleman  MS',
        '2  REG002  PESERTA UJI 2  Analis Kebijakan Ahli Pertama  Kab. Sleman  MS',
        '3  REG003  PESERTA UJI 3  Analis Kebijakan Ahli Pertama  Kota Malang  MS',
        '4  REG004  PESERTA UJI 4  Perawat Terampil  Kab. Sleman  MS',
        '',
        'halaman 2',
    ];
}

function metaUji(?int $total = null): MetadataLampiran
{
    return new MetadataLampiran('Kementerian Uji', 2024, '2024-09-19', 'Pengumuman Uji', 'https://contoh.id/pengumuman', 'akhir', $total);
}

it('menghitung peserta per jabatan dan lokasi serta mengabaikan baris lain', function (): void {
    $hasil = (new AgregasiLampiran)->handle(lampiranUji(), new PolaBarisLampiran(POLA_UJI), metaUji());

    expect($hasil->barisCocok)->toBe(4)->and($hasil->barisTidakCocok)->toBe(3)->and($hasil->jumlahJabatan)->toBe(3);
});

it('menghasilkan CSV impor yang valid dan hanya berisi agregat', function (): void {
    $hasil = (new AgregasiLampiran)->handle(lampiranUji(), new PolaBarisLampiran(POLA_UJI), metaUji(4));
    $urai = PenguraiCsvStatistik::urai($hasil->csv, new DateTimeImmutable('2026-11-16'));

    expect($urai->galat)->toBe([])->and($urai->baris)->toHaveCount(3)
        ->and($urai->baris[0]->namaJabatan)->toBe('Analis Kebijakan Ahli Pertama')
        ->and($urai->baris[0]->lokasi)->toBe('Kab. Sleman')
        ->and($urai->baris[0]->jumlahLolosAdministrasi)->toBe(2)
        ->and($urai->baris[0]->tingkatData->value)->toBe('B');

    expect($hasil->csv)->not->toContain('PESERTA UJI')->not->toContain('REG00');
});

it('menolak hasil bila total tidak sama dengan angka resmi di pengumuman', function (): void {
    expect(fn () => (new AgregasiLampiran)->handle(lampiranUji(), new PolaBarisLampiran(POLA_UJI), metaUji(5)))->toThrow(TotalLampiranTidakSesuai::class);
});

it('menolak pola yang bukan regex atau tanpa grup jabatan', function (): void {
    expect(fn () => new PolaBarisLampiran('/(/'))->toThrow(PolaLampiranTidakValid::class)
        ->and(fn () => new PolaBarisLampiran('/^\d+ (?<lokasi>.+)$/'))->toThrow(PolaLampiranTidakValid::class);
});

it('mengabaikan baris yang jabatannya kosong', function (): void {
    $hasil = (new AgregasiLampiran)->handle(['x  '], new PolaBarisLampiran('/^x\s*(?<jabatan>.*)$/'), metaUji());

    expect($hasil->barisCocok)->toBe(0);
});

it('menyatukan jabatan yang sama walau huruf besar kecil dan spasinya berbeda', function (): void {
    $hasil = (new AgregasiLampiran)->handle(['A  Perawat   Terampil', 'A  PERAWAT TERAMPIL'], new PolaBarisLampiran('/^A\s+(?<jabatan>.+)$/'), metaUji());

    expect($hasil->jumlahJabatan)->toBe(1)->and($hasil->barisCocok)->toBe(2);
});
