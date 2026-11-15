import { LuExternalLink } from 'react-icons/lu';

import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { teksPeluang } from '@/lang/peluang';
import { formatHari } from '@/lib/format';
import type { AngkaNasionalItem } from '@/types';

export function RingkasanNasional({ angka }: { angka: AngkaNasionalItem[] }) {
    const teks = teksPeluang.nasional;

    if (angka.length === 0) {
        return null;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>{teks.judul}</CardTitle>
            </CardHeader>
            <CardContent>
                <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {angka.map((butir) => (
                        <div key={butir.jenis} className="flex flex-col gap-1">
                            <dt className="text-sm text-redup">{teks.jenis[butir.jenis]}</dt>
                            <dd className="text-2xl font-bold">{butir.nilai.toLocaleString('id-ID')}</dd>
                            <dd className="flex flex-wrap items-center gap-2 text-xs text-redup">
                                <Badge variant={butir.keyakinan === 'resmi' ? 'sukses' : 'netral'}>{butir.keyakinan === 'resmi' ? teks.resmi : teks.turunan}</Badge>
                                {teks.per(formatHari(butir.tanggal))}
                            </dd>
                            <dd className="text-xs text-redup">
                                {butir.sumberUrl ? (
                                    <a href={butir.sumberUrl} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1 text-primer underline">
                                        {butir.sumberNama}
                                        <LuExternalLink aria-hidden className="h-3.5 w-3.5 shrink-0" />
                                    </a>
                                ) : (
                                    butir.sumberNama
                                )}
                            </dd>
                        </div>
                    ))}
                </dl>
            </CardContent>
        </Card>
    );
}
