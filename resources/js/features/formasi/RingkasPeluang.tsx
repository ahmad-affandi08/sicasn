import { LencanaPersaingan } from '@/features/peluang/LencanaPersaingan';
import { teksFormasi } from '@/lang/formasi';
import { formatAngka, formatHari, formatRasio } from '@/lib/format';
import type { PeluangFormasiItem } from '@/types';

export function RingkasPeluang({ peluang }: { peluang: PeluangFormasiItem | null }) {
    const teks = teksFormasi.peluang;

    if (peluang === null) {
        return (
            <div className="flex flex-col gap-2 rounded-xl bg-latar p-3 text-sm">
                <LencanaPersaingan tingkat="belum_ada_data" />
                <p className="text-redup">{teks.belumAda}</p>
            </div>
        );
    }

    const sementara = peluang.statusAngka === 'sementara';
    const pelamar = peluang.pelamar ?? peluang.submit;
    const tampilPelamar = pelamar === null ? '-' : `${formatAngka(pelamar, sementara || peluang.pelamar === null)}${peluang.pelamar === null ? ` ${teks.submit}` : ''}`;

    return (
        <div className="flex flex-col gap-3 rounded-xl bg-latar p-3 text-sm">
            <div className="flex flex-wrap items-center gap-2">
                <LencanaPersaingan tingkat={peluang.tingkat} />
                <span className="text-xs text-redup">{teks.judul}</span>
            </div>
            <dl className="grid grid-cols-2 gap-3">
                <div>
                    <dt className="text-redup">{teks.pelamar}</dt>
                    <dd className="font-medium">{tampilPelamar}</dd>
                </div>
                <div>
                    <dt className="text-redup">{teks.rasio}</dt>
                    <dd className="font-medium">{formatRasio(peluang.rasio, peluang.batasBawah)}</dd>
                </div>
            </dl>
            <div className="flex flex-col gap-0.5 text-xs text-redup">
                {!peluang.perJabatan && <p className="font-medium">{teks.bukanPerJabatan}</p>}
                {peluang.formasiDariTanggal && <p>{teks.formasiDari(formatHari(peluang.formasiDariTanggal))}</p>}
                <p>
                    {teks.tingkatData(peluang.tingkatData)} · {peluang.sumberNama} · {formatHari(peluang.tanggal)}
                </p>
                <p>{teks.bukanJaminan}</p>
            </div>
        </div>
    );
}
