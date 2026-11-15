import { LencanaPersaingan } from '@/features/peluang/LencanaPersaingan';
import { teksFormasi } from '@/lang/formasi';
import { formatRasio } from '@/lib/format';
import type { PeluangFormasiItem } from '@/types';

export function BarisInstansi({ peluang }: { peluang: PeluangFormasiItem | null }) {
    const teks = teksFormasi.peluang;

    return (
        <div className="flex flex-wrap items-center gap-x-2 gap-y-1 border-t border-garis pt-2.5 text-xs text-redup">
            <span>{teks.instansi}</span>
            <LencanaPersaingan tingkat={peluang?.tingkat ?? 'belum_ada_data'} />
            {peluang?.rasio !== null && peluang !== null && <span className="font-medium text-teks">{formatRasio(peluang.rasio, peluang.batasBawah)}</span>}
        </div>
    );
}
