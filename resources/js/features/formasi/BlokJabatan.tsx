import { LencanaPersaingan } from '@/features/peluang/LencanaPersaingan';
import { MeterRasio } from '@/features/peluang/MeterRasio';
import { teksFormasi } from '@/lang/formasi';
import { formatAngka, formatHari, formatRasio } from '@/lib/format';
import type { PeluangJabatanItem } from '@/types';

interface PropsBlokJabatan {
    jabatan: PeluangJabatanItem;
    ambang?: { longgarMaks: number; ketatMin: number };
}

export function BlokJabatan({ jabatan, ambang }: PropsBlokJabatan) {
    const teks = teksFormasi.peluang;
    const dasarPelamar = jabatan.pelamar ?? jabatan.submit;

    return (
        <div className="flex flex-col gap-2.5 text-sm">
            <div className="flex items-center justify-between gap-2">
                {jabatan.rasio !== null ? <LencanaPersaingan tingkat={jabatan.tingkat} /> : <span />}
                <span className="text-xs font-medium text-primer">{teks.jabatan.judul}</span>
            </div>
            <dl className="grid grid-cols-2 gap-3">
                {jabatan.lolosAdministrasi !== null && (
                    <div>
                        <dt className="text-xs text-redup">{teks.jabatan.lolos}</dt>
                        <dd className="text-lg leading-tight font-medium">{formatAngka(jabatan.lolosAdministrasi)}</dd>
                    </div>
                )}
                {dasarPelamar !== null && (
                    <div>
                        <dt className="text-xs text-redup">{teks.pelamar}</dt>
                        <dd className="text-lg leading-tight font-medium">{formatAngka(dasarPelamar, jabatan.statusAngka === 'sementara' || jabatan.pelamar === null)}</dd>
                    </div>
                )}
                {jabatan.formasi !== null && (
                    <div>
                        <dt className="text-xs text-redup">{teks.jabatan.formasi}</dt>
                        <dd className="text-lg leading-tight font-medium">{formatAngka(jabatan.formasi)}</dd>
                    </div>
                )}
                {jabatan.rasio !== null && (
                    <div>
                        <dt className="text-xs text-redup">{teks.rasio}</dt>
                        <dd className="text-lg leading-tight font-medium">{formatRasio(jabatan.rasio, jabatan.batasBawah)}</dd>
                    </div>
                )}
            </dl>
            {jabatan.rasio !== null && ambang && <MeterRasio rasio={jabatan.rasio} tingkat={jabatan.tingkat} batasBawah={jabatan.batasBawah} longgarMaks={ambang.longgarMaks} ketatMin={ambang.ketatMin} />}
            {jabatan.rasio === null && <p className="text-xs text-redup">{teks.jabatan.rasioTakAda}</p>}
            <div className="flex flex-col gap-0.5 text-xs text-redup">
                {jabatan.lolosAdministrasi !== null && <p>{teks.jabatan.lolosCatatan}</p>}
                {jabatan.lokasi && <p>{teks.jabatan.lokasi(jabatan.lokasi)}</p>}
                <p>
                    {teks.tingkatData(jabatan.tingkatData)} · {jabatan.sumberNama} · {formatHari(jabatan.tanggal)}
                </p>
            </div>
        </div>
    );
}
