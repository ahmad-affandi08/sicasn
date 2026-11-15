import { describe, expect, it } from 'vitest';

import { FILTER_KOSONG, hitungFilterAktif, parameterFilter } from './filter';

describe('filter formasi', () => {
    it('tidak menghitung pencarian teks dan urutan bawaan sebagai filter aktif', () => {
        expect(hitungFilterAktif({ ...FILTER_KOSONG, cari: 'perawat' })).toBe(0);
    });

    it('menghitung setiap kelompok filter yang terisi sekali', () => {
        expect(hitungFilterAktif({ ...FILTER_KOSONG, jenjang: ['S1', 'D4'], jurusan: [1, 2], provinsi: 3, wilayah: 4, instansi: 5, jenis: 'UMUM', tingkat: 'ketat', urut: 'terbaru', sertakan: true })).toBe(9);
        expect(hitungFilterAktif({ ...FILTER_KOSONG, jenjang: ['S1', 'D4'] })).toBe(1);
    });

    it('tidak mengirim parameter kosong agar URL ringkas dan bisa dibagikan', () => {
        expect(parameterFilter(FILTER_KOSONG)).toEqual({});
    });

    it('mengirim parameter yang terisi beserta halaman bila lebih dari satu', () => {
        const parameter = parameterFilter({ ...FILTER_KOSONG, cari: 'bkn', jenjang: ['S1'], jurusan: [7], provinsi: 34, urut: 'kuota_terbanyak', sertakan: true }, 3);

        expect(parameter).toEqual({ cari: 'bkn', jenjang: ['S1'], jurusan: [7], provinsi: 34, urut: 'kuota_terbanyak', sertakan: '1', halaman: 3 });
    });

    it('tidak mengirim halaman pertama', () => {
        expect(parameterFilter(FILTER_KOSONG, 1)).toEqual({});
    });
});
