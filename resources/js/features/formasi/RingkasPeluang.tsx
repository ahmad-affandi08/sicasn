import { LencanaPersaingan } from '@/features/peluang/LencanaPersaingan';
import { MeterRasio } from '@/features/peluang/MeterRasio';
import { teksFormasi } from '@/lang/formasi';
import { formatAngka, formatHari, formatRasio } from '@/lib/format';
import type { PeluangFormasiItem } from '@/types';

interface PropsRingkasPeluang {
    peluang: PeluangFormasiItem | null;
    ambang?: { longgarMaks: number; ketatMin: number };
    lengkap?: boolean;
}

export function RingkasPeluang({ peluang, ambang, lengkap = true }: PropsRingkasPeluang) {
    const teks = teksFormasi.peluang;

    if (peluang === null) {
        return (
            <div className="flex flex-col gap-1.5 text-sm">
                <div>
                    <LencanaPersaingan tingkat="belum_ada_data" />
                </div>
                <p className="text-xs text-redup">{teks.belumAda}</p>
            </div>
        );
    }

    const sementara = peluang.statusAngka === 'sementara';
    const pelamar = peluang.pelamar ?? peluang.submit;
    const tampilPelamar = pelamar === null ? '-' : `${formatAngka(pelamar, sementara || peluang.pelamar === null)}${peluang.pelamar === null ? ` ${teks.submit}` : ''}`;

    return (
        <div className="flex flex-col gap-2.5 text-sm">
            <div className="flex items-center justify-between gap-2">
                <LencanaPersaingan tingkat={peluang.tingkat} />
                <span className="text-xs text-redup">{teks.judul}</span>
            </div>
            <dl className="grid grid-cols-2 gap-3">
                <div>
                    <dt className="text-xs text-redup">{teks.rasio}</dt>
                    <dd className="text-lg leading-tight font-medium">{formatRasio(peluang.rasio, peluang.batasBawah)}</dd>
                </div>
                <div>
                    <dt className="text-xs text-redup">{teks.pelamar}</dt>
                    <dd className="text-lg leading-tight font-medium">{tampilPelamar}</dd>
                </div>
            </dl>
            {ambang && <MeterRasio rasio={peluang.rasio} tingkat={peluang.tingkat} batasBawah={peluang.batasBawah} longgarMaks={ambang.longgarMaks} ketatMin={ambang.ketatMin} />}
            <div className="flex flex-col gap-0.5 text-xs text-redup">
                {!peluang.perJabatan && <p className="font-medium text-teks">{teks.bukanPerJabatan}</p>}
                {peluang.formasiDariTanggal && <p>{teks.formasiDari(formatHari(peluang.formasiDariTanggal))}</p>}
                <p>
                    {teks.tingkatData(peluang.tingkatData)} · {peluang.sumberNama} · {formatHari(peluang.tanggal)}
                </p>
                {lengkap && <p>{teks.bukanJaminan}</p>}
            </div>
        </div>
    );
}
