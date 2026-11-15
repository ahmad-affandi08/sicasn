<?php

declare(strict_types=1);

use Src\Latihan\Domain\Exception\SoalTidakValid;
use Src\Latihan\Domain\KategoriSoal;
use Src\Latihan\Domain\OpsiBaru;
use Src\Latihan\Domain\SoalBaru;

function soalBaru(string $kategori, array $skor, array $ubah = []): SoalBaru
{
    $opsi = $ubah['opsi'] ?? array_map(fn (string $label, int $s) => new OpsiBaru($label, "Opsi {$label}", $s), ['A', 'B', 'C', 'D', 'E'], $skor);

    return new SoalBaru(
        KategoriSoal::from($kategori),
        $ubah['subkategori'] ?? 'uji',
        $ubah['pertanyaan'] ?? 'Berapa 2 + 2?',
        $ubah['pembahasan'] ?? 'Empat.',
        $ubah['sumber'] ?? 'Buatan SICASN',
        $ubah['url'] ?? null,
        $ubah['lisensi'] ?? 'Asli SICASN',
        $opsi,
    );
}

it('menerima soal TWK dan TIU dengan tepat satu jawaban benar', function (): void {
    expect(soalBaru('tiu', [0, 0, 5, 0, 0]))->toBeInstanceOf(SoalBaru::class);
});

it('menolak soal TWK atau TIU tanpa atau dengan lebih dari satu kunci', function (array $skor): void {
    expect(fn () => soalBaru('twk', $skor))->toThrow(SoalTidakValid::class);
})->with([[[0, 0, 0, 0, 0]], [[5, 5, 0, 0, 0]], [[1, 2, 3, 4, 5]], [[0, 0, 0, 0, 4]]]);

it('menerima soal TKP dengan skor 1 sampai 5 masing-masing sekali', function (): void {
    expect(soalBaru('tkp', [3, 5, 1, 4, 2]))->toBeInstanceOf(SoalBaru::class);
});

it('menolak soal TKP dengan skor kembar atau di luar 1 sampai 5', function (array $skor): void {
    expect(fn () => soalBaru('tkp', $skor))->toThrow(SoalTidakValid::class);
})->with([[[5, 5, 3, 2, 1]], [[0, 1, 2, 3, 4]], [[5, 4, 3, 2, 6]]]);

it('menolak opsi yang tidak lima, tidak berurutan, kembar, atau kosong', function (array $label): void {
    $opsi = array_map(fn (string $l) => new OpsiBaru($l, "Teks {$l}", 0), $label);
    $opsi[0] = new OpsiBaru($label[0], 'Teks benar', 5);

    expect(fn () => soalBaru('tiu', [], ['opsi' => $opsi]))->toThrow(SoalTidakValid::class);
})->with([[['A', 'B', 'C', 'D']], [['B', 'A', 'C', 'D', 'E']], [['A', 'B', 'C', 'D', 'F']]]);

it('menolak teks opsi yang kembar meski berbeda spasi di tepi', function (): void {
    $opsi = [new OpsiBaru('A', 'Sama', 5), new OpsiBaru('B', 'Sama ', 0), new OpsiBaru('C', 'C', 0), new OpsiBaru('D', 'D', 0), new OpsiBaru('E', 'E', 0)];

    expect(fn () => soalBaru('tiu', [], ['opsi' => $opsi]))->toThrow(SoalTidakValid::class);
});

it('mengizinkan opsi yang hanya berbeda huruf besar kecil karena soal ejaan menguji hal itu', function (): void {
    $opsi = [new OpsiBaru('A', 'Rapat dimulai.', 5), new OpsiBaru('B', 'rapat dimulai.', 0), new OpsiBaru('C', 'Rapat Dimulai.', 0), new OpsiBaru('D', 'Rapat dimulai', 0), new OpsiBaru('E', 'RAPAT dimulai.', 0)];

    expect(soalBaru('twk', [], ['opsi' => $opsi]))->toBeInstanceOf(SoalBaru::class);
});

it('mewajibkan pertanyaan, pembahasan, sumber, dan lisensi', function (string $kolom): void {
    expect(fn () => soalBaru('tiu', [5, 0, 0, 0, 0], [$kolom => '  ']))->toThrow(SoalTidakValid::class);
})->with(['pertanyaan', 'pembahasan', 'sumber', 'lisensi', 'subkategori']);

it('menolak URL sumber yang bukan http atau https', function (): void {
    expect(fn () => soalBaru('tiu', [5, 0, 0, 0, 0], ['url' => 'javascript:alert(1)']))->toThrow(SoalTidakValid::class);
    expect(soalBaru('tiu', [5, 0, 0, 0, 0], ['url' => 'https://contoh.id/uud']))->toBeInstanceOf(SoalBaru::class);
});

it('membuat kode yang sama untuk pertanyaan yang sama meski spasi dan huruf besar berbeda', function (): void {
    $a = soalBaru('tiu', [5, 0, 0, 0, 0], ['pertanyaan' => 'Berapa  2 + 2?']);
    $b = soalBaru('tiu', [5, 0, 0, 0, 0], ['pertanyaan' => 'berapa 2 + 2? ']);
    $c = soalBaru('twk', [5, 0, 0, 0, 0], ['pertanyaan' => 'Berapa 2 + 2?']);

    expect($a->kode())->toBe($b->kode())->and($a->kode())->not->toBe($c->kode());
});
