import { LuClock } from 'react-icons/lu';

import { teksLatihan } from '@/lang/latihan';
import { cn } from '@/lib/utils';

const AMBANG_PERINGATAN_DETIK = 300;
const MENIT_PENGUMUMAN = [10, 5, 1];

function format(detik: number): string {
    const jam = Math.floor(detik / 3600);
    const menit = Math.floor((detik % 3600) / 60);
    const sisa = detik % 60;
    const dua = (angka: number) => String(angka).padStart(2, '0');

    return jam > 0 ? `${jam}:${dua(menit)}:${dua(sisa)}` : `${dua(menit)}:${dua(sisa)}`;
}

export function PenghitungWaktu({ sisaDetik }: { sisaDetik: number }) {
    const menit = Math.ceil(sisaDetik / 60);
    const umumkan = sisaDetik > 0 && sisaDetik % 60 === 0 && MENIT_PENGUMUMAN.includes(sisaDetik / 60);

    return (
        <div className={cn('flex items-center gap-2 font-medium tabular-nums', sisaDetik <= AMBANG_PERINGATAN_DETIK ? 'text-peringatan' : 'text-teks')}>
            <LuClock aria-hidden className="h-4 w-4" />
            <span className="sr-only">{teksLatihan.kerjakan.waktu}: </span>
            <span role="timer" aria-live="off">
                {format(sisaDetik)}
            </span>
            <span className="sr-only" aria-live="polite">
                {umumkan ? teksLatihan.kerjakan.waktuHampirHabis(menit) : ''}
            </span>
        </div>
    );
}
