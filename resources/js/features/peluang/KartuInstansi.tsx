import { Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { teksPeluang } from '@/lang/peluang';
import { formatHari, formatRasio } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { InstansiPeluangItem } from '@/types';

import { catatanFormasi, tampilFormasi, tampilPelamar } from './AngkaInstansi';
import { LencanaPersaingan } from './LencanaPersaingan';
import { MeterRasio } from './MeterRasio';

interface PropsKartuInstansi {
    instansi: InstansiPeluangItem;
    ambang?: { longgarMaks: number; ketatMin: number };
    onBuka: (instansi: InstansiPeluangItem) => void;
}

export function KartuInstansi({ instansi, ambang = { longgarMaks: 5, ketatMin: 20 }, onBuka }: PropsKartuInstansi) {
    const teks = teksPeluang.kartu;
    const catatan = catatanFormasi(instansi);

    return (
        <div className="flex flex-col gap-3 p-4">
            <div className="flex items-start justify-between gap-3">
                <div className="flex min-w-0 flex-col gap-0.5">
                    <h3 className="text-base leading-snug font-medium break-words">{instansi.nama}</h3>
                    <span className="text-xs text-redup">{teks.tipe[instansi.tipe]}</span>
                </div>
                <LencanaPersaingan tingkat={instansi.tingkat} />
            </div>
            <dl className="grid grid-cols-3 gap-3 text-sm">
                <div>
                    <dt className="text-xs text-redup">{teks.formasi}</dt>
                    <dd className="font-medium">{tampilFormasi(instansi)}</dd>
                </div>
                <div>
                    <dt className="text-xs text-redup">{teks.pelamar}</dt>
                    <dd className="font-medium">{tampilPelamar(instansi)}</dd>
                </div>
                <div>
                    <dt className="text-xs text-redup">{teks.rasio}</dt>
                    <dd className="font-medium">{formatRasio(instansi.rasio, instansi.batasBawah)}</dd>
                </div>
            </dl>
            <MeterRasio rasio={instansi.rasio} tingkat={instansi.tingkat} batasBawah={instansi.batasBawah} longgarMaks={ambang.longgarMaks} ketatMin={ambang.ketatMin} />
            {catatan && <p className="text-xs text-redup">{catatan}</p>}
            <div className="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                <p className="text-xs text-redup">
                    {instansi.terbaru.sumberNama} · {formatHari(instansi.terbaru.tanggal)}
                </p>
                <div className="flex items-center gap-1">
                    {instansi.formasiAktif > 0 && (
                        <Link href={`${rute.formasi}?instansi=${instansi.id}`} className="inline-flex min-h-11 items-center rounded-lg px-2 text-sm font-medium text-primer underline-offset-4 hover:underline">
                            {teks.formasiAktif(instansi.formasiAktif)}
                        </Link>
                    )}
                    <Button variant="ghost" onClick={() => onBuka(instansi)}>
                        {teks.rincian}
                    </Button>
                </div>
            </div>
        </div>
    );
}
