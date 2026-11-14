import { LuBookmarkCheck, LuChartColumn, LuListFilter } from 'react-icons/lu';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { teks } from '@/lang/id';

const ikon = [LuListFilter, LuChartColumn, LuBookmarkCheck];

export function Manfaat() {
    return (
        <section id="manfaat" className="scroll-mt-20 border-y border-garis bg-kartu">
            <div className="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 md:py-20">
                <div className="mx-auto mb-10 flex max-w-2xl flex-col gap-3 text-center">
                    <h2 className="text-2xl font-bold sm:text-3xl">{teks.manfaat.judul}</h2>
                    <p className="text-redup">{teks.manfaat.sub}</p>
                </div>
                <div className="grid gap-5 md:grid-cols-3">
                    {teks.manfaat.daftar.map((item, indeks) => {
                        const Ikon = ikon[indeks] ?? LuListFilter;

                        return (
                            <Card key={item.judul} className="bg-latar shadow-none">
                                <CardHeader>
                                    <span className="mb-2 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primer-lembut text-primer">
                                        <Ikon aria-hidden className="h-6 w-6" />
                                    </span>
                                    <CardTitle>{item.judul}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-redup">{item.isi}</p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
