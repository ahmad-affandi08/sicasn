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
        <section id="cara-akses" className="scroll-mt-16 border-t border-garis">
            <div className="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 md:py-20">
                <div className="mb-10 flex max-w-2xl flex-col gap-2">
                    <h2 className="text-2xl font-medium sm:text-3xl">{teks.caraAkses.judul}</h2>
                    <p className="text-redup">{teks.caraAkses.sub}</p>
                </div>
                <ol className="grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                    {teks.caraAkses.langkah.map((langkah, indeks) => {
                        const Ilustrasi = ilustrasi[indeks] ?? IlustrasiLangkahChat;

                        return (
                            <li key={langkah.judul} className="flex flex-col gap-3 border-t-2 border-teks pt-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium text-redup">{String(indeks + 1).padStart(2, '0')}</span>
                                    <Ilustrasi className="h-14 w-14" judul={`${teks.caraAkses.ilustrasiLangkah} ${indeks + 1}`} />
                                </div>
                                <h3 className="text-lg font-medium">{langkah.judul}</h3>
                                <p className="text-sm text-redup">{langkah.isi}</p>
                            </li>
                        );
                    })}
                </ol>
            </div>
        </section>
    );
}
