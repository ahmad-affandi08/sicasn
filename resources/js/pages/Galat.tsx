import { Head, Link, usePage } from '@inertiajs/react';

import { IlustrasiGalat, IlustrasiNotFound, IlustrasiPeringatan, IlustrasiServerError } from '@/components/ilustrasi';
import { Button } from '@/components/ui/button';
import { teksSistem } from '@/lang/sistem';
import { LayoutPublik } from '@/layouts/LayoutPublik';
import { rute } from '@/lib/rute';
import type { PropsBersama } from '@/types';

type KodeGalat = keyof typeof teksSistem.galat.halaman;

interface PropsHalaman {
    kode: KodeGalat;
}

function IlustrasiKode({ kode }: PropsHalaman) {
    const kelas = 'h-40 w-52';

    if (kode === 404) {
        return <IlustrasiNotFound className={kelas} />;
    }

    if (kode === 500 || kode === 503) {
        return <IlustrasiServerError className={kelas} />;
    }

    return kode === 403 ? <IlustrasiGalat className={kelas} /> : <IlustrasiPeringatan className={kelas} />;
}

export default function Galat({ kode }: PropsHalaman) {
    const { auth } = usePage<PropsBersama>().props;
    const isi = teksSistem.galat.halaman[kode];
    const tujuan = auth.pengguna ? (auth.pengguna.peran === 'admin' ? rute.admin.dasbor : rute.peluangInstansi) : rute.beranda;

    return (
        <LayoutPublik>
            <Head title={isi.judul} />
            <div className="mx-auto flex max-w-lg flex-col items-center gap-4 px-4 py-16 text-center">
                <IlustrasiKode kode={kode} />
                {kode !== 404 && <p className="text-sm font-medium text-redup tabular-nums">{kode}</p>}
                <h1 className="text-2xl font-medium tracking-tight">{isi.judul}</h1>
                <p className="text-redup">{isi.pesan}</p>
                <Button asChild>
                    <Link href={tujuan}>{auth.pengguna ? teksSistem.galat.kembaliAkun : teksSistem.galat.kembali}</Link>
                </Button>
            </div>
        </LayoutPublik>
    );
}
