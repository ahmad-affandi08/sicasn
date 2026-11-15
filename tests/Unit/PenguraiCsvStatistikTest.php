<?php

declare(strict_types=1);

use Src\DataImport\Application\HasilUraiCsv;
use Src\DataImport\Application\PenguraiCsvStatistik;
use Src\DataImport\Application\TemplateCsvStatistik;
use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;

const HEADER_CSV = 'tahun,jenis_pengadaan,cakupan,instansi,jumlah_formasi,jumlah_pelamar,jumlah_submit,jumlah_lolos_administrasi,tanggal_snapshot,status_angka,tingkat_data,keyakinan,sumber_nama,sumber_url,catatan';

function urai(string $isi): HasilUraiCsv
{
    return PenguraiCsvStatistik::urai($isi, new DateTimeImmutable('2026-11-15'));
}

function kolomGalat(string $isi): array
{
    return array_map(fn ($g) => $g->kolom, urai($isi)->galat);
}

it('mengurai baris valid dan mengisi nilai bawaan yang konservatif', function (): void {
    $hasil = urai("tahun,cakupan,instansi,jumlah_pelamar,jumlah_formasi,tanggal_snapshot,sumber_nama\n2024,instansi,  Kementerian   Agama ,411194,20772,2024-09-17,Media Uji\n");

    expect($hasil->galat)->toBe([])->and($hasil->baris)->toHaveCount(1);

    $baris = $hasil->baris[0];

    expect($baris->namaInstansi)->toBe('Kementerian Agama')
        ->and($baris->jumlahPelamar)->toBe(411194)
        ->and($baris->jumlahFormasi)->toBe(20772)
        ->and($baris->statusAngka)->toBe(StatusAngka::Sementara)
        ->and($baris->tingkatData)->toBe(TingkatData::A)
        ->and($baris->keyakinan)->toBe(Keyakinan::Turunan)
        ->and($baris->cakupan)->toBe(Cakupan::Instansi);
});

it('menangani BOM, pemisah titik koma, dan angka bertitik ribuan', function (): void {
    $isi = "\xEF\xBB\xBFtahun;cakupan;instansi;jumlah_pelamar;tanggal_snapshot;sumber_nama\n2024;instansi;Kementerian Agama;411.194;2024-09-17;Media Uji\n";

    $hasil = urai($isi);

    expect($hasil->galat)->toBe([])->and($hasil->baris[0]->jumlahPelamar)->toBe(411194);
});

it('menangani pemisah tab', function (): void {
    $hasil = urai("tahun\tcakupan\tinstansi\tjumlah_pelamar\ttanggal_snapshot\tsumber_nama\n2024\tinstansi\tKementerian Agama\t100\t2024-09-17\tMedia Uji\n");

    expect($hasil->galat)->toBe([])->and($hasil->baris)->toHaveCount(1);
});

it('mengubah encoding Windows-1252 menjadi UTF-8', function (): void {
    $isi = mb_convert_encoding("tahun,cakupan,instansi,jumlah_pelamar,tanggal_snapshot,sumber_nama\n2024,instansi,Kafé Negara,100,2024-09-17,Media Uji\n", 'Windows-1252', 'UTF-8');

    expect(urai($isi)->baris[0]->namaInstansi)->toBe('Kafé Negara');
});

it('menolak berkas tanpa kolom wajib', function (): void {
    $hasil = urai("tahun,instansi\n2024,Kementerian Agama\n");

    expect($hasil->baris)->toBe([])->and($hasil->galatBerkas)->toContain('cakupan')->toContain('tanggal_snapshot')->toContain('sumber_nama');
});

it('menolak berkas kosong atau hanya berisi header', function (): void {
    expect(urai('')->galatBerkas)->toBe('Berkas kosong.')
        ->and(urai(HEADER_CSV."\n")->galatBerkas)->toBe('Berkas tidak memuat baris data.');
});

it('menolak berkas yang melebihi batas baris', function (): void {
    $isi = "tahun,cakupan,instansi,jumlah_pelamar,tanggal_snapshot,sumber_nama\n".str_repeat("2024,instansi,A,1,2024-09-17,S\n", PenguraiCsvStatistik::MAKS_BARIS + 1);

    expect(urai($isi)->galatBerkas)->toContain('melebihi');
});

it('melaporkan galat per baris dengan nomor baris sesuai berkas', function (): void {
    $isi = "tahun,cakupan,instansi,jumlah_pelamar,tanggal_snapshot,sumber_nama\n2024,instansi,Valid,10,2024-09-17,S\n2024,instansi,,10,2024-09-17,S\n";

    $hasil = urai($isi);

    expect($hasil->baris)->toHaveCount(1)
        ->and($hasil->galat)->toHaveCount(1)
        ->and($hasil->galat[0]->baris)->toBe(3)
        ->and($hasil->galat[0]->kolom)->toBe('instansi');
});

