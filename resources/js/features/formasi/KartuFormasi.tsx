import { Link } from '@inertiajs/react';
import { LuAccessibility, LuChevronRight, LuGraduationCap, LuMapPin, LuUsers } from 'react-icons/lu';

import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { teksFormasi } from '@/lang/formasi';
import { rute } from '@/lib/rute';
import type { FormasiItem } from '@/types';

import { RingkasPeluang } from './RingkasPeluang';

export function KartuFormasi({ formasi }: { formasi: FormasiItem }) {
    const teks = teksFormasi.kartu;
    const usia = teks.usia(formasi.usiaMin, formasi.usiaMaks);

    return (
        <Card className="h-full">
            <CardContent className="flex h-full flex-col gap-4 pt-6">
                <div className="flex flex-col gap-1.5">
                    <p className="text-sm text-redup break-words">{formasi.instansi}</p>
                    <h3 className="text-lg leading-snug font-medium break-words">{formasi.jabatan}</h3>
                    <div className="flex flex-wrap gap-2 empty:hidden">
                        {formasi.disabilitas === true && (
                            <Badge variant="info">
                                <LuAccessibility aria-hidden className="h-3.5 w-3.5" />
                                {teks.disabilitas}
                            </Badge>
                        )}
                        {formasi.jenisFormasi !== 'UMUM' && formasi.disabilitas !== true && <Badge variant="netral">{formasi.jenisFormasi}</Badge>}
                    </div>
                </div>

                <ul className="flex flex-col gap-2 text-sm">
                    <li className="flex items-start gap-2">
                        <LuMapPin aria-hidden className="mt-0.5 h-4 w-4 shrink-0 text-redup" />
                        <span className="break-words">
                            <span className="sr-only">{teks.lokasi}: </span>
                            {formasi.lokasi}
                        </span>
                    </li>
                    <li className="flex items-start gap-2">
                        <LuUsers aria-hidden className="mt-0.5 h-4 w-4 shrink-0 text-redup" />
                        <span>
                            {teks.kuota} <strong>{formasi.kuota.toLocaleString('id-ID')}</strong>
                            {usia && <span className="text-redup"> · {usia}</span>}
                        </span>
                    </li>
                    <li className="flex items-start gap-2">
                        <LuGraduationCap aria-hidden className="mt-0.5 h-4 w-4 shrink-0 text-redup" />
                        <span className={formasi.kualifikasiTersedia ? 'line-clamp-2 break-words' : 'text-redup italic'} title={formasi.kualifikasi ?? undefined}>
                            <span className="sr-only">{teks.pendidikan}: </span>
                            {formasi.kualifikasiTersedia && formasi.kualifikasi ? formasi.kualifikasi : teks.kualifikasiKosong}
                        </span>
                    </li>
                </ul>

                <RingkasPeluang peluang={formasi.peluang} />

                <div className="mt-auto flex justify-end border-t border-garis pt-3">
                    <Link href={rute.detailFormasi(formasi.id)} className="inline-flex min-h-11 items-center gap-1 rounded-xl px-3 text-sm font-medium text-primer hover:bg-primer-lembut">
                        {teks.lihat}
                        <span className="sr-only"> {formasi.jabatan}</span>
                        <LuChevronRight aria-hidden className="h-4 w-4" />
                    </Link>
                </div>
            </CardContent>
        </Card>
    );
}
