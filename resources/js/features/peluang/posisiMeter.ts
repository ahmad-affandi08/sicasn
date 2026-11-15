import type { TingkatPersainganKode } from '@/types';

const BATAS_ATAS_KALI = 3;

export function posisiMeter(rasio: number, longgarMaks: number, ketatMin: number): number {
    if (rasio <= longgarMaks) {
        return (Math.max(rasio, 0) / longgarMaks) * (100 / 3);
    }

    if (rasio <= ketatMin) {
        return 100 / 3 + ((rasio - longgarMaks) / (ketatMin - longgarMaks)) * (100 / 3);
    }

    const puncak = ketatMin * BATAS_ATAS_KALI;

    return Math.min(200 / 3 + ((rasio - ketatMin) / (puncak - ketatMin)) * (100 / 3), 100);
}

export function warnaTingkat(tingkat: TingkatPersainganKode): string {
    const warna: Record<TingkatPersainganKode, string> = {
        longgar: 'bg-sukses',
        sedang: 'bg-peringatan',
        ketat: 'bg-bahaya',
        belum_final: 'bg-info',
        belum_ada_data: 'bg-redup',
    };

    return warna[tingkat];
}
