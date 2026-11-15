import { Link } from '@inertiajs/react';
import { LuAccessibility, LuChevronRight, LuGraduationCap, LuMapPin, LuUsers } from 'react-icons/lu';

import { TombolPantau } from '@/components/TombolPantau';
import { Badge } from '@/components/ui/badge';
import { teksFormasi } from '@/lang/formasi';
import { rute } from '@/lib/rute';
import type { FormasiItem } from '@/types';

import { RingkasPeluang } from './RingkasPeluang';

interface PropsKartuFormasi {
    formasi: FormasiItem;
    ambang?: { longgarMaks: number; ketatMin: number };
    dipantau?: boolean;
    muatUlang?: string[];
}

export function KartuFormasi({ formasi, ambang, dipantau = false, muatUlang }: PropsKartuFormasi) {
    const teks = teksFormasi.kartu;
    const usia = teks.usia(formasi.usiaMin, formasi.usiaMaks);

    return (
        <article className="grid gap-4 p-4 md:grid-cols-[1fr_15rem] md:gap-6 md:p-5">
            <div className="flex min-w-0 flex-col gap-2.5">
                <div className="flex flex-col gap-0.5">
                    <p className="text-sm text-redup break-words">{formasi.instansi}</p>
                    <h3 className="text-base leading-snug font-medium break-words">{formasi.jabatan}</h3>
                </div>
                {(formasi.disabilitas === true || formasi.jenisFormasi !== 'UMUM') && (
                    <div className="flex flex-wrap gap-2">
                        {formasi.disabilitas === true ? (
                            <Badge variant="info">
                                <LuAccessibility aria-hidden className="h-3.5 w-3.5" />
                                {teks.disabilitas}
                            </Badge>
                        ) : (
                            <Badge variant="netral">{formasi.jenisFormasi}</Badge>
                        )}
                    </div>
                )}
                <ul className="flex flex-col gap-1.5 text-sm">
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
                            {teks.kuota} <strong className="font-medium">{formasi.kuota.toLocaleString('id-ID')}</strong>
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
                <div className="flex flex-wrap items-center gap-2">
                    <Link href={rute.detailFormasi(formasi.id)} className="-ml-2 inline-flex min-h-11 items-center gap-1 rounded-lg px-2 text-sm font-medium text-primer hover:bg-primer-lembut md:min-h-9">
                        {teks.lihat}
                        <span className="sr-only"> {formasi.jabatan}</span>
                        <LuChevronRight aria-hidden className="h-4 w-4" />
                    </Link>
                    <TombolPantau formasiId={formasi.id} jabatan={formasi.jabatan} dipantau={dipantau} muatUlang={muatUlang} />
                </div>
            </div>
            <div className="border-t border-garis pt-4 md:border-t-0 md:border-l md:pt-0 md:pl-6">
                <RingkasPeluang peluang={formasi.peluang} jabatan={formasi.peluangJabatan} ambang={ambang} lengkap={false} />
            </div>
        </article>
    );
}
