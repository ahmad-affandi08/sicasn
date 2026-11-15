import { describe, expect, it } from 'vitest';

import { hitungFilterAktif, parameterFilter } from './hitungFilter';

const kosong = { cari: '', tipe: '', tingkat: '', urut: 'rasio_terendah' };

describe('hitungFilter', () => {
    it('tidak menghitung pencarian teks dan urutan bawaan', () => {
        expect(hitungFilterAktif({ ...kosong, cari: 'kementerian' })).toBe(0);
    });

    it('menghitung tipe, tingkat, dan urutan yang diubah', () => {
        expect(hitungFilterAktif({ cari: '', tipe: 'pusat', tingkat: 'ketat', urut: 'nama' })).toBe(3);
    });

    it('hanya mengirim parameter yang terisi agar URL ringkas dan bisa dibagikan', () => {
        expect(parameterFilter(kosong)).toEqual({});
        expect(parameterFilter({ cari: 'bkn', tipe: 'daerah', tingkat: '', urut: 'nama' }, 3)).toEqual({ cari: 'bkn', tipe: 'daerah', urut: 'nama', halaman: 3 });
    });

    it('tidak mengirim halaman pertama', () => {
        expect(parameterFilter(kosong, 1)).toEqual({});
    });
});
