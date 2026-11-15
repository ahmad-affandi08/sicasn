import { MeterRasio } from '@/features/peluang/MeterRasio';
import { teks } from '@/lang/id';

const contoh = [
    { rasio: 2.4, tingkat: 'longgar' },
    { rasio: 12, tingkat: 'sedang' },
    { rasio: 41, tingkat: 'ketat' },
] as const;

export function DataTelusur() {
    const skala = teks.data.skala;

    return (
        <section aria-labelledby="judul-data" className="border-t border-garis bg-permukaan">
            <div className="mx-auto grid w-full max-w-6xl gap-10 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-2 lg:gap-16">
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-2">
                        <h2 id="judul-data" className="text-2xl font-medium sm:text-3xl">
                            {teks.data.judul}
                        </h2>
                        <p className="max-w-lg text-redup">{teks.data.sub}</p>
                    </div>
                    <dl className="flex flex-col gap-5">
                        {teks.data.poin.map((butir) => (
                            <div key={butir.judul} className="flex flex-col gap-0.5">
                                <dt className="font-medium">{butir.judul}</dt>
                                <dd className="max-w-lg text-sm text-redup">{butir.isi}</dd>
                            </div>
                        ))}
                    </dl>
                </div>
                <div className="flex flex-col gap-5 self-start rounded-kartu border border-garis bg-kartu p-5 sm:p-6">
                    <div className="flex flex-col gap-1">
                        <h3 className="font-medium">{skala.judul}</h3>
                        <p className="text-sm text-redup">{skala.isi}</p>
                    </div>
                    <ul aria-label={skala.contoh} className="flex flex-col gap-4">
                        {contoh.map((butir, indeks) => (
                            <li key={butir.tingkat} className="grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-1.5 sm:grid-cols-[14rem_1fr]">
                                <span className="text-sm">{skala.tingkat[indeks]}</span>
                                <MeterRasio rasio={butir.rasio} tingkat={butir.tingkat} longgarMaks={5} ketatMin={20} className="col-span-2 sm:col-span-1" />
                            </li>
                        ))}
                    </ul>
                    <p className="text-xs text-redup">{skala.catatan}</p>
                </div>
            </div>
        </section>
    );
}
