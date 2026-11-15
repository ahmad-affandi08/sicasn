import { teksPeluang } from '@/lang/peluang';

export function LegendaMeter({ longgarMaks, ketatMin }: { longgarMaks: number; ketatMin: number }) {
    const teks = teksPeluang.legenda;
    const butir = [
        { warna: 'bg-sukses', label: teks.longgar(longgarMaks.toLocaleString('id-ID')) },
        { warna: 'bg-peringatan', label: teks.sedang(longgarMaks.toLocaleString('id-ID'), ketatMin.toLocaleString('id-ID')) },
        { warna: 'bg-bahaya', label: teks.ketat(ketatMin.toLocaleString('id-ID')) },
    ];

    return (
        <ul aria-label={teks.judul} className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-redup">
            {butir.map((item) => (
                <li key={item.label} className="flex items-center gap-1.5">
                    <span aria-hidden className={`h-2 w-2 rounded-full ${item.warna}`} />
                    {item.label}
                </li>
            ))}
            <li>{teks.sementara}</li>
        </ul>
    );
}
