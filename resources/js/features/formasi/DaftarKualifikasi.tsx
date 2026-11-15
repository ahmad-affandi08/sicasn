import { LuGraduationCap } from 'react-icons/lu';

import { teksFormasi } from '@/lang/formasi';
import type { DetailFormasiItem } from '@/types';

export function DaftarKualifikasi({ item, tersedia }: { item: DetailFormasiItem['kualifikasiItem']; tersedia: boolean }) {
    const teks = teksFormasi;

    if (!tersedia || item.length === 0) {
        return <p className="text-sm text-redup italic">{teks.kartu.kualifikasiKosong}</p>;
    }

    return (
        <ul className="flex flex-col gap-2">
            {item.map((butir) => (
                <li key={`${butir.jenjang}-${butir.jurusan ?? '*'}`} className="flex items-start gap-2">
                    <LuGraduationCap aria-hidden className="mt-0.5 h-4 w-4 shrink-0 text-redup" />
                    <span className="break-words">
                        <strong>{butir.jenjang}</strong> · {butir.semuaJurusan ? teks.detail.semuaJurusan : butir.jurusan}
                    </span>
                </li>
            ))}
        </ul>
    );
}
