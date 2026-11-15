import { Head, Link, router } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { LuSearch, LuSlidersHorizontal } from 'react-icons/lu';

import { Catatan } from '@/components/Catatan';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { KeadaanKosong } from '@/components/KeadaanKosong';
import { Paginasi } from '@/components/Paginasi';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Sheet, SheetContent, SheetDescription, SheetTitle } from '@/components/ui/sheet';
import { hitungFilterAktif, parameterFilter } from '@/features/formasi/filter';
import { KartuFormasi } from '@/features/formasi/KartuFormasi';
import { PanelFilterFormasi } from '@/features/formasi/PanelFilterFormasi';
import { useLayarLebar } from '@/hooks/useLayarLebar';
import { teksFormasi } from '@/lang/formasi';
import { LayoutMember } from '@/layouts/LayoutMember';
import { rute } from '@/lib/rute';
import type { FilterFormasiItem, FormasiItem, OpsiFormasiItem } from '@/types';

interface PropsHalaman {
    item: FormasiItem[];
    paginasi: { total: number; halaman: number; terakhir: number; perHalaman: number };
    totalAktif: number;
    ambang: { longgarMaks: number; ketatMin: number };
    dipantau: number[];
    tingkat: { kode: string; label: string }[];
    opsi: OpsiFormasiItem;
    filter: FilterFormasiItem;
}

export default function Index({ item, paginasi, totalAktif, ambang, dipantau, tingkat, opsi, filter }: PropsHalaman) {
    const teks = teksFormasi;
    const lebar = useLayarLebar();
    const [cari, setCari] = useState(filter.cari);
    const [panelTerbuka, setPanelTerbuka] = useState(false);
    const aktif = hitungFilterAktif(filter);

    function buka(filterBaru: FilterFormasiItem, halaman?: number) {
        setPanelTerbuka(false);
        router.get(rute.formasi, parameterFilter(filterBaru, halaman), { preserveState: true, preserveScroll: halaman !== undefined, replace: true });
    }

    function kirimCari(kejadian: FormEvent) {
        kejadian.preventDefault();
        buka({ ...filter, cari: cari.trim() });
    }

    const panel = <PanelFilterFormasi key={JSON.stringify(filter)} filter={filter} opsi={opsi} tingkat={tingkat} onTerapkan={buka} />;

    return (
        <LayoutMember lebar>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <Catatan className="mb-6">
                <span className="md:hidden">{teks.disclaimerRingkas}</span>
                <span className="hidden md:inline">{teks.disclaimer}</span>{' '}
                <a href="https://sscasn.bkn.go.id" target="_blank" rel="noopener noreferrer" className="font-medium text-primer underline underline-offset-2">
                    {teks.sumberResmi} ↗
                </a>
            </Catatan>

            {totalAktif === 0 ? (
                <KeadaanKosong
                    judul={teks.kosongBelumDibuka.judul}
                    pesan={teks.kosongBelumDibuka.pesan}
                    aksi={
                        <Button asChild>
                            <Link href={rute.peluangInstansi}>{teks.kosongBelumDibuka.tombol}</Link>
                        </Button>
                    }
                />
            ) : (
                <div className="lg:grid lg:grid-cols-[18rem_1fr] lg:items-start lg:gap-8">
                    {lebar && (
                        <aside aria-label={teks.filter.judul} className="sticky top-20 max-h-[calc(100dvh-6rem)] overflow-y-auto rounded-kartu border border-garis bg-kartu p-4">
                            {panel}
                        </aside>
                    )}

                    <div className="flex min-w-0 flex-col gap-5">
                        <form onSubmit={kirimCari} role="search" className="flex gap-2">
                            <div className="relative flex-1">
                                <LuSearch aria-hidden className="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-redup" />
                                <Input type="search" aria-label={teks.cari.label} placeholder={teks.cari.placeholder} className="pl-12" value={cari} onChange={(kejadian) => setCari(kejadian.target.value)} />
                            </div>
                            <Button type="submit">{teks.cari.tombol}</Button>
                            {!lebar && (
                                <Button type="button" variant="outline" onClick={() => setPanelTerbuka(true)}>
                                    <LuSlidersHorizontal aria-hidden className="h-4 w-4" />
                                    {aktif > 0 ? teks.filter.tombolAktif(aktif) : teks.filter.tombol}
                                </Button>
                            )}
                        </form>

                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm text-redup">
                                {teks.hasil.jumlah(item.length, paginasi.total)} · {teks.hasil.aktif(totalAktif)}
                            </p>
                            <Select aria-label={teks.filter.urut} className="sm:w-60" value={filter.urut} onChange={(kejadian) => buka({ ...filter, urut: kejadian.target.value })}>
                                {Object.entries(teks.filter.urutOpsi).map(([kode, label]) => (
                                    <option key={kode} value={kode}>
                                        {label}
                                    </option>
                                ))}
                            </Select>
                        </div>

                        {item.length === 0 ? (
                            <KeadaanKosong judul={teks.kosongFilter.judul} pesan={teks.kosongFilter.pesan} />
                        ) : (
                            <>
                                <ul aria-label={teks.daftar} className="divide-y divide-garis overflow-hidden rounded-kartu border border-garis bg-kartu">
                                    {item.map((butir) => (
                                        <li key={butir.id}>
                                            <KartuFormasi formasi={butir} ambang={ambang} dipantau={dipantau.includes(butir.id)} />
                                        </li>
                                    ))}
                                </ul>
                                <Paginasi halaman={paginasi.halaman} terakhir={paginasi.terakhir} onPindah={(halaman) => buka(filter, halaman)} />
                            </>
                        )}
                    </div>
                </div>
            )}

            {!lebar && (
                <Sheet open={panelTerbuka} onOpenChange={setPanelTerbuka}>
                    <SheetContent className="max-w-md overflow-y-auto">
                        <SheetTitle>{teks.filter.judul}</SheetTitle>
                        <SheetDescription className="sr-only">{teks.sub}</SheetDescription>
                        {panel}
                    </SheetContent>
                </Sheet>
            )}
        </LayoutMember>
    );
}
