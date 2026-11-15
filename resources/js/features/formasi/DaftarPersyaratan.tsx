import { Badge } from '@/components/ui/badge';
import { teksFormasi } from '@/lang/formasi';
import type { DetailFormasiItem } from '@/types';

export function DaftarPersyaratan({ persyaratan }: { persyaratan: DetailFormasiItem['persyaratan'] }) {
    const teks = teksFormasi.detail;

    if (persyaratan.length === 0) {
        return <p className="text-sm text-redup">{teks.persyaratanKosong}</p>;
    }

    return (
        <ul className="flex flex-col divide-y divide-garis">
            {persyaratan.map((butir) => (
                <li key={butir.uraian} className="flex flex-col gap-1.5 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <p className="break-words">{butir.uraian}</p>
                    <Badge variant={butir.wajib ? 'default' : 'netral'} className="self-start">
                        {butir.wajib ? teks.wajib : teks.opsional}
                    </Badge>
                </li>
            ))}
        </ul>
    );
}
