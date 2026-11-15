import { LencanaPersaingan } from '@/features/peluang/LencanaPersaingan';
import { MeterRasio } from '@/features/peluang/MeterRasio';
import { teksPantauan } from '@/lang/pantauan';
import { formatAngka, formatHari, formatRasio } from '@/lib/format';
import type { PeluangFormasiItem, PeluangJabatanItem, TingkatPersainganKode } from '@/types';

interface PropsSelPersaingan {
    tingkat: TingkatPersainganKode;
    rasio: number | null;
    batasBawah: boolean;
    pelamar: number | null;
    label: string;
    sumber: string;
    tanggal: string;
    ambang: { longgarMaks: number; ketatMin: number };
}

function SelPersaingan({ tingkat, rasio, batasBawah, pelamar, label, sumber, tanggal, ambang }: PropsSelPersaingan) {
    return (
        <div className="flex flex-col gap-1.5">
            <div className="flex flex-wrap items-center gap-2">
                <LencanaPersaingan tingkat={tingkat} />
                <span className="font-medium">{formatRasio(rasio, batasBawah)}</span>
            </div>
            <MeterRasio rasio={rasio} tingkat={tingkat} batasBawah={batasBawah} longgarMaks={ambang.longgarMaks} ketatMin={ambang.ketatMin} />
            <p className="text-xs text-redup">
                {label}
                {pelamar !== null && ` · ${formatAngka(pelamar, batasBawah)}`}
            </p>
            <p className="text-xs text-redup">
                {sumber} · {formatHari(tanggal)}
            </p>
        </div>
    );
}

export function SelPersainganInstansi({ peluang, ambang }: { peluang: PeluangFormasiItem | null; ambang: { longgarMaks: number; ketatMin: number } }) {
    if (peluang === null) {
        return <span className="text-redup">{teksPantauan.halamanBandingkan.tidakAda}</span>;
    }

    return (
        <SelPersaingan
            tingkat={peluang.tingkat}
            rasio={peluang.rasio}
            batasBawah={peluang.batasBawah}
            pelamar={peluang.pelamar ?? peluang.submit}
            label={teksPantauan.halamanBandingkan.ringkasanInstansi}
            sumber={peluang.sumberNama}
            tanggal={peluang.tanggal}
            ambang={ambang}
        />
    );
}

export function SelPersainganJabatan({ jabatan, ambang }: { jabatan: PeluangJabatanItem | null; ambang: { longgarMaks: number; ketatMin: number } }) {
    if (jabatan === null) {
        return <span className="text-redup">{teksPantauan.halamanBandingkan.tidakAdaJabatan}</span>;
    }

    return (
        <SelPersaingan
            tingkat={jabatan.tingkat}
            rasio={jabatan.rasio}
            batasBawah={jabatan.batasBawah}
            pelamar={jabatan.lolosAdministrasi ?? jabatan.pelamar ?? jabatan.submit}
            label={jabatan.lolosAdministrasi !== null ? 'Lolos administrasi' : 'Pelamar'}
            sumber={jabatan.sumberNama}
            tanggal={jabatan.tanggal}
            ambang={ambang}
        />
    );
}
