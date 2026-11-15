import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

import { useDialog } from '@/components/dialog/konteks';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { formatHari } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { PratinjauStatistikItem } from '@/types';

export default function Pratinjau({ pratinjau }: { pratinjau: PratinjauStatistikItem }) {
    const teks = teksAdmin.statistik.pratinjau;
    const dialog = useDialog();
    const [memproses, setMemproses] = useState(false);

    async function konfirmasi() {
        if (pratinjau.token === null) {
            return;
        }

        const setuju = await dialog.konfirmasi({
            judul: teksAdmin.statistik.konfirmasiImpor.judul,
            pesan: teksAdmin.statistik.konfirmasiImpor.pesan(pratinjau.barisValid, pratinjau.jumlahGalat),
            teksTombol: teksAdmin.statistik.konfirmasiImpor.tombol,
        });

        if (setuju) {
            router.post(rute.admin.konfirmasiStatistik(pratinjau.token), {}, { onStart: () => setMemproses(true), onFinish: () => setMemproses(false) });
        }
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub(pratinjau.namaBerkas)} />

            <div className="flex flex-col gap-6">
                <div className="grid gap-4 sm:grid-cols-3">
                    {[
                        { label: teks.total, nilai: pratinjau.totalBaris },
                        { label: teks.valid, nilai: pratinjau.barisValid },
                        { label: teks.galat, nilai: pratinjau.jumlahGalat },
                    ].map((butir) => (
                        <Card key={butir.label}>
                            <CardContent className="flex flex-col gap-1 pt-6">
                                <p className="text-3xl font-bold">{butir.nilai.toLocaleString('id-ID')}</p>
                                <p className="text-sm text-redup">{butir.label}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {pratinjau.barisValid === 0 && <Alert variant="bahaya">{teks.tidakAdaValid}</Alert>}

                {pratinjau.ringkasanSumber.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{teks.sumber}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead>{teks.kolom.sumber}</TableHead>
                                        <TableHead>{teks.kolom.tanggal}</TableHead>
                                        <TableHead>{teks.kolom.status}</TableHead>
                                        <TableHead>{teks.kolom.cakupan}</TableHead>
                                        <TableHead>{teks.kolom.jumlah}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {pratinjau.ringkasanSumber.map((butir) => (
                                        <TableRow key={`${butir.sumber}-${butir.tanggal}-${butir.status}-${butir.cakupan}`}>
                                            <TableCell className="min-w-48">{butir.sumber}</TableCell>
                                            <TableCell className="whitespace-nowrap">{formatHari(butir.tanggal)}</TableCell>
                                            <TableCell>{butir.status}</TableCell>
                                            <TableCell>{butir.cakupan}</TableCell>
                                            <TableCell>{butir.jumlah.toLocaleString('id-ID')}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                {pratinjau.jumlahGalat > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{teks.galatJudul}</CardTitle>
                            <CardDescription>{teks.galatSub(pratinjau.galat.length, pratinjau.jumlahGalat)}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead>{teks.kolomGalat.baris}</TableHead>
                                        <TableHead>{teks.kolomGalat.kolom}</TableHead>
                                        <TableHead>{teks.kolomGalat.pesan}</TableHead>
                                        <TableHead>{teks.kolomGalat.nilai}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {pratinjau.galat.map((butir) => (
                                        <TableRow key={`${butir.baris}-${butir.kolom}-${butir.pesan}`}>
                                            <TableCell>{butir.baris}</TableCell>
                                            <TableCell>{butir.kolom ?? '-'}</TableCell>
                                            <TableCell className="min-w-56">{butir.pesan}</TableCell>
                                            <TableCell className="max-w-48 break-words">{butir.nilai ?? '-'}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button asChild variant="outline">
                        <Link href={rute.admin.statistik}>{teks.batal}</Link>
                    </Button>
                    <Button onClick={konfirmasi} disabled={pratinjau.barisValid === 0 || memproses}>
                        {memproses ? teks.memproses : teks.konfirmasi}
                    </Button>
                </div>
            </div>
        </LayoutAdmin>
    );
}
