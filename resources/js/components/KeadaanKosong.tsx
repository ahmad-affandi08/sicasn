import type { ReactNode } from 'react';

import { IlustrasiKosong } from '@/components/ilustrasi';

interface PropsKeadaanKosong {
    judul: string;
    pesan: string;
    aksi?: ReactNode;
}

export function KeadaanKosong({ judul, pesan, aksi }: PropsKeadaanKosong) {
    return (
        <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed border-garis p-8 text-center">
            <IlustrasiKosong className="h-32 w-40" judul={judul} />
            <h2 className="text-lg font-medium">{judul}</h2>
            <p className="max-w-md text-redup">{pesan}</p>
            {aksi}
        </div>
    );
}
