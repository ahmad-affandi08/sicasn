import { router } from '@inertiajs/react';
import { LuDownload, LuUndo2 } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { Button } from '@/components/ui/button';
import { teksAdmin } from '@/lang/admin';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { RiwayatStatistikItem } from '@/types';

import { LencanaImpor } from './LencanaImpor';

function Angka({ label, nilai }: { label: string; nilai: number | undefined }) {
    return (
        <div>
            <dt className="text-redup">{label}</dt>
            <dd className="font-medium">{(nilai ?? 0).toLocaleString('id-ID')}</dd>
        </div>
    );
}

export function ButirRiwayat({ impor }: { impor: RiwayatStatistikItem }) {
    const teks = teksAdmin.statistik;
    const dialog = useDialog();

    async function batalkan() {
        const setuju = await dialog.konfirmasi({ judul: teks.konfirmasiRollback.judul, pesan: teks.konfirmasiRollback.pesan, teksTombol: teks.konfirmasiRollback.tombol, berisiko: true });

        if (setuju) {
            router.post(rute.admin.rollbackStatistik(impor.id), {}, { preserveScroll: true });
        }
    }

    return (
        <li className="flex flex-col gap-3 rounded-lg border border-garis p-4">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div className="min-w-0">
                    <p className="font-medium break-all">{impor.berkas ?? '-'}</p>
                    <p className="text-sm text-redup">
                        {teks.riwayat.mulai} {formatWaktu(impor.mulaiAt)}
                    </p>
                </div>
                <LencanaImpor status={impor.status} />
            </div>
            {impor.ringkasan.galat && <p className="text-sm text-bahaya">{impor.ringkasan.galat}</p>}
            <dl className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                <Angka label={teks.riwayat.baru} nilai={impor.ringkasan.baris_baru} />
                <Angka label={teks.riwayat.duplikat} nilai={impor.ringkasan.baris_duplikat} />
                <Angka label={teks.riwayat.gagal} nilai={impor.ringkasan.baris_gagal} />
                {impor.status === 'dibatalkan' && <Angka label={teks.riwayat.dihapus} nilai={impor.ringkasan.baris_dihapus} />}
            </dl>
            <div className="flex flex-col gap-2 sm:flex-row">
                {(impor.ringkasan.baris_gagal ?? 0) > 0 && (
                    <Button asChild variant="outline">
                        <a href={rute.admin.galatStatistik(impor.id)}>
                            <LuDownload aria-hidden className="h-4 w-4" />
                            {teks.riwayat.unduhGalat}
                        </a>
                    </Button>
                )}
                {impor.bisaDibatalkan && (
                    <Button variant="outline" onClick={batalkan}>
                        <LuUndo2 aria-hidden className="h-4 w-4" />
                        {teks.riwayat.rollback}
                    </Button>
                )}
            </div>
        </li>
    );
}
