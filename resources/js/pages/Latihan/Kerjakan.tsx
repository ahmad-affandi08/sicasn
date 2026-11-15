import { Head } from '@inertiajs/react';

import { AreaSoal } from '@/components/latihan/AreaSoal';
import { PenghitungWaktu } from '@/components/latihan/PenghitungWaktu';
import { PetaSoal } from '@/components/latihan/PetaSoal';
import { Button } from '@/components/ui/button';
import type { PercobaanBerjalan } from '@/features/latihan/tipe';
import { useSesiLatihan } from '@/features/latihan/useSesiLatihan';
import { teksLatihan } from '@/lang/latihan';
import { LayoutMember } from '@/layouts/LayoutMember';

export default function Kerjakan({ percobaan }: { percobaan: PercobaanBerjalan }) {
    const teks = teksLatihan.kerjakan;
    const sesi = useSesiLatihan(percobaan);
    const total = percobaan.soal.length;
    const soal = percobaan.soal[sesi.indeks] ?? percobaan.soal[0];

    if (!soal) {
        return null;
    }

    const jawaban = sesi.jawaban[soal.soalId] ?? { opsiId: null, ragu: false };
    const terakhir = sesi.indeks === total - 1;

    return (
        <LayoutMember lebar>
            <Head title={percobaan.modeLabel} />

            <div className="sticky top-14 z-30 -mx-4 mb-6 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-b border-garis bg-latar/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
                <div className="flex flex-col">
                    <h1 className="text-base font-medium">{percobaan.modeLabel}</h1>
                    <p className="text-xs text-redup" aria-live="polite">
                        {teks.ringkasan(sesi.ringkasan.dijawab, total)} · {sesi.menyimpan > 0 ? teks.menyimpan : teks.tersimpan}
                    </p>
                </div>
                <div className="flex items-center gap-4">
                    <PenghitungWaktu sisaDetik={sesi.sisaDetik} />
                    <Button variant="outline" onClick={() => void sesi.konfirmasiSelesai()}>
                        {teks.selesai}
                    </Button>
                </div>
            </div>

            <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div className="flex flex-col gap-6">
                    <AreaSoal
                        key={soal.soalId}
                        soal={soal}
                        nomor={sesi.indeks + 1}
                        total={total}
                        jawaban={jawaban}
                        onPilih={(opsiId) => sesi.ubah(soal.soalId, { opsiId })}
                        onRagu={(ragu) => sesi.ubah(soal.soalId, { ragu })}
                        onKosongkan={() => sesi.ubah(soal.soalId, { opsiId: null })}
                    />
                    <div className="flex justify-between gap-3 border-t border-garis pt-4">
                        <Button variant="outline" disabled={sesi.indeks === 0} onClick={() => sesi.setIndeks(sesi.indeks - 1)}>
                            {teks.sebelumnya}
                        </Button>
                        {terakhir ? (
                            <Button onClick={() => void sesi.konfirmasiSelesai()}>{teks.selesai}</Button>
                        ) : (
                            <Button onClick={() => sesi.setIndeks(sesi.indeks + 1)}>{teks.berikutnya}</Button>
                        )}
                    </div>
                </div>

                <aside className="lg:sticky lg:top-40 lg:self-start">
                    <PetaSoal soal={percobaan.soal} jawaban={sesi.jawaban} indeks={sesi.indeks} onPilih={sesi.setIndeks} />
                </aside>
            </div>
        </LayoutMember>
    );
}
