import { Link } from '@inertiajs/react';
import { LuChevronRight } from 'react-icons/lu';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { teksPeluang } from '@/lang/peluang';
import { formatHari, formatRasio } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { InstansiPeluangItem } from '@/types';

import { catatanFormasi, tampilFormasi, tampilPelamar } from './AngkaInstansi';
import { LencanaPersaingan } from './LencanaPersaingan';

interface PropsKartuInstansi {
    instansi: InstansiPeluangItem;
    onBuka: (instansi: InstansiPeluangItem) => void;
}

export function KartuInstansi({ instansi, onBuka }: PropsKartuInstansi) {
    const teks = teksPeluang.kartu;
    const catatan = catatanFormasi(instansi);

    return (
        <Card>
            <CardContent className="flex flex-col gap-4 pt-6">
                <div className="flex flex-col gap-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <LencanaPersaingan tingkat={instansi.tingkat} />
                        <span className="text-xs text-redup">{teks.tipe[instansi.tipe]}</span>
                    </div>
                    <h3 className="text-lg font-medium break-words">{instansi.nama}</h3>
                </div>
                <dl className="grid grid-cols-3 gap-3 text-sm">
                    <div>
                        <dt className="text-redup">{teks.formasi}</dt>
                        <dd className="font-medium">{tampilFormasi(instansi)}</dd>
                    </div>
                    <div>
                        <dt className="text-redup">{teks.pelamar}</dt>
                        <dd className="font-medium">{tampilPelamar(instansi)}</dd>
                    </div>
                    <div>
                        <dt className="text-redup">{teks.rasio}</dt>
                        <dd className="font-medium">{formatRasio(instansi.rasio, instansi.batasBawah)}</dd>
                    </div>
                </dl>
                {catatan && <p className="text-xs text-redup">{catatan}</p>}
                {instansi.formasiAktif > 0 && (
                    <Link href={`${rute.formasi}?instansi=${instansi.id}`} className="inline-flex min-h-11 items-center self-start text-sm font-medium text-primer underline">
                        {teks.formasiAktif(instansi.formasiAktif)}
                    </Link>
                )}
                <div className="flex items-center justify-between gap-2 border-t border-garis pt-3">
                    <p className="text-xs text-redup">
                        {instansi.terbaru.sumberNama} · {formatHari(instansi.terbaru.tanggal)}
                    </p>
                    <Button variant="ghost" onClick={() => onBuka(instansi)}>
                        {teks.rincian}
                        <LuChevronRight aria-hidden className="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}
