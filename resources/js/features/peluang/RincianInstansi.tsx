import { Sheet, SheetContent, SheetDescription, SheetTitle } from '@/components/ui/sheet';
import { teksPeluang } from '@/lang/peluang';
import type { InstansiPeluangItem } from '@/types';

import { ButirSnapshot } from './ButirSnapshot';
import { LencanaPersaingan } from './LencanaPersaingan';

interface PropsRincianInstansi {
    instansi: InstansiPeluangItem | null;
    onTutup: () => void;
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
