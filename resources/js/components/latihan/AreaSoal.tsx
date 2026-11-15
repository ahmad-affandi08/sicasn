import { teksLatihan } from '@/lang/latihan';
import { cn } from '@/lib/utils';
import type { JawabanSoal, SoalDikerjakan } from '@/features/latihan/tipe';

interface PropsAreaSoal {
    soal: SoalDikerjakan;
    nomor: number;
    total: number;
    jawaban: JawabanSoal;
    onPilih: (opsiId: number) => void;
    onRagu: (ragu: boolean) => void;
    onKosongkan: () => void;
}

export function AreaSoal({ soal, nomor, total, jawaban, onPilih, onRagu, onKosongkan }: PropsAreaSoal) {
    const teks = teksLatihan.kerjakan;

    return (
        <section aria-labelledby="judul-soal" className="flex flex-col gap-5">
            <header className="flex flex-col gap-1">
                <p className="text-sm text-redup">
                    <span className="font-medium text-teks">{teksLatihan.kategori[soal.kategori]}</span> · {soal.subkategori}
                </p>
                <h2 id="judul-soal" className="text-sm font-medium text-redup tabular-nums">
                    {teks.soalKe(nomor, total)}
                </h2>
            </header>

            <p className="text-base leading-relaxed whitespace-pre-line sm:text-lg">{soal.pertanyaan}</p>

            <fieldset className="flex flex-col gap-2">
                <legend className="sr-only">{teks.opsi}</legend>
                {soal.opsi.map((opsi) => {
                    const dipilih = jawaban.opsiId === opsi.id;

                    return (
                        <label
                            key={opsi.id}
                            className={cn(
                                'flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border px-3 py-2.5 text-base transition-colors has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primer',
                                dipilih ? 'border-primer bg-primer-lembut' : 'border-garis hover:bg-permukaan',
                            )}
                        >
                            <input
                                type="radio"
                                name={`soal-${soal.soalId}`}
                                value={opsi.id}
                                checked={dipilih}
                                onChange={() => onPilih(opsi.id)}
                                className="mt-1 h-4 w-4 shrink-0 accent-primer"
                            />
                            <span>
                                <span className="font-medium">{opsi.label}.</span> <span className="whitespace-pre-line">{opsi.teks}</span>
                            </span>
                        </label>
                    );
                })}
            </fieldset>

            <div className="flex flex-wrap items-center gap-x-6 gap-y-2">
                <label className="flex min-h-11 cursor-pointer items-center gap-2 text-sm">
                    <input type="checkbox" checked={jawaban.ragu} onChange={(kejadian) => onRagu(kejadian.target.checked)} className="h-4 w-4 accent-primer" />
                    {teks.ragu}
                </label>
                {jawaban.opsiId !== null && (
                    <button type="button" onClick={onKosongkan} className="min-h-11 text-sm text-redup underline underline-offset-4 hover:text-teks">
                        {teks.kosongkan}
                    </button>
                )}
            </div>
        </section>
    );
}
