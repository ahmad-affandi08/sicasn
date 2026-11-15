import { LuCircleHelp } from 'react-icons/lu';

import { teksLatihan } from '@/lang/latihan';
import { cn } from '@/lib/utils';
import type { PetaJawaban, SoalDikerjakan } from '@/features/latihan/tipe';

interface PropsPetaSoal {
    soal: SoalDikerjakan[];
    jawaban: PetaJawaban;
    indeks: number;
    onPilih: (indeks: number) => void;
}

type StatusPeta = 'dijawab' | 'ragu' | 'kosong';

function statusDari(soal: SoalDikerjakan, jawaban: PetaJawaban): StatusPeta {
    const butir = jawaban[soal.soalId];

    if (butir?.ragu) {
        return 'ragu';
    }

    return butir?.opsiId != null ? 'dijawab' : 'kosong';
}

export function PetaSoal({ soal, jawaban, indeks, onPilih }: PropsPetaSoal) {
    const teks = teksLatihan.kerjakan;

    return (
        <nav aria-label={teks.peta} className="flex flex-col gap-3">
            <h2 className="text-sm font-medium">{teks.peta}</h2>
            <ol className="grid grid-cols-6 gap-1.5 sm:grid-cols-8 lg:grid-cols-5">
                {soal.map((butir, posisi) => {
                    const status = statusDari(butir, jawaban);
                    const sekarang = posisi === indeks;

                    return (
                        <li key={butir.soalId}>
                            <button
                                type="button"
                                onClick={() => onPilih(posisi)}
                                aria-label={teks.petaAria(posisi + 1, teks.status[status])}
                                aria-current={sekarang ? 'step' : undefined}
                                className={cn(
                                    'relative flex h-11 w-full items-center justify-center rounded-md border text-sm font-medium tabular-nums transition-colors md:h-9',
                                    status === 'dijawab' && 'border-primer bg-primer text-white',
                                    status === 'ragu' && 'border-peringatan bg-peringatan-lembut text-peringatan',
                                    status === 'kosong' && 'border-garis text-redup hover:bg-permukaan',
                                    sekarang && 'ring-2 ring-teks ring-offset-1 ring-offset-latar',
                                )}
                            >
                                {posisi + 1}
                                {status === 'ragu' && <LuCircleHelp aria-hidden className="absolute -top-1 -right-1 h-3.5 w-3.5 rounded-full bg-latar" />}
                            </button>
                        </li>
                    );
                })}
            </ol>
            <ul className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-redup">
                <li>■ {teks.petaKeterangan.dijawab}</li>
                <li>? {teks.petaKeterangan.ragu}</li>
                <li>□ {teks.petaKeterangan.kosong}</li>
            </ul>
        </nav>
    );
}
