import { Head, Link } from '@inertiajs/react';
import { LuArrowLeft, LuExternalLink } from 'react-icons/lu';

import { Catatan } from '@/components/Catatan';
import { TombolPantau } from '@/components/TombolPantau';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Alert } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DaftarKualifikasi } from '@/features/formasi/DaftarKualifikasi';
import { DaftarPersyaratan } from '@/features/formasi/DaftarPersyaratan';
import { InfoFormasi } from '@/features/formasi/InfoFormasi';
import { RingkasPeluang } from '@/features/formasi/RingkasPeluang';
import { ButirSnapshot } from '@/features/peluang/ButirSnapshot';
import { teksFormasi } from '@/lang/formasi';
import { LayoutMember } from '@/layouts/LayoutMember';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { DetailFormasiItem, PeluangFormasiItem, PeluangJabatanItem, SnapshotItem } from '@/types';

interface PropsHalaman {
    formasi: DetailFormasiItem;
    peluang: PeluangFormasiItem | null;
    peluangJabatan: PeluangJabatanItem | null;
    riwayatJabatan: SnapshotItem[];
    riwayat: SnapshotItem[];
    tautanResmi: string;
    ambang: { longgarMaks: number; ketatMin: number };
    dipantau: boolean;
}

function rentangPenghasilan(formasi: DetailFormasiItem): string {
    const { penghasilanMin: min, penghasilanMaks: maks } = formasi;

    if (min !== null && maks !== null) {
        return teksFormasi.detail.rentang(min, maks);
    }

    return min !== null || maks !== null ? teksFormasi.detail.rupiah((min ?? maks) as number) : teksFormasi.detail.belumTersedia;
}

export default function Detail({ formasi, peluang, peluangJabatan, riwayatJabatan, riwayat, tautanResmi, ambang, dipantau }: PropsHalaman) {
    const teks = teksFormasi.detail;
    const usia = teksFormasi.kartu.usia(formasi.usiaMin, formasi.usiaMaks);

    return (
        <LayoutMember>
            <Head title={`${formasi.jabatan} - ${formasi.instansi}`} />
            <div className="mb-4">
                <Button asChild variant="ghost" className="-ml-3">
                    <Link href={rute.formasi}>
                        <LuArrowLeft aria-hidden className="h-4 w-4" />
                        {teks.kembali}
                    </Link>
                </Button>
            </div>
            <HeaderHalaman
                judul={formasi.jabatan}
                sub={formasi.instansi}
                aksi={
                    <>
                        <TombolPantau formasiId={formasi.id} jabatan={formasi.jabatan} dipantau={dipantau} />
                        <Button asChild variant="outline">
                        <a href={tautanResmi} target="_blank" rel="noopener noreferrer">
                            {teksFormasi.sumberResmi}
                            <LuExternalLink aria-hidden className="h-4 w-4" />
                        </a>
                    </Button>
                    </>
                }
            />

            <div className="flex flex-col gap-6">
                {formasi.status === 'ditarik' && <Alert variant="bahaya">{teks.ditarik}</Alert>}
                <Catatan>{teksFormasi.disclaimer}</Catatan>

                <div className="grid gap-6 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>{teks.info}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl className="grid grid-cols-2 gap-x-4 gap-y-4">
                                <InfoFormasi label={teks.instansi} penuh>{formasi.instansi}</InfoFormasi>
                                <InfoFormasi label={teks.lokasi} penuh>{formasi.lokasi}</InfoFormasi>
                                {formasi.provinsi && <InfoFormasi label={teks.provinsi}>{formasi.provinsi}</InfoFormasi>}
                                <InfoFormasi label={teks.kuota}>{formasi.kuota.toLocaleString('id-ID')}</InfoFormasi>
                                <InfoFormasi label={teks.usia}>{usia || teks.belumTersedia}</InfoFormasi>
                                <InfoFormasi label={teks.jenisFormasi}>
                                    <span className="inline-flex flex-wrap items-center gap-2">
                                        {formasi.jenisFormasi}
                                        {formasi.disabilitas === true && <Badge variant="info">{teksFormasi.kartu.disabilitas}</Badge>}
                                    </span>
                                </InfoFormasi>
                                <InfoFormasi label={teks.jenisPengadaan}>{formasi.jenisPengadaan}</InfoFormasi>
                                <InfoFormasi label={teks.event} penuh>{formasi.namaEvent}</InfoFormasi>
                                <InfoFormasi label={teks.penghasilan}>{rentangPenghasilan(formasi)}</InfoFormasi>
                                {formasi.kodeJabatan && <InfoFormasi label={teks.kodeJabatan}>{formasi.kodeJabatan}</InfoFormasi>}
                            </dl>
                            <p className="mt-4 text-xs text-redup">{teks.terakhirTerlihat(formatWaktu(formasi.terakhirTerlihat))}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{teks.persaingan}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <RingkasPeluang peluang={peluang} jabatan={peluangJabatan} ambang={ambang} />
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.kualifikasi}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DaftarKualifikasi item={formasi.kualifikasiItem} tersedia={formasi.kualifikasiTersedia} />
                    </CardContent>
                </Card>

                {formasi.uraianTugas && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{teks.uraianTugas}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="whitespace-pre-line">{formasi.uraianTugas}</p>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.persyaratan}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DaftarPersyaratan persyaratan={formasi.persyaratan} />
                    </CardContent>
                </Card>

                {riwayatJabatan.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{teks.riwayatJabatan}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="grid gap-3 md:grid-cols-2">
                                {riwayatJabatan.map((snapshot) => (
                                    <ButirSnapshot key={`${snapshot.tanggal}-${snapshot.sumberNama}-${snapshot.status}-${snapshot.lokasi ?? ''}`} snapshot={snapshot} />
                                ))}
                            </ul>
                        </CardContent>
                    </Card>
                )}

                {riwayat.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{teks.riwayat}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="grid gap-3 md:grid-cols-2">
                                {riwayat.map((snapshot) => (
                                    <ButirSnapshot key={`${snapshot.tanggal}-${snapshot.sumberNama}-${snapshot.status}-${snapshot.keyakinan}`} snapshot={snapshot} />
                                ))}
                            </ul>
                        </CardContent>
                    </Card>
                )}
            </div>
        </LayoutMember>
    );
}
