import { teksPeluang } from '@/lang/peluang';
import { formatHari } from '@/lib/format';
import type { AngkaNasionalItem } from '@/types';

export function RingkasanNasional({ angka }: { angka: AngkaNasionalItem[] }) {
    const teks = teksPeluang.nasional;

    if (angka.length === 0) {
        return null;
    }

    return (
        <section aria-label={teks.judul}>
            <dl className="grid grid-cols-2 overflow-hidden rounded-kartu border border-garis bg-kartu lg:grid-cols-4">
                {angka.map((butir) => (
                    <div key={butir.jenis} className="flex flex-col gap-1 border-b border-garis p-4 even:border-l lg:border-b-0 lg:border-l lg:first:border-l-0">
                        <dt className="text-xs text-redup">{teks.jenis[butir.jenis]}</dt>
                        <dd className="text-2xl leading-none font-medium">{butir.nilai.toLocaleString('id-ID')}</dd>
                        <dd className="text-xs text-redup">
                            {teks.per(formatHari(butir.tanggal))} ·{' '}
                            {butir.sumberUrl ? (
                                <a href={butir.sumberUrl} target="_blank" rel="noopener noreferrer" className="underline underline-offset-2 hover:text-teks">
                                    {butir.sumberNama} ↗
                                </a>
                            ) : (
                                butir.sumberNama
                            )}
                        </dd>
                        <dd className="text-xs font-medium text-redup">{butir.keyakinan === 'resmi' ? teks.resmi : teks.turunan}</dd>
                    </div>
                ))}
            </dl>
        </section>
    );
}
