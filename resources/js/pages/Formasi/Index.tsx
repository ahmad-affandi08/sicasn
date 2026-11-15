import { Head, Link, router } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { LuSearch, LuSlidersHorizontal } from 'react-icons/lu';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { KeadaanKosong } from '@/components/KeadaanKosong';
import { Paginasi } from '@/components/Paginasi';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
    tingkat: { kode: string; label: string }[];
    opsi: OpsiFormasiItem;
    filter: FilterFormasiItem;
}

export default function Index({ item, paginasi, totalAktif, tingkat, opsi, filter }: PropsHalaman) {
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

            <Alert variant="peringatan" className="mb-6">
                <p>
                    {teks.disclaimer}{' '}
                    <a href="https://sscasn.bkn.go.id" target="_blank" rel="noopener noreferrer" className="font-medium text-primer underline">
                        {teks.sumberResmi}
                    </a>
                </p>
            </Alert>

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
                <div className="lg:grid lg:grid-cols-[19rem_1fr] lg:items-start lg:gap-8">
                    {lebar && (
                        <aside aria-label={teks.filter.judul} className="sticky top-24">
                            <Card>
                                <CardContent className="pt-6">{panel}</CardContent>
                            </Card>
                        </aside>
                    )}

                    <div className="flex min-w-0 flex-col gap-5">
                        <form onSubmit={kirimCari} role="search" className="flex gap-2">
                            <div className="relative flex-1">
                                <LuSearch aria-hidden className="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-redup" />
                                <Input type="search" aria-label={teks.cari.label} placeholder={teks.cari.label} className="pl-12" value={cari} onChange={(kejadian) => setCari(kejadian.target.value)} />
                            </div>
                            <Button type="submit">{teks.cari.tombol}</Button>
                            {!lebar && (
                                <Button type="button" variant="outline" onClick={() => setPanelTerbuka(true)}>
                                    <LuSlidersHorizontal aria-hidden className="h-4 w-4" />
                                    {aktif > 0 ? teks.filter.tombolAktif(aktif) : teks.filter.tombol}
                                </Button>
                            )}
                        </form>

                        <p className="text-sm text-redup">
                            {teks.hasil.jumlah(item.length, paginasi.total)} · {teks.hasil.aktif(totalAktif)}
                        </p>

                        {item.length === 0 ? (
                            <KeadaanKosong judul={teks.kosongFilter.judul} pesan={teks.kosongFilter.pesan} />
                        ) : (
                            <>
                                <ul className="grid gap-4 md:grid-cols-2">
                                    {item.map((butir) => (
                                        <li key={butir.id}>
                                            <KartuFormasi formasi={butir} />
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
