import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

import { teksFormasi } from '@/lang/formasi';
import { teksPantauan } from '@/lang/pantauan';
import { rute } from '@/lib/rute';
import type { PantauanItem } from '@/types';

import { SelPersainganInstansi, SelPersainganJabatan } from './SelPersaingan';

interface PropsTabelPerbandingan {
    item: PantauanItem[];
    ambang: { longgarMaks: number; ketatMin: number };
}

interface BarisTabel {
    judul: string;
    isi: (butir: PantauanItem) => ReactNode;
}

export function TabelPerbandingan({ item, ambang }: PropsTabelPerbandingan) {
    const teks = teksPantauan.halamanBandingkan;

    const baris: BarisTabel[] = [
        { judul: teks.baris.lokasi, isi: (b) => b.lokasi },
        { judul: teks.baris.kuota, isi: (b) => <span className="text-lg font-medium">{b.kuota.toLocaleString('id-ID')}</span> },
        { judul: teks.baris.usia, isi: (b) => teksFormasi.kartu.usia(b.usiaMin, b.usiaMaks) || '-' },
        { judul: teks.baris.jenis, isi: (b) => b.jenisFormasi },
        { judul: teks.baris.pendidikan, isi: (b) => (b.kualifikasiTersedia && b.kualifikasi ? b.kualifikasi : <span className="text-redup italic">{teks.kualifikasiKosong}</span>) },
        { judul: teks.baris.persaingan, isi: (b) => <SelPersainganInstansi peluang={b.peluang} ambang={ambang} /> },
        { judul: teks.baris.jabatan, isi: (b) => <SelPersainganJabatan jabatan={b.peluangJabatan} ambang={ambang} /> },
        { judul: teks.baris.catatan, isi: (b) => (b.catatan ? <span className="whitespace-pre-line">{b.catatan}</span> : <span className="text-redup">{teks.tanpaCatatan}</span>) },
    ];

    return (
        <div className="overflow-x-auto rounded-kartu border border-garis bg-kartu">
            <table className="w-full min-w-[40rem] border-collapse text-left text-sm">
                <caption className="sr-only">{teks.judul}</caption>
                <thead>
                    <tr className="border-b border-garis bg-permukaan">
                        <th scope="col" className="sticky left-0 z-10 w-32 bg-permukaan p-3 align-bottom text-xs font-medium text-redup">
                            {teks.baris.formasi}
                        </th>
                        {item.map((butir) => (
                            <th key={butir.id} scope="col" className="min-w-52 p-3 align-top font-normal">
                                <span className="block text-xs text-redup">{butir.instansi}</span>
                                <span className="block text-base font-medium">{butir.jabatan}</span>
                                <Link href={rute.detailFormasi(butir.id)} className="mt-1 inline-flex min-h-9 items-center text-sm font-medium text-primer underline-offset-4 hover:underline">
                                    {teks.detail}
                                </Link>
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {baris.map((b) => (
                        <tr key={b.judul} className="border-b border-garis last:border-b-0">
                            <th scope="row" className="sticky left-0 z-10 bg-kartu p-3 align-top text-xs font-medium text-redup">
                                {b.judul}
                            </th>
                            {item.map((butir) => (
                                <td key={butir.id} className="p-3 align-top break-words">
                                    {b.isi(butir)}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
