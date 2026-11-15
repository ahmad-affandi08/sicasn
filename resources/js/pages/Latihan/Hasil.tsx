import { Head, Link, router } from '@inertiajs/react';

import { Catatan } from '@/components/Catatan';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { DaftarPembahasan } from '@/components/latihan/DaftarPembahasan';
import { TabelSkor } from '@/components/latihan/TabelSkor';
import { Button } from '@/components/ui/button';
import type { HasilLatihan } from '@/features/latihan/tipe';
import { teksLatihan } from '@/lang/latihan';
import { LayoutMember } from '@/layouts/LayoutMember';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';

export default function Hasil({ hasil }: { hasil: HasilLatihan }) {
    const teks = teksLatihan.hasil;
    const belumTercapai = hasil.kategori.filter((butir) => !butir.tercapai).map((butir) => teksLatihan.kategori[butir.kategori]);

    return (
        <LayoutMember>
            <Head title={teks.judul} />
            <HeaderHalaman
                judul={hasil.modeLabel}
                sub={`${teks.waktuSelesai} ${formatWaktu(hasil.selesai)}`}
                aksi={
                    <>
                        <Button variant="outline" asChild>
                            <Link href={rute.latihan}>{teks.riwayat}</Link>
                        </Button>
                        <Button onClick={() => router.post(rute.latihan, { mode: hasil.mode })}>{teks.latihanLagi}</Button>
                    </>
                }
            />

            <div className="mb-6 flex flex-col gap-1">
                <p className="text-sm text-redup">{teks.total}</p>
                <p className="text-4xl leading-none font-medium tabular-nums">
                    {hasil.total.toLocaleString('id-ID')} <span className="text-base font-normal text-redup">{teks.dari(hasil.totalMaks)}</span>
                </p>
                {hasil.lulus !== null && <p className="mt-1 font-medium">{hasil.lulus ? teks.lulus : teks.belumLulus(belumTercapai.join(', '))}</p>}
            </div>

            <TabelSkor kategori={hasil.kategori} />
            <Catatan className="mt-4">
                {teks.setara} {teks.disclaimer}
            </Catatan>

            <DaftarPembahasan soal={hasil.soal} />
        </LayoutMember>
    );
}
