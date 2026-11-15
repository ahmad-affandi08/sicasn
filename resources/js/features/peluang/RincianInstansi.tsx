import { LuExternalLink } from 'react-icons/lu';

import { Badge } from '@/components/ui/badge';
import { Sheet, SheetContent, SheetDescription, SheetTitle } from '@/components/ui/sheet';
import { teksPeluang } from '@/lang/peluang';
import { formatAngka, formatHari } from '@/lib/format';
import type { InstansiPeluangItem, SnapshotItem } from '@/types';

import { LencanaPersaingan } from './LencanaPersaingan';

interface PropsRincianInstansi {
    instansi: InstansiPeluangItem | null;
    onTutup: () => void;
}

function Angka({ label, nilai }: { label: string; nilai: number | null }) {
    return (
        <div>
            <dt className="text-redup">{label}</dt>
            <dd className="font-medium">{formatAngka(nilai)}</dd>
        </div>
    );
}

function ButirSnapshot({ snapshot }: { snapshot: SnapshotItem }) {
    const teks = teksPeluang.rincian;

    return (
        <li className="flex flex-col gap-3 rounded-xl border border-garis p-4">
            <div className="flex flex-wrap items-center gap-2">
                <p className="font-medium">{formatHari(snapshot.tanggal)}</p>
                <Badge variant={snapshot.status === 'akhir' ? 'sukses' : 'peringatan'}>{teks.status[snapshot.status]}</Badge>
                <Badge variant="netral">{teks.tingkatData(snapshot.tingkatData)}</Badge>
            </div>
            <dl className="grid grid-cols-2 gap-3 text-sm">
                <Angka label={teks.formasi} nilai={snapshot.formasi} />
                <Angka label={teks.pelamar} nilai={snapshot.pelamar} />
                <Angka label={teks.submit} nilai={snapshot.submit} />
                <Angka label={teks.lolos} nilai={snapshot.lolosAdministrasi} />
            </dl>
            <div className="flex flex-col gap-1 text-xs text-redup">
                <p>
                    {teks.keyakinan[snapshot.keyakinan]} · {snapshot.sumberNama}
                </p>
                {snapshot.catatan && <p>{snapshot.catatan}</p>}
                {snapshot.sumberUrl && (
                    <a href={snapshot.sumberUrl} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1 text-primer underline">
                        {teks.sumber}
                        <LuExternalLink aria-hidden className="h-3.5 w-3.5" />
                    </a>
                )}
            </div>
        </li>
    );
}

export function RincianInstansi({ instansi, onTutup }: PropsRincianInstansi) {
    const teks = teksPeluang.rincian;

    return (
        <Sheet open={instansi !== null} onOpenChange={(buka) => !buka && onTutup()}>
            <SheetContent className="max-w-lg overflow-y-auto">
                {instansi && (
                    <>
                        <div className="flex flex-col gap-2 pr-10">
                            <SheetTitle className="break-words">{instansi.nama}</SheetTitle>
                            <SheetDescription>{teks.penjelasanTingkat}</SheetDescription>
                            <div>
                                <LencanaPersaingan tingkat={instansi.tingkat} />
                            </div>
                        </div>
                        <h3 className="font-medium">{teks.riwayat}</h3>
                        <ul className="flex flex-col gap-3">
                            {instansi.riwayat.map((snapshot) => (
                                <ButirSnapshot key={`${snapshot.tanggal}-${snapshot.sumberNama}-${snapshot.status}-${snapshot.keyakinan}`} snapshot={snapshot} />
                            ))}
                        </ul>
                    </>
                )}
            </SheetContent>
        </Sheet>
    );
}
