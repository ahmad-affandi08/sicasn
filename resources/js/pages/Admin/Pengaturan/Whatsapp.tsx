import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { LuMessageCircle } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { BidangForm } from '@/components/form/BidangForm';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { rute } from '@/lib/rute';

interface PropsHalaman {
    pengaturan: { nomor: string; namaTampilan: string; templatePesan: string };
    pratinjauPesan: string;
}

export default function Whatsapp({ pengaturan }: PropsHalaman) {
    const teks = teksAdmin.pengaturan.whatsapp;
    const dialog = useDialog();
    const form = useForm({ nomor: pengaturan.nomor, nama_tampilan: pengaturan.namaTampilan, template_pesan: pengaturan.templatePesan });

    const pratinjau = form.data.template_pesan.replace('{nama_admin}', form.data.nama_tampilan.trim() || 'Admin');

    async function simpan(kejadian: FormEvent) {
        kejadian.preventDefault();

        const setuju = await dialog.konfirmasi({ judul: teks.konfirmasi.judul, pesan: teks.konfirmasi.pesan, teksTombol: teks.konfirmasi.tombol });

        if (setuju) {
            form.put(rute.admin.whatsapp, { preserveScroll: true });
        }
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <form onSubmit={simpan} noValidate className="flex max-w-2xl flex-col gap-6">
                <Card>
                    <CardContent className="flex flex-col gap-5 pt-6">
                        <BidangForm id="nomor" label={teks.nomor} error={form.errors.nomor} petunjuk={teks.nomorPetunjuk}>
                            <Input
                                id="nomor"
                                type="tel"
                                inputMode="tel"
                                autoComplete="off"
                                value={form.data.nomor}
                                aria-invalid={Boolean(form.errors.nomor)}
                                onChange={(kejadian) => form.setData('nomor', kejadian.target.value)}
                            />
                        </BidangForm>
                        <BidangForm id="nama_tampilan" label={teks.nama} error={form.errors.nama_tampilan}>
                            <Input id="nama_tampilan" value={form.data.nama_tampilan} onChange={(kejadian) => form.setData('nama_tampilan', kejadian.target.value)} />
                        </BidangForm>
                        <BidangForm id="template_pesan" label={teks.template} error={form.errors.template_pesan} petunjuk={teks.templatePetunjuk}>
                            <Textarea id="template_pesan" value={form.data.template_pesan} onChange={(kejadian) => form.setData('template_pesan', kejadian.target.value)} />
                        </BidangForm>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.pratinjau}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-start gap-3 rounded-xl bg-sukses-lembut p-4">
                            <LuMessageCircle aria-hidden className="mt-0.5 h-5 w-5 shrink-0 text-sukses" />
                            <p className="break-words">{pratinjau}</p>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end">
                    <Button type="submit" size="lg" disabled={form.processing}>
                        {form.processing ? teksAdmin.pengaturan.memproses : teks.simpanTombol}
                    </Button>
                </div>
            </form>
        </LayoutAdmin>
    );
}
