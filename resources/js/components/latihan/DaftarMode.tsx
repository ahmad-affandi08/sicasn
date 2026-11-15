import { router } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { durasiMenit, kategoriDibutuhkan, ringkasKomposisi } from '@/features/latihan/format';
import type { InfoMode, KategoriSoal } from '@/features/latihan/tipe';
import { teksLatihan } from '@/lang/latihan';
import { rute } from '@/lib/rute';

interface PropsDaftarMode {
    mode: InfoMode[];
    ketersediaan: Record<KategoriSoal, number>;
    terkunci: boolean;
}

function deskripsi(info: InfoMode): string {
    const menit = durasiMenit(info);
    const jumlah = ringkasKomposisi(info);

    if (info.nilai === 'simulasi') {
        return teksLatihan.mode.simulasi.deskripsi(jumlah, menit);
    }

    return teksLatihan.mode[info.nilai].deskripsi(Object.values(info.komposisi)[0] ?? 0, menit);
}

export function DaftarMode({ mode, ketersediaan, terkunci }: PropsDaftarMode) {
    return (
        <ul className="divide-y divide-garis rounded-kartu border border-garis bg-kartu">
            {mode.map((info) => {
                const siap = kategoriDibutuhkan(info).every((kategori) => ketersediaan[kategori] > 0);

                return (
                    <li key={info.nilai} className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex flex-col gap-0.5">
                            <h3 className="font-medium">{teksLatihan.mode[info.nilai].judul}</h3>
                            <p className="text-sm text-redup">{deskripsi(info)}</p>
                        </div>
                        <Button
                            variant={info.nilai === 'simulasi' ? 'default' : 'outline'}
                            disabled={!siap || terkunci}
                            onClick={() => router.post(rute.latihan, { mode: info.nilai })}
                            className="self-start sm:self-center"
                        >
                            {siap ? teksLatihan.mulai : teksLatihan.tidakTersedia}
                        </Button>
                    </li>
                );
            })}
        </ul>
    );
}
