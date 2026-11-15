import type { FilterPeluangItem } from '@/types';

export const URUTAN_AWAL = 'rasio_terendah';

export function hitungFilterAktif(filter: FilterPeluangItem): number {
    return [filter.tipe !== '', filter.tingkat !== '', filter.urut !== URUTAN_AWAL].filter(Boolean).length;
}

export function parameterFilter(filter: FilterPeluangItem, halaman?: number): Record<string, string | number> {
    const parameter: Record<string, string | number> = {};

    if (filter.cari !== '') {
        parameter.cari = filter.cari;
    }

    if (filter.tipe !== '') {
        parameter.tipe = filter.tipe;
    }

    if (filter.tingkat !== '') {
        parameter.tingkat = filter.tingkat;
    }

    if (filter.urut !== URUTAN_AWAL) {
        parameter.urut = filter.urut;
    }

    if (halaman !== undefined && halaman > 1) {
        parameter.halaman = halaman;
    }

    return parameter;
}
