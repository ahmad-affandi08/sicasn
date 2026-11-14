import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { LuArrowLeft, LuSend } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { BidangForm } from '@/components/form/BidangForm';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { LencanaAkun, LencanaEmail } from '@/components/StatusAkun';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { CatatanAuditItem, PenggunaItem } from '@/types';

interface PropsHalaman {
    pengguna: PenggunaItem;
    riwayat: CatatanAuditItem[];
}

export default function Tampil({ pengguna, riwayat }: PropsHalaman) {
    const teks = teksAdmin.detail;
    const dialog = useDialog();
    const [alasan, setAlasan] = useState('');
    const member = pengguna.peran === 'member';

    async function kirimUlang() {
        const setuju = await dialog.konfirmasi({ judul: teks.kirimUlangKonfirmasi.judul, pesan: teks.kirimUlangKonfirmasi.pesan, teksTombol: teks.kirimUlangKonfirmasi.tombol });

        if (setuju) {
            router.post(rute.admin.kirimUlang(pengguna.id), {}, { preserveScroll: true });
        }
    }

    async function nonaktifkan() {
        const setuju = await dialog.konfirmasi({
            judul: teks.nonaktifkanKonfirmasi.judul,
            pesan: teks.nonaktifkanKonfirmasi.pesan,
            teksTombol: teks.nonaktifkanKonfirmasi.tombol,
            berisiko: true,
        });

        if (setuju) {
            router.post(rute.admin.nonaktifkan(pengguna.id), { alasan }, { preserveScroll: true, onSuccess: () => setAlasan('') });
        }
    }

    async function aktifkan() {
        const setuju = await dialog.konfirmasi({ judul: teks.aktifkanKonfirmasi.judul, pesan: teks.aktifkanKonfirmasi.pesan, teksTombol: teks.aktifkanKonfirmasi.tombol });

        if (setuju) {
            router.post(rute.admin.aktifkan(pengguna.id), {}, { preserveScroll: true });
        }
    }

    return (
        <LayoutAdmin>
            <Head title={pengguna.nama} />
            <Button asChild variant="ghost" size="sm" className="mb-4 -ml-3">
                <Link href={rute.admin.pengguna}>
                    <LuArrowLeft aria-hidden className="h-4 w-4" />
                    {teks.kembali}
                </Link>
            </Button>
            <HeaderHalaman judul={pengguna.nama} sub={pengguna.email} aksi={<LencanaAkun pengguna={pengguna} />} />

            {!pengguna.aktif && pengguna.alasanNonaktif && (
                <Alert variant="peringatan" className="mb-6">
                    <span>
                        <strong>{teks.alasanNonaktif}:</strong> {pengguna.alasanNonaktif}
                    </span>
                </Alert>
            )}

            <div className="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{teks.info}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl className="grid gap-4 text-sm sm:grid-cols-2">
                            <div>
                                <dt className="text-redup">{teks.email}</dt>
                                <dd className="font-medium break-all">{pengguna.email}</dd>
                            </div>
                            <div>
                                <dt className="text-redup">{teks.whatsapp}</dt>
                                <dd className="font-medium">{pengguna.noWhatsapp ?? '-'}</dd>
                            </div>
                            <div className="sm:col-span-2">
                                <dt className="text-redup">{teks.catatan}</dt>
                                <dd className="font-medium">{pengguna.catatanPembayaran ?? '-'}</dd>
                            </div>
                            <div>
                                <dt className="text-redup">{teks.dibuat}</dt>
                                <dd className="font-medium">{formatWaktu(pengguna.dibuatAt)}</dd>
                            </div>
                            <div>
                                <dt className="text-redup">{teks.masukTerakhir}</dt>
                                <dd className="font-medium">{formatWaktu(pengguna.lastLoginAt)}</dd>
                            </div>
                            {member && (
                                <>
                                    <div>
                                        <dt className="text-redup">{teks.kataSandi}</dt>
                                        <dd className="font-medium">{pengguna.wajibGantiPassword ? teks.masihSementara : teks.sudahDiganti}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-redup">{teks.emailKredensial}</dt>
                                        <dd className="mt-1">
                                            <LencanaEmail status={pengguna.statusEmail} />
                                        </dd>
                                    </div>
                                </>
                            )}
                        </dl>
                        {pengguna.statusEmail === 'gagal' && pengguna.galatEmail && (
                            <Alert variant="bahaya" className="mt-4 break-words">
                                {pengguna.galatEmail}
                            </Alert>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.tindakan}</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-6">
                        {member ? (
                            <>
                                <Button variant="outline" onClick={kirimUlang} className="self-start">
                                    <LuSend aria-hidden className="h-4 w-4" />
                                    {teks.kirimUlang}
                                </Button>

                                {pengguna.aktif ? (
                                    <div className="flex flex-col gap-3 border-t border-garis pt-6">
                                        <BidangForm id="alasan" label={teks.alasan} petunjuk={teks.alasanPetunjuk}>
                                            <Input id="alasan" value={alasan} maxLength={255} onChange={(kejadian) => setAlasan(kejadian.target.value)} />
                                        </BidangForm>
                                        <Button variant="destructive" onClick={nonaktifkan} className="self-start">
                                            {teks.nonaktifkan}
                                        </Button>
                                    </div>
                                ) : (
                                    <div className="border-t border-garis pt-6">
                                        <Button onClick={aktifkan}>{teks.aktifkan}</Button>
                                    </div>
                                )}
                            </>
                        ) : (
                            <p className="text-sm text-redup">{teks.akunAdmin}</p>
                        )}
                    </CardContent>
                </Card>

                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>{teks.riwayat}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {riwayat.length === 0 ? (
                            <p className="text-sm text-redup">{teks.riwayatKosong}</p>
                        ) : (
                            <ol className="flex flex-col gap-4">
                                {riwayat.map((catatan) => (
                                    <li key={`${catatan.aksi}-${catatan.waktu}`} className="flex flex-col gap-0.5 border-l-2 border-primer pl-4">
                                        <p className="font-medium">{teksAdmin.audit[catatan.aksi] ?? catatan.aksi}</p>
                                        <p className="text-sm text-redup">
                                            {formatWaktu(catatan.waktu)}
                                            {catatan.aktor ? ` · ${teks.oleh(catatan.aktor)}` : ''}
                                        </p>
                                        {typeof catatan.rincian.alasan === 'string' && catatan.rincian.alasan !== '' && <p className="text-sm">{catatan.rincian.alasan}</p>}
                                    </li>
                                ))}
                            </ol>
                        )}
                    </CardContent>
                </Card>
            </div>
        </LayoutAdmin>
    );
}
