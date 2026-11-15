import { LuCheck, LuFlag } from 'react-icons/lu';

import { Badge } from '@/components/ui/badge';
import type { SoalHasil } from '@/features/latihan/tipe';
import { teksLatihan } from '@/lang/latihan';
import { cn } from '@/lib/utils';

const VARIAN = { benar: 'sukses', salah: 'bahaya', kosong: 'netral' } as const;

export function ButirPembahasan({ soal }: { soal: SoalHasil }) {
    const teks = teksLatihan.hasil;
    const tkp = soal.kategori === 'tkp';
    const kunci = Math.max(...soal.opsi.map((opsi) => opsi.skor));
    const labelStatus = tkp ? teks.statusTkp[soal.status] : teks.status[soal.status];

    return (
        <article className="flex flex-col gap-3 py-5" aria-labelledby={`soal-${soal.urutan}`}>
            <header className="flex flex-wrap items-center gap-2">
                <h3 id={`soal-${soal.urutan}`} className="font-medium tabular-nums">
                    {soal.urutan}.
                </h3>
                <Badge variant={VARIAN[soal.status]}>{labelStatus}</Badge>
                <span className="text-sm text-redup">
                    {teksLatihan.kategori[soal.kategori]} · {soal.subkategori}
                </span>
                {soal.ragu && (
                    <span className="inline-flex items-center gap-1 text-xs text-redup">
                        <LuFlag aria-hidden className="h-3 w-3" />
                        {teks.ragu}
                    </span>
                )}
            </header>

            <p className="leading-relaxed whitespace-pre-line">{soal.pertanyaan}</p>

            <ul className="flex flex-col gap-1.5">
                {soal.opsi.map((opsi) => {
                    const dipilih = opsi.id === soal.opsiDipilihId;
                    const benar = opsi.skor === kunci;

                    return (
                        <li key={opsi.id} className={cn('flex items-start gap-2 rounded-md border px-3 py-2 text-sm', benar ? 'border-sukses bg-sukses-lembut' : dipilih ? 'border-bahaya bg-bahaya-lembut' : 'border-garis')}>
                            <span className="font-medium">{opsi.label}.</span>
                            <span className="flex-1 whitespace-pre-line">{opsi.teks}</span>
                            <span className="flex shrink-0 flex-col items-end gap-0.5 text-xs">
                                {benar && (
                                    <span className="inline-flex items-center gap-1 font-medium text-sukses">
                                        <LuCheck aria-hidden className="h-3 w-3" />
                                        {teks.kunci}
                                    </span>
                                )}
                                {dipilih && <span className="font-medium">{teks.pilihanAnda}</span>}
                                {tkp && <span className="text-redup">{teks.skorOpsi(opsi.skor)}</span>}
                            </span>
                        </li>
                    );
                })}
            </ul>

            <div className="flex flex-col gap-1 border-l-2 border-garis-input pl-3 text-sm">
                <p className="font-medium">{teks.pembahasan}</p>
                <p className="leading-relaxed whitespace-pre-line text-redup">{soal.pembahasan}</p>
                <p className="text-xs text-redup">
                    {teks.sumber}:{' '}
                    {soal.sumberUrl ? (
                        <a href={soal.sumberUrl} target="_blank" rel="noopener noreferrer" className="underline underline-offset-4">
                            {soal.sumberNama}
                        </a>
                    ) : (
                        soal.sumberNama
                    )}{' '}
                    · {teks.lisensi}: {soal.lisensi}
                </p>
            </div>
        </article>
    );
}
