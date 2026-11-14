import type { ComponentType } from 'react';

import {
    IlustrasiLangkahChat,
    IlustrasiLangkahEmail,
    IlustrasiLangkahMasuk,
    IlustrasiLangkahTransfer,
    type PropsIlustrasi,
} from '@/components/ilustrasi';
import { teks } from '@/lang/id';

const ilustrasi: ComponentType<PropsIlustrasi>[] = [
    IlustrasiLangkahChat,
    IlustrasiLangkahTransfer,
    IlustrasiLangkahEmail,
    IlustrasiLangkahMasuk,
];

export function CaraAkses() {
    return (
        <section id="cara-akses" className="mx-auto w-full max-w-6xl scroll-mt-20 px-4 py-14 sm:px-6 md:py-20">
            <div className="mx-auto mb-10 flex max-w-2xl flex-col gap-3 text-center">
                <h2 className="text-2xl font-bold sm:text-3xl">{teks.caraAkses.judul}</h2>
                <p className="text-redup">{teks.caraAkses.sub}</p>
            </div>
            <ol className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {teks.caraAkses.langkah.map((langkah, indeks) => {
                    const Ilustrasi = ilustrasi[indeks] ?? IlustrasiLangkahChat;

                    return (
                        <li key={langkah.judul} className="flex flex-col items-center gap-3 rounded-kartu border border-garis bg-kartu p-6 text-center">
                            <Ilustrasi className="h-24 w-24" judul={`${teks.caraAkses.ilustrasiLangkah} ${indeks + 1}`} />
                            <span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-primer text-sm font-bold text-primer-teks">
                                {indeks + 1}
                            </span>
                            <h3 className="text-lg font-medium">{langkah.judul}</h3>
                            <p className="text-sm text-redup">{langkah.isi}</p>
                        </li>
                    );
                })}
            </ol>
        </section>
    );
}
