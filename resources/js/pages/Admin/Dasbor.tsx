import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';
import { LuCircleAlert, LuCircleCheck, LuUserPlus } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { BatchItem, EventItem } from '@/types';

interface PropsHalaman {
    ringkasan: { aktif: number; nonaktif: number };
    kesiapan: { email: boolean; whatsapp: boolean; pembayaran: boolean; sinkron: boolean };
    sinkron: { terakhir: BatchItem | null; formasiAktif: number };
    eventBaru: EventItem[];
}

export default function Dasbor({ ringkasan, kesiapan, sinkron, eventBaru }: PropsHalaman) {
    const teks = teksAdmin.dasbor;
    const dialog = useDialog();

    useEffect(() => {
        if (eventBaru.length === 0) {
            return;
        }

        void dialog
            .info({
                judul: teksAdmin.dasbor.eventBaru.judul,
                pesan: teksAdmin.dasbor.eventBaru.pesan(eventBaru.map((butir) => butir.nama).join(', ')),
                teksTombol: teksAdmin.dasbor.eventBaru.tombol,
            })
            .then(() => router.post(rute.admin.eventDiberitahukan, {}, { preserveScroll: true }));
    }, [eventBaru, dialog]);

    const butir = [
        { kunci: 'email', siap: kesiapan.email, tujuan: rute.admin.email, judul: teks.butir.email.judul, keterangan: teks.butir.email.siap, belum: teks.butir.email.belum },
        { kunci: 'whatsapp', siap: kesiapan.whatsapp, tujuan: rute.admin.whatsapp, judul: teks.butir.whatsapp.judul, keterangan: teks.butir.whatsapp.siap, belum: teks.butir.whatsapp.belum },
        { kunci: 'sinkron', siap: kesiapan.sinkron, tujuan: rute.admin.sinkron, judul: teks.butir.sinkron.judul, keterangan: teks.butir.sinkron.siap, belum: teks.butir.sinkron.belum },
        { kunci: 'pembayaran', siap: kesiapan.pembayaran, tujuan: rute.admin.pembayaran, judul: teks.butir.pembayaran.judul, keterangan: teks.butir.pembayaran.siap, belum: teks.butir.pembayaran.belum },
    ];

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman
                judul={teks.judul}
                sub={teks.sub}
                aksi={
                    <Button asChild>
                        <Link href={rute.admin.buatPengguna}>
                            <LuUserPlus aria-hidden className="h-4 w-4" />
                            {teks.buatAkun}
                        </Link>
                    </Button>
                }
            />

            <dl className="grid grid-cols-2 overflow-hidden rounded-kartu border border-garis bg-kartu">
                <div className="flex flex-col gap-1 p-4">
                    <dt className="text-sm text-redup">{teks.memberAktif}</dt>
                    <dd className="text-3xl leading-none font-medium">{ringkasan.aktif}</dd>
                </div>
                <div className="flex flex-col gap-1 border-l border-garis p-4">
                    <dt className="text-sm text-redup">{teks.memberNonaktif}</dt>
                    <dd className="text-3xl leading-none font-medium">{ringkasan.nonaktif}</dd>
                </div>
            </dl>

            <Card className="mt-4">
                <CardContent className="flex flex-col gap-4 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="font-medium">{teks.kartuSinkron.judul}</p>
                        <p className="text-sm text-redup">
                            {teks.kartuSinkron.terakhir}: {sinkron.terakhir ? formatWaktu(sinkron.terakhir.selesaiAt) : teks.kartuSinkron.belumPernah}
                        </p>
                        <p className="mt-2 text-2xl font-medium">
                            {sinkron.formasiAktif.toLocaleString('id-ID')} <span className="text-sm font-normal text-redup">{teks.kartuSinkron.formasiAktif}</span>
                        </p>
                        {sinkron.formasiAktif === 0 && <p className="mt-1 max-w-xl text-sm text-redup">{teks.kartuSinkron.kosong}</p>}
                    </div>
                    <Button asChild variant="outline" className="self-start sm:self-center">
                        <Link href={rute.admin.sinkron}>{teks.kartuSinkron.buka}</Link>
                    </Button>
                </CardContent>
            </Card>

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>{teks.kesiapan}</CardTitle>
                    <CardDescription>{teks.kesiapanSub}</CardDescription>
                </CardHeader>
                <CardContent>
                    <ul className="flex flex-col divide-y divide-garis">
                        {butir.map((item) => (
                            <li key={item.kunci} className="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-start gap-3">
                                    {item.siap ? (
                                        <LuCircleCheck aria-hidden className="mt-0.5 h-5 w-5 shrink-0 text-sukses" />
                                    ) : (
                                        <LuCircleAlert aria-hidden className="mt-0.5 h-5 w-5 shrink-0 text-peringatan" />
                                    )}
                                    <div>
                                        <p className="flex flex-wrap items-center gap-2 font-medium">
                                            {item.judul}
                                            <Badge variant={item.siap ? 'sukses' : 'peringatan'}>{item.siap ? teks.siap : teks.belum}</Badge>
                                        </p>
                                        <p className="text-sm text-redup">{item.siap ? item.keterangan : item.belum}</p>
                                    </div>
                                </div>
                                <Button asChild variant="outline" size="sm" className="self-start sm:self-center">
                                    <Link href={item.tujuan}>{teks.atur}</Link>
                                </Button>
                            </li>
                        ))}
                    </ul>
                </CardContent>
            </Card>
        </LayoutAdmin>
    );
}