it('menolak nilai yang tidak valid', function (string $baris, string $kolom): void {
    expect(kolomGalat("tahun,cakupan,instansi,jumlah_pelamar,jumlah_submit,jumlah_lolos_administrasi,tanggal_snapshot,status_angka,tingkat_data,keyakinan,sumber_nama,sumber_url\n{$baris}\n"))->toContain($kolom);
})->with([
    'pelamar negatif' => ['2024,instansi,A,-5,,,2024-09-17,,,,S,', 'jumlah_pelamar'],
    'pelamar pecahan' => ['2024,instansi,A,"1,5",,,2024-09-17,,,,S,', 'jumlah_pelamar'],
    'pelamar huruf' => ['2024,instansi,A,seratus,,,2024-09-17,,,,S,', 'jumlah_pelamar'],
    'submit lebih besar dari pelamar' => ['2024,instansi,A,10,20,,2024-09-17,,,,S,', 'jumlah_submit'],
    'lolos lebih besar dari pelamar' => ['2024,instansi,A,10,,20,2024-09-17,,,,S,', 'jumlah_lolos_administrasi'],
    'tanggal bukan format' => ['2024,instansi,A,10,,,17/09/2024,,,,S,', 'tanggal_snapshot'],
    'tanggal tidak ada di kalender' => ['2024,instansi,A,10,,,2024-02-31,,,,S,', 'tanggal_snapshot'],
    'tanggal masa depan' => ['2024,instansi,A,10,,,2027-01-01,,,,S,', 'tanggal_snapshot'],
    'status tidak dikenal' => ['2024,instansi,A,10,,,2024-09-17,mungkin,,,S,', 'status_angka'],
    'tingkat tidak dikenal' => ['2024,instansi,A,10,,,2024-09-17,,Z,,S,', 'tingkat_data'],
    'keyakinan tidak dikenal' => ['2024,instansi,A,10,,,2024-09-17,,,pasti,S,', 'keyakinan'],
    'tautan bukan http' => ['2024,instansi,A,10,,,2024-09-17,,,,S,javascript:alert(1)', 'sumber_url'],
    'tahun di luar rentang' => ['1900,instansi,A,10,,,2024-09-17,,,,S,', 'tahun'],
    'sumber kosong' => ['2024,instansi,A,10,,,2024-09-17,,,,,', 'sumber_nama'],
    'tanpa satu pun angka' => ['2024,instansi,A,,,,2024-09-17,,,,S,', 'jumlah_pelamar'],
]);

it('menolak cakupan jabatan dan aturan instansi untuk cakupan nasional', function (): void {
    $kolom = kolomGalat("tahun,cakupan,instansi,jumlah_pelamar,tanggal_snapshot,sumber_nama\n2024,jabatan,A,10,2024-09-17,S\n2024,nasional,Kementerian,10,2024-09-17,S\n2024,instansi,,10,2024-09-17,S\n");

    expect($kolom)->toBe(['cakupan', 'instansi', 'instansi']);
});

it('menolak baris ganda pada kombinasi kunci yang sama tanpa membedakan huruf besar kecil', function (): void {
    $hasil = urai("tahun,cakupan,instansi,jumlah_pelamar,tanggal_snapshot,sumber_nama\n2024,instansi,Kementerian Agama,10,2024-09-17,S\n2024,instansi,KEMENTERIAN AGAMA,12,2024-09-17,s\n");

    expect($hasil->baris)->toHaveCount(1)->and($hasil->galat[0]->kolom)->toBe('baris_ganda');
});

it('membuat template CSV yang kolomnya sama dengan yang dibaca parser', function (): void {
    $baris = explode("\n", TemplateCsvStatistik::isi());

    expect($baris[0])->toBe(implode(',', PenguraiCsvStatistik::KOLOM))
        ->and(urai(TemplateCsvStatistik::isi())->galat)->toBe([]);
});

it('melindungi sel CSV dari injeksi rumus dan mengutip karakter khusus', function (): void {
    expect(TemplateCsvStatistik::keCsv(['=SUM(A1)', '+1', '-1', '@cmd', 'a,b', 'ia berkata "ya"']))
        ->toBe("'=SUM(A1),'+1,'-1,'@cmd,\"a,b\",\"ia berkata \"\"ya\"\"\"");
});
