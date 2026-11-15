import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

import { Catatan } from '@/components/Catatan';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { KeadaanKosong } from '@/components/KeadaanKosong';
import { Paginasi } from '@/components/Paginasi';
import { parameterFilter } from '@/features/peluang/hitungFilter';
import { KartuInstansi } from '@/features/peluang/KartuInstansi';
import { LegendaMeter } from '@/features/peluang/LegendaMeter';
import { PanelFilter } from '@/features/peluang/PanelFilter';
import { RincianInstansi } from '@/features/peluang/RincianInstansi';
import { RingkasanNasional } from '@/features/peluang/RingkasanNasional';
import { TabelInstansi } from '@/features/peluang/TabelInstansi';
import { teksPeluang } from '@/lang/peluang';
import { LayoutMember } from '@/layouts/LayoutMember';
import { rute } from '@/lib/rute';
import type { AngkaNasionalItem, FilterPeluangItem, InstansiPeluangItem } from '@/types';

interface PropsHalaman {
    item: InstansiPeluangItem[];
    paginasi: { total: number; halaman: number; terakhir: number; perHalaman: number };
    totalInstansi: number;
    nasional: AngkaNasionalItem[];
    ambang: { longgarMaks: number; ketatMin: number };
    tingkat: { kode: string; label: string }[];
    filter: FilterPeluangItem;
}

export default function PeluangInstansi({ item, paginasi, totalInstansi, nasional, ambang, tingkat, filter }: PropsHalaman) {
    const teks = teksPeluang;
    const [dibuka, setDibuka] = useState<InstansiPeluangItem | null>(null);

    function buka(filterBaru: FilterPeluangItem, halaman?: number) {
        router.get(rute.peluangInstansi, parameterFilter(filterBaru, halaman), { preserveState: true, preserveScroll: halaman !== undefined, replace: true });
    }

    return (
        <LayoutMember>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <div className="flex flex-col gap-5">
                <Catatan>
                    <span className="md:hidden">{teks.disclaimer.ringkas}</span>
                    <span className="hidden md:inline">{teks.disclaimer.isi}</span>{' '}
                    <a href="https://sscasn.bkn.go.id" target="_blank" rel="noopener noreferrer" className="font-medium text-primer underline underline-offset-2">
                        {teks.disclaimer.tautan} ↗
                    </a>
                </Catatan>

                <RingkasanNasional angka={nasional} />

                <PanelFilter key={JSON.stringify(filter)} filter={filter} tingkat={tingkat} onTerapkan={(baru) => buka(baru)} />

                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-sm text-redup">{teks.hasil.jumlah(item.length, paginasi.total)}</p>
                    <LegendaMeter longgarMaks={ambang.longgarMaks} ketatMin={ambang.ketatMin} />
                </div>

                {totalInstansi === 0 && <KeadaanKosong judul={teks.hasil.belumAdaJudul} pesan={teks.hasil.belumAdaPesan} />}
                {totalInstansi > 0 && item.length === 0 && <KeadaanKosong judul={teks.hasil.kosongJudul} pesan={teks.hasil.kosongPesan} />}

                {item.length > 0 && (
                    <>
                        <div className="hidden lg:block">
                            <TabelInstansi instansi={item} urut={filter.urut} ambang={ambang} onUrut={(urut) => buka({ ...filter, urut })} onBuka={setDibuka} />
                        </div>
                        <ul className="divide-y divide-garis overflow-hidden rounded-kartu border border-garis bg-kartu lg:hidden">
                            {item.map((butir) => (
                                <li key={butir.id}>
                                    <KartuInstansi instansi={butir} ambang={ambang} onBuka={setDibuka} />
                                </li>
                            ))}
                        </ul>
                        <Paginasi halaman={paginasi.halaman} terakhir={paginasi.terakhir} onPindah={(halaman) => buka(filter, halaman)} />
                    </>
                )}
            </div>

            <RincianInstansi instansi={dibuka} onTutup={() => setDibuka(null)} />
        </LayoutMember>
    );
}
