import { teks } from '@/lang/id';

export function Manfaat() {
    return (
        <section id="manfaat" className="scroll-mt-16 border-t border-garis">
            <div className="mx-auto grid w-full max-w-6xl gap-8 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-[1fr_2fr] lg:gap-16">
                <div className="flex flex-col gap-2 lg:sticky lg:top-24 lg:self-start">
                    <h2 className="text-2xl font-medium sm:text-3xl">{teks.manfaat.judul}</h2>
                    <p className="text-redup">{teks.manfaat.sub}</p>
                </div>
                <ol className="flex flex-col divide-y divide-garis border-y border-garis">
                    {teks.manfaat.daftar.map((item, indeks) => (
                        <li key={item.judul} className="grid gap-2 py-6 sm:grid-cols-[4rem_1fr] sm:gap-6">
                            <span aria-hidden className="text-sm font-medium text-redup">
                                {String(indeks + 1).padStart(2, '0')}
                            </span>
                            <div className="flex flex-col gap-1">
                                <h3 className="text-lg font-medium">{item.judul}</h3>
                                <p className="max-w-xl text-redup">{item.isi}</p>
                            </div>
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}
