import { Head, useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { LuSend } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { BidangForm } from '@/components/form/BidangForm';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { KonfigurasiSmtp } from '@/features/pengaturan-email/KonfigurasiSmtp';
import { isianAwal, isianAwalPenyedia, keMuatan } from '@/features/pengaturan-email/isian';
import type { InfoPenyedia, IsianEmail, IsianSmtp, OpsiEnkripsi, PengaturanEmailTampil } from '@/features/pengaturan-email/tipe';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { rute } from '@/lib/rute';

interface PropsHalaman {
    pengaturan: PengaturanEmailTampil;
    penyedia: InfoPenyedia[];
    enkripsi: OpsiEnkripsi[];
}

export default function Email({ pengaturan, penyedia, enkripsi }: PropsHalaman) {
    const teks = teksAdmin.pengaturan.email;
    const dialog = useDialog();
    const form = useForm<IsianEmail>(isianAwal(pengaturan, penyedia));
    const uji = useForm({ tujuan: '' });
    const [sedangMenyimpan, setSedangMenyimpan] = useState(false);

    const galatForm = form.errors as Record<string, string | undefined>;
    const cariPenyedia = (kode: string) => penyedia.find((item) => item.kode === kode);

    function pilihPenyedia(kolom: 'aktif' | 'cadangan', kode: string) {
        form.setData((sebelumnya) => {
            const konfigurasi = { ...sebelumnya.konfigurasi };

            if (kode !== '' && !konfigurasi[kode]) {
                konfigurasi[kode] = isianAwalPenyedia(kode, penyedia, pengaturan.konfigurasi);
            }

            const berikutnya = { ...sebelumnya, [kolom]: kode, konfigurasi };

            if (berikutnya.aktif === berikutnya.cadangan) {
                berikutnya.cadangan = '';
            }

            return berikutnya;
        });
    }

    function ubahIsian(kode: string, kolom: keyof IsianSmtp, nilai: string) {
        form.setData((sebelumnya) => {
            const lama = sebelumnya.konfigurasi[kode] ?? isianAwalPenyedia(kode, penyedia, pengaturan.konfigurasi);

            return { ...sebelumnya, konfigurasi: { ...sebelumnya.konfigurasi, [kode]: { ...lama, [kolom]: nilai } } };
        });
    }

    async function simpan(kejadian: FormEvent) {
        kejadian.preventDefault();

        const setuju = await dialog.konfirmasi({ judul: teks.konfirmasi.judul, pesan: teks.konfirmasi.pesan, teksTombol: teks.konfirmasi.tombol });

        if (!setuju) {
            return;
        }

        setSedangMenyimpan(true);
        form.transform((data) => keMuatan(data));
        form.put(rute.admin.email, { preserveScroll: true, onFinish: () => setSedangMenyimpan(false) });
    }

    function kirimUji(kejadian: FormEvent) {
        kejadian.preventDefault();
        uji.post(rute.admin.ujiEmail, { preserveScroll: true });
    }

    function blokKonfigurasi(kode: string) {
        const info = cariPenyedia(kode);

        if (!info) {
            return null;
        }

        return (
            <KonfigurasiSmtp
                key={kode}
                awalan={kode}
                penyedia={info}
                nilai={form.data.konfigurasi[kode] ?? isianAwalPenyedia(kode, penyedia, pengaturan.konfigurasi)}
                enkripsi={enkripsi}
                passwordTersimpan={pengaturan.konfigurasi[kode]?.passwordTersimpan ?? false}
                galat={(kolom) => galatForm[`konfigurasi.${kode}.${kolom}`]}
                onUbah={(kolom, nilai) => ubahIsian(kode, kolom, nilai)}
            />
        );
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman
                judul={teks.judul}
                sub={teks.sub}
                aksi={<Badge variant={pengaturan.siap ? 'sukses' : 'peringatan'}>{pengaturan.siap ? teks.status.siap : teks.status.belum}</Badge>}
            />

            <form onSubmit={simpan} noValidate className="flex flex-col gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{teks.penyedia}</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-5 sm:grid-cols-2">
                        <BidangForm id="aktif" label={teks.aktif} error={form.errors.aktif}>
                            <Select id="aktif" value={form.data.aktif} onChange={(kejadian) => pilihPenyedia('aktif', kejadian.target.value)}>
                                {penyedia.map((item) => (
                                    <option key={item.kode} value={item.kode}>
                                        {item.label}
                                    </option>
                                ))}
                            </Select>
                        </BidangForm>
                        <BidangForm id="cadangan" label={teks.cadangan} error={form.errors.cadangan} petunjuk={teks.cadanganPetunjuk}>
                            <Select id="cadangan" value={form.data.cadangan} onChange={(kejadian) => pilihPenyedia('cadangan', kejadian.target.value)}>
                                <option value="">{teks.tanpaCadangan}</option>
                                {penyedia
                                    .filter((item) => item.kode !== form.data.aktif)
                                    .map((item) => (
                                        <option key={item.kode} value={item.kode}>
                                            {item.label}
                                        </option>
                                    ))}
                            </Select>
                        </BidangForm>
                    </CardContent>
                </Card>

                {blokKonfigurasi(form.data.aktif)}
                {form.data.cadangan !== '' && blokKonfigurasi(form.data.cadangan)}

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.pengirim}</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-5 sm:grid-cols-2">
                        <BidangForm id="pengirim_nama" label={teks.pengirimNama} error={form.errors.pengirim_nama}>
                            <Input id="pengirim_nama" value={form.data.pengirim_nama} onChange={(kejadian) => form.setData('pengirim_nama', kejadian.target.value)} />
                        </BidangForm>
                        <BidangForm id="pengirim_alamat" label={teks.pengirimAlamat} error={form.errors.pengirim_alamat} petunjuk={teks.pengirimAlamatPetunjuk}>
                            <Input
                                id="pengirim_alamat"
                                type="email"
                                inputMode="email"
                                value={form.data.pengirim_alamat}
                                onChange={(kejadian) => form.setData('pengirim_alamat', kejadian.target.value)}
                            />
                        </BidangForm>
                        <BidangForm id="alamat_balas" label={teks.alamatBalas} error={form.errors.alamat_balas} className="sm:col-span-2">
                            <Input id="alamat_balas" type="email" inputMode="email" value={form.data.alamat_balas} onChange={(kejadian) => form.setData('alamat_balas', kejadian.target.value)} />
                        </BidangForm>
                    </CardContent>
                </Card>

                <div className="flex justify-end">
                    <Button type="submit" size="lg" disabled={form.processing || sedangMenyimpan}>
                        {form.processing ? teksAdmin.pengaturan.memproses : teks.simpanTombol}
                    </Button>
                </div>
            </form>

            <Card className="mt-8">
                <CardHeader>
                    <CardTitle>{teks.uji}</CardTitle>
                    <CardDescription>{teks.ujiSub}</CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={kirimUji} noValidate className="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <BidangForm id="tujuan" label={teks.ujiTujuan} error={uji.errors.tujuan} className="flex-1">
                            <Input id="tujuan" type="email" inputMode="email" value={uji.data.tujuan} onChange={(kejadian) => uji.setData('tujuan', kejadian.target.value)} />
                        </BidangForm>
                        <Button type="submit" variant="outline" disabled={uji.processing || !pengaturan.siap}>
                            <LuSend aria-hidden className="h-4 w-4" />
                            {uji.processing ? teks.ujiMemproses : teks.ujiTombol}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </LayoutAdmin>
    );
}
