import { teksPeluang } from '@/lang/peluang';
import { formatAngka, formatHari } from '@/lib/format';
import type { InstansiPeluangItem } from '@/types';

export function tampilPelamar(instansi: InstansiPeluangItem): string {
    if (instansi.pelamar !== null) {
        return formatAngka(instansi.pelamar, instansi.terbaru.status === 'sementara');
    }

    return instansi.submit === null ? teksPeluang.kosongData : `${formatAngka(instansi.submit, instansi.terbaru.status === 'sementara')} ${teksPeluang.kartu.submit}`;
}

export function tampilFormasi(instansi: InstansiPeluangItem): string {
    return formatAngka(instansi.formasi);
}

export function catatanFormasi(instansi: InstansiPeluangItem): string | null {
    return instansi.formasiDariTanggal === null ? null : teksPeluang.kartu.formasiDari(formatHari(instansi.formasiDariTanggal));
}
