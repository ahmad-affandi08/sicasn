import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { LuArrowLeft } from 'react-icons/lu';

import { BidangForm } from '@/components/form/BidangForm';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { rute } from '@/lib/rute';

export default function Buat() {
    const teks = teksAdmin.buat;
    const form = useForm({ nama: '', email: '', no_whatsapp: '', catatan_pembayaran: '' });

    function kirim(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.post(rute.admin.pengguna);
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <Button asChild variant="ghost" size="sm" className="mb-4 -ml-3">
                <Link href={rute.admin.pengguna}>
                    <LuArrowLeft aria-hidden className="h-4 w-4" />
                    {teksAdmin.detail.kembali}
                </Link>
            </Button>
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <Card className="max-w-2xl p-6 sm:p-8">
                <form onSubmit={kirim} noValidate className="flex flex-col gap-5">
                    <BidangForm id="nama" label={teks.nama} error={form.errors.nama}>
                        <Input id="nama" autoFocus autoComplete="off" value={form.data.nama} aria-invalid={Boolean(form.errors.nama)} onChange={(kejadian) => form.setData('nama', kejadian.target.value)} />
                    </BidangForm>
                    <BidangForm id="email" label={teks.email} error={form.errors.email} petunjuk={teks.emailPetunjuk}>
                        <Input
                            id="email"
                            type="email"
                            inputMode="email"
                            autoComplete="off"
                            value={form.data.email}
                            aria-invalid={Boolean(form.errors.email)}
                            onChange={(kejadian) => form.setData('email', kejadian.target.value)}
                        />
                    </BidangForm>
                    <BidangForm id="no_whatsapp" label={teks.whatsapp} error={form.errors.no_whatsapp}>
                        <Input id="no_whatsapp" type="tel" inputMode="tel" autoComplete="off" value={form.data.no_whatsapp} onChange={(kejadian) => form.setData('no_whatsapp', kejadian.target.value)} />
                    </BidangForm>
                    <BidangForm id="catatan_pembayaran" label={teks.catatan} error={form.errors.catatan_pembayaran} petunjuk={teks.catatanPetunjuk}>
                        <Input
                            id="catatan_pembayaran"
                            autoComplete="off"
                            value={form.data.catatan_pembayaran}
                            onChange={(kejadian) => form.setData('catatan_pembayaran', kejadian.target.value)}
                        />
                    </BidangForm>

                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Button asChild variant="outline">
                            <Link href={rute.admin.pengguna}>{teks.batal}</Link>
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? teks.memproses : teks.simpan}
                        </Button>
                    </div>
                </form>
            </Card>
        </LayoutAdmin>
    );
}
