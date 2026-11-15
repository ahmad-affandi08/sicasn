import { useState } from 'react';

import { ButirPembahasan } from '@/components/latihan/ButirPembahasan';
import type { SoalHasil, StatusSoal } from '@/features/latihan/tipe';
import { teksLatihan } from '@/lang/latihan';
import { cn } from '@/lib/utils';

type Filter = 'semua' | StatusSoal;

const PILIHAN: Filter[] = ['semua', 'salah', 'kosong', 'benar'];

export function DaftarPembahasan({ soal }: { soal: SoalHasil[] }) {
    const teks = teksLatihan.hasil;
    const [filter, setFilter] = useState<Filter>('semua');
    const tampil = filter === 'semua' ? soal : soal.filter((butir) => butir.status === filter);

    return (
        <section aria-labelledby="judul-pembahasan" className="mt-10">
            <h2 id="judul-pembahasan" className="mb-3 text-lg font-medium">
                {teks.pembahasan}
            </h2>
            <div role="group" aria-label={teks.filterAria} className="flex flex-wrap gap-2">
                {PILIHAN.map((pilihan) => {
                    const jumlah = pilihan === 'semua' ? soal.length : soal.filter((butir) => butir.status === pilihan).length;

                    return (
                        <button
                            key={pilihan}
                            type="button"
                            aria-pressed={filter === pilihan}
                            onClick={() => setFilter(pilihan)}
                            className={cn('min-h-11 rounded-md border px-3 text-sm md:min-h-9', filter === pilihan ? 'border-primer bg-primer-lembut font-medium text-primer' : 'border-garis text-redup hover:bg-permukaan')}
                        >
                            {teks.filter[pilihan]} ({jumlah})
                        </button>
                    );
                })}
            </div>
            {tampil.length === 0 ? (
                <p className="mt-6 text-sm text-redup">{teks.tidakAda}</p>
            ) : (
                <div className="mt-2 divide-y divide-garis">
                    {tampil.map((butir) => (
                        <ButirPembahasan key={butir.urutan} soal={butir} />
                    ))}
                </div>
            )}
        </section>
    );
}
