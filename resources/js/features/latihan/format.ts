import { teksLatihan } from '@/lang/latihan';

import type { InfoMode, KategoriSoal } from './tipe';

const URUTAN: KategoriSoal[] = ['twk', 'tiu', 'tkp'];

export function ringkasKomposisi(info: InfoMode): string {
    return URUTAN.filter((kategori) => info.komposisi[kategori] !== undefined)
        .map((kategori) => `${info.komposisi[kategori]} ${teksLatihan.kategori[kategori]}`)
        .join(' · ');
}

export function kategoriDibutuhkan(info: InfoMode): KategoriSoal[] {
    return URUTAN.filter((kategori) => info.komposisi[kategori] !== undefined);
}

export function durasiMenit(info: InfoMode): number {
    const total = Object.values(info.komposisi).reduce((jumlah, nilai) => jumlah + (nilai ?? 0), 0);

    return info.nilai === 'simulasi' ? 100 : Math.round((total * 90) / 60);
}
