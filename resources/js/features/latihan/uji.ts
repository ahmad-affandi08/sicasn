import type { HasilLatihan, PercobaanBerjalan, SoalDikerjakan, SoalHasil } from './tipe';

export function soalUji(urutan: number, kategori: SoalDikerjakan['kategori'] = 'tiu'): SoalDikerjakan {
    return {
        urutan,
        soalId: urutan * 10,
        kategori,
        subkategori: 'Deret angka',
        pertanyaan: `Pertanyaan nomor ${urutan}`,
        opsi: ['A', 'B', 'C', 'D', 'E'].map((label, indeks) => ({ id: urutan * 100 + indeks, label, teks: `Teks ${label} soal ${urutan}` })),
        opsiDipilihId: null,
        ragu: false,
    };
}

export function percobaanUji(jumlah = 3, sisaDetik = 600): PercobaanBerjalan {
    return {
        id: 7,
        mode: 'tiu',
        modeLabel: 'Latihan TIU',
        sisaDetik,
        durasiDetik: 1800,
        soal: Array.from({ length: jumlah }, (_, indeks) => soalUji(indeks + 1)),
    };
}

export function soalHasilUji(urutan: number, status: SoalHasil['status'], kategori: SoalHasil['kategori'] = 'tiu'): SoalHasil {
    const opsi = ['A', 'B', 'C', 'D', 'E'].map((label, indeks) => ({ id: urutan * 100 + indeks, label, teks: `Teks ${label}`, skor: indeks === 1 ? 5 : 0 }));

    return {
        urutan,
        kategori,
        subkategori: 'Deret angka',
        pertanyaan: `Pertanyaan hasil ${urutan}`,
        pembahasan: `Pembahasan soal ${urutan}`,
        sumberNama: 'Soal asli SICASN',
        sumberUrl: null,
        lisensi: 'Soal asli SICASN',
        opsi,
        opsiDipilihId: status === 'kosong' ? null : status === 'benar' ? opsi[1]?.id ?? null : opsi[0]?.id ?? null,
        ragu: false,
        status,
    };
}

export function hasilUji(lulus: boolean | null = true): HasilLatihan {
    return {
        id: 7,
        mode: lulus === null ? 'tiu' : 'simulasi',
        modeLabel: lulus === null ? 'Latihan TIU' : 'Simulasi SKD lengkap',
        mulai: '2026-11-15T10:00:00+07:00',
        selesai: '2026-11-15T11:40:00+07:00',
        total: 420,
        totalMaks: 550,
        lulus,
        kategori: [
            { kategori: 'twk', label: 'Tes Wawasan Kebangsaan', jumlah: 30, skor: 120, skorMaks: 150, benar: 24, salah: 5, kosong: 1, ambangSetara: 65, tercapai: true },
            { kategori: 'tiu', label: 'Tes Intelegensia Umum', jumlah: 35, skor: 60, skorMaks: 175, benar: 12, salah: 20, kosong: 3, ambangSetara: 80, tercapai: false },
        ],
        soal: [soalHasilUji(1, 'benar'), soalHasilUji(2, 'salah'), soalHasilUji(3, 'kosong')],
    };
}
