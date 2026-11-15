import { Alert } from '@/components/ui/alert';
import { teksAdmin } from '@/lang/admin';
import type { BatchItem } from '@/types';

export function RingkasanBatch({ batch }: { batch: BatchItem }) {
    const teks = teksAdmin.sinkron;
    const r = batch.ringkasan;

    if (r.galat) {
        return <p className="text-sm break-words text-bahaya">{r.galat}</p>;
    }

    if (r.dilewati) {
        return <p className="text-sm text-redup">{teks.dilewati}</p>;
    }

    const angka = [
        [teks.statistik.barisAktif, r.baris_aktif ?? 0],
        [teks.statistik.baru, r.baru ?? 0],
        [teks.statistik.diubah, r.diubah ?? 0],
        [teks.statistik.ditarik, r.ditarik ?? 0],
        [teks.statistik.diabaikan, r.diabaikan ?? 0],
        [teks.statistik.gagal, r.gagal_baris ?? 0],
    ] as const;

    return (
        <div className="flex flex-col gap-3">
            <dl className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                {angka.map(([label, nilai]) => (
                    <div key={label}>
                        <dt className="text-xs text-redup">{label}</dt>
                        <dd className="text-xl font-medium">{nilai.toLocaleString('id-ID')}</dd>
                    </div>
                ))}
            </dl>
            {typeof r.durasi_detik === 'number' && <p className="text-xs text-redup">{teks.durasi(r.durasi_detik)}</p>}
            {r.penarikan_ditunda && <Alert variant="peringatan">{teks.penarikanDitunda}</Alert>}
        </div>
    );
}
