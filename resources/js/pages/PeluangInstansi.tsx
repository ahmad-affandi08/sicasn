import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { LuExternalLink, LuInfo } from 'react-icons/lu';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { KeadaanKosong } from '@/components/KeadaanKosong';
import { Paginasi } from '@/components/Paginasi';
import { Alert } from '@/components/ui/alert';
import { parameterFilter } from '@/features/peluang/hitungFilter';
import { KartuInstansi } from '@/features/peluang/KartuInstansi';
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

            <div className="flex flex-col gap-6">
                <Alert variant="peringatan">
                    <LuInfo aria-hidden className="mt-0.5 h-5 w-5 shrink-0" />
                    <div className="flex flex-col gap-1">
                        <p className="font-medium">{teks.disclaimer.judul}</p>
                        <p>{teks.disclaimer.isi}</p>
                        <a href="https://sscasn.bkn.go.id" target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1 font-medium text-primer underline">
                            {teks.disclaimer.tautan}
                            <LuExternalLink aria-hidden className="h-4 w-4" />
                        </a>
                    </div>
                </Alert>

                <RingkasanNasional angka={nasional} />

                <PanelFilter key={JSON.stringify(filter)} filter={filter} tingkat={tingkat} onTerapkan={(baru) => buka(baru)} />

                <div className="flex flex-col gap-1 text-sm text-redup">
                    <p>{teks.hasil.jumlah(item.length, paginasi.total)}</p>
                    <p>{teks.ambang(ambang.longgarMaks, ambang.ketatMin)}</p>
                    <p>{teks.batasBawah}</p>
                </div>

                {totalInstansi === 0 && <KeadaanKosong judul={teks.hasil.belumAdaJudul} pesan={teks.hasil.belumAdaPesan} />}
                {totalInstansi > 0 && item.length === 0 && <KeadaanKosong judul={teks.hasil.kosongJudul} pesan={teks.hasil.kosongPesan} />}

                {item.length > 0 && (
                    <>
                        <div className="hidden lg:block">
                            <TabelInstansi instansi={item} urut={filter.urut} onUrut={(urut) => buka({ ...filter, urut })} onBuka={setDibuka} />
                        </div>
                        <ul className="grid gap-4 md:grid-cols-2 lg:hidden">
                            {item.map((butir) => (
                                <li key={butir.id}>
                                    <KartuInstansi instansi={butir} onBuka={setDibuka} />
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
