import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { LuColumns3 } from 'react-icons/lu';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { KeadaanKosong } from '@/components/KeadaanKosong';
import { Button } from '@/components/ui/button';
import { BarisPantauan } from '@/features/pantauan/BarisPantauan';
import { teksPantauan } from '@/lang/pantauan';
import { LayoutMember } from '@/layouts/LayoutMember';
import { rute } from '@/lib/rute';
import type { PantauanItem } from '@/types';

interface PropsHalaman {
    item: PantauanItem[];
    ambang: { longgarMaks: number; ketatMin: number };
    batas: { perbandingan: number; catatan: number; pantauan: number };
}

export default function Index({ item, ambang, batas }: PropsHalaman) {
    const teks = teksPantauan;
    const [dipilih, setDipilih] = useState<number[]>([]);
    const pilihanAktif = dipilih.filter((id) => item.some((butir) => butir.id === id));
    const penuh = pilihanAktif.length >= batas.perbandingan;
    const cukup = pilihanAktif.length >= 2;

    function pilih(id: number) {
        setDipilih((sekarang) => (sekarang.includes(id) ? sekarang.filter((butir) => butir !== id) : [...sekarang, id]));
    }

    function bandingkan() {
        router.get(rute.bandingkan, { id: pilihanAktif });
    }

    return (
        <LayoutMember>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            {item.length === 0 ? (
                <KeadaanKosong
                    judul={teks.kosong.judul}
                    pesan={teks.kosong.pesan}
                    aksi={
                        <Button asChild>
                            <Link href={rute.formasi}>{teks.kosong.tombol}</Link>
                        </Button>
                    }
                />
            ) : (
                <div className="flex flex-col gap-4">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-sm text-redup">{teks.jumlah(item.length, batas.pantauan)}</p>
                        <div className="flex flex-col items-start gap-1 sm:items-end">
                            <Button type="button" onClick={bandingkan} disabled={!cukup || pilihanAktif.length > batas.perbandingan}>
                                <LuColumns3 aria-hidden className="h-4 w-4" />
                                {teks.bandingkan.tombol(pilihanAktif.length)}
                            </Button>
                            <p className="text-xs text-redup">{penuh ? teks.bandingkan.penuh(batas.perbandingan) : teks.bandingkan.petunjuk(2, batas.perbandingan)}</p>
                        </div>
                    </div>
                    <ul aria-label={teks.judul} className="divide-y divide-garis overflow-hidden rounded-kartu border border-garis bg-kartu">
                        {item.map((butir) => (
                            <li key={butir.id}>
                                <BarisPantauan pantauan={butir} ambang={ambang} maksCatatan={batas.catatan} dipilih={pilihanAktif.includes(butir.id)} pilihanPenuh={penuh} onPilih={pilih} />
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </LayoutMember>
    );
}
