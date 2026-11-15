import { Link } from '@inertiajs/react';
import { LuCircleCheck, LuCircleMinus } from 'react-icons/lu';

import { Badge } from '@/components/ui/badge';
import type { ItemRiwayat, KategoriSoal } from '@/features/latihan/tipe';
import { teksLatihan } from '@/lang/latihan';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';

const URUTAN: KategoriSoal[] = ['twk', 'tiu', 'tkp'];

export function RiwayatLatihan({ riwayat }: { riwayat: ItemRiwayat[] }) {
    const teks = teksLatihan.riwayat;

    if (riwayat.length === 0) {
        return <p className="text-sm text-redup">{teks.kosong}</p>;
    }

    return (
        <ul aria-label={teks.judul} className="divide-y divide-garis border-y border-garis">
            {riwayat.map((item) => (
                <li key={item.id} className="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex flex-col gap-0.5">
                        <p className="font-medium">{item.modeLabel}</p>
                        <p className="text-sm text-redup tabular-nums">
                            {formatWaktu(item.selesai)} · {URUTAN.filter((k) => item.skor[k] !== undefined && (item.mode === 'simulasi' || item.mode === k)).map((k) => teks.skor(teksLatihan.kategori[k], item.skor[k] ?? 0)).join(' · ')}
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        {item.lulus !== null && (
                            <Badge variant={item.lulus ? 'sukses' : 'netral'}>
                                {item.lulus ? <LuCircleCheck aria-hidden className="h-3.5 w-3.5" /> : <LuCircleMinus aria-hidden className="h-3.5 w-3.5" />}
                                {item.lulus ? teks.lulus : teks.belumLulus}
                            </Badge>
                        )}
                        <Link href={rute.hasilLatihan(item.id)} className="text-sm font-medium text-primer underline-offset-4 hover:underline">
                            {teks.lihat}
                        </Link>
                    </div>
                </li>
            ))}
        </ul>
    );
}
