import { Head, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { LuRefreshCw } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { BidangForm } from '@/components/form/BidangForm';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Alert } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { LencanaBatch } from '@/features/sinkron/LencanaBatch';
import { RingkasanBatch } from '@/features/sinkron/RingkasanBatch';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { BatchItem, EventItem } from '@/types';

interface PropsHalaman {
    sedangBerjalan: boolean;
    formasiAktif: number;
    lokasiTidakTerpetakan: { geografis: number; satuanKerja: number };
    jenisAktif: string[];
    terakhirBerhasil: BatchItem | null;
    riwayat: BatchItem[];
    event: EventItem[];
    pengaturan: { url: string; frekuensi: string };
    frekuensi: { kode: string; label: string }[];
}

export default function Sinkron({ sedangBerjalan, formasiAktif, lokasiTidakTerpetakan, jenisAktif, terakhirBerhasil, riwayat, event, pengaturan, frekuensi }: PropsHalaman) {
    const teks = teksAdmin.sinkron;
    const dialog = useDialog();
    const form = useForm({ url: pengaturan.url, frekuensi: pengaturan.frekuensi });

    async function jalankan() {
        const setuju = await dialog.konfirmasi({ judul: teks.konfirmasi.judul, pesan: teks.konfirmasi.pesan, teksTombol: teks.konfirmasi.tombol });

        if (setuju) {
            router.post(rute.admin.jalankanSinkron, {}, { preserveScroll: true });
        }
    }

    function simpan(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.put(rute.admin.pengaturanSinkron, { preserveScroll: true });
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman
                judul={teks.judul}
                sub={teks.sub}
                aksi={
                    <Button onClick={jalankan} disabled={sedangBerjalan}>
                        <LuRefreshCw aria-hidden className="h-4 w-4" />
                        {teks.jalankan}
                    </Button>
                }
            />

            <div className="flex flex-col gap-6">
                {sedangBerjalan && <Alert variant="info">{teks.berjalan}</Alert>}
                <Alert variant="info">{teks.cakupan(jenisAktif.join(', '))}</Alert>
                {lokasiTidakTerpetakan.geografis > 0 && (
                    <Alert variant="peringatan">
                        <div>
                            <p className="font-medium">{teks.lokasi.judul}</p>
                            <p>{teks.lokasi.geografis(lokasiTidakTerpetakan.geografis)}</p>
                        </div>
                    </Alert>
                )}
                {lokasiTidakTerpetakan.satuanKerja > 0 && <Alert variant="info">{teks.lokasi.satuanKerja(lokasiTidakTerpetakan.satuanKerja)}</Alert>}

                <div className="grid gap-6 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>{teks.statusTerakhir}</CardTitle>
                            {terakhirBerhasil && <CardDescription>{formatWaktu(terakhirBerhasil.selesaiAt)}</CardDescription>}
                        </CardHeader>
                        <CardContent>{terakhirBerhasil ? <RingkasanBatch batch={terakhirBerhasil} /> : <p className="text-redup">{teks.belumPernah}</p>}</CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex h-full flex-col justify-center gap-1 pt-6">
                            <p className="text-4xl font-medium">{formasiAktif.toLocaleString('id-ID')}</p>
                            <p className="text-sm text-redup">{teks.formasiAktif}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.event.judul}</CardTitle>
                        <CardDescription>{teks.event.sub}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {event.length === 0 ? (
                            <p className="text-sm text-redup">{teks.event.kosong}</p>
                        ) : (
                            <ul className="flex flex-col divide-y divide-garis">
                                {event.map((butir) => (
                                    <li key={butir.eventId} className="flex flex-col gap-2 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                        <div className="min-w-0">
                                            <p className="font-medium break-words">{butir.nama}</p>
                                            <p className="text-sm text-redup">
                                                {butir.jenis} · {teks.event.baris(butir.jumlahBaris)} · {teks.event.pertama} {formatWaktu(butir.pertamaKaliTerlihat)}
                                            </p>
                                        </div>
                                        <Badge variant={butir.diabaikan ? 'netral' : 'sukses'} className="self-start">
                                            {butir.diabaikan ? teks.event.diabaikan : teks.event.diproses}
                                        </Badge>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.riwayat.judul}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {riwayat.length === 0 ? (
                            <p className="text-sm text-redup">{teks.riwayat.kosong}</p>
                        ) : (
                            <ul className="flex flex-col gap-4">
                                {riwayat.map((batch) => (
                                    <li key={batch.id} className="flex flex-col gap-2 rounded-lg border border-garis p-4">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <p className="text-sm text-redup">
                                                {teks.riwayat.mulai} {formatWaktu(batch.mulaiAt)}
                                            </p>
                                            <LencanaBatch status={batch.status} />
                                        </div>
                                        {batch.status !== 'berjalan' && <RingkasanBatch batch={batch} />}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.pengaturan.judul}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={simpan} noValidate className="flex flex-col gap-5">
                            <BidangForm id="url" label={teks.pengaturan.url} error={form.errors.url} petunjuk={teks.pengaturan.urlPetunjuk}>
                                <Input id="url" type="url" inputMode="url" value={form.data.url} aria-invalid={Boolean(form.errors.url)} onChange={(kejadian) => form.setData('url', kejadian.target.value)} />
                            </BidangForm>
                            <BidangForm id="frekuensi" label={teks.pengaturan.frekuensi} error={form.errors.frekuensi} petunjuk={teks.pengaturan.frekuensiPetunjuk}>
                                <Select id="frekuensi" value={form.data.frekuensi} onChange={(kejadian) => form.setData('frekuensi', kejadian.target.value)}>
                                    {frekuensi.map((opsi) => (
                                        <option key={opsi.kode} value={opsi.kode}>
                                            {opsi.label}
                                        </option>
                                    ))}
                                </Select>
                            </BidangForm>
                            <div className="flex justify-end">
                                <Button type="submit" disabled={form.processing}>
                                    {form.processing ? teksAdmin.pengaturan.memproses : teks.pengaturan.simpan}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </LayoutAdmin>
    );
}
