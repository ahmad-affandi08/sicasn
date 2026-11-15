import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { BidangForm } from '@/components/form/BidangForm';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { teksAuth } from '@/lang/auth';
import { LayoutAuth } from '@/layouts/LayoutAuth';
import { rute } from '@/lib/rute';
import type { PropsBersama } from '@/types';

interface PropsHalaman {
    wajib: boolean;
}

export default function GantiPassword({ wajib }: PropsHalaman) {
    const { auth } = usePage<PropsBersama>().props;
    const form = useForm({ password_lama: '', password: '', password_confirmation: '' });
    const teks = teksAuth.ganti;
    const tujuanBatal = auth.pengguna?.peran === 'admin' ? rute.admin.dasbor : rute.akun;

    function kirim(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.post(rute.gantiPassword, { onError: () => form.setData({ password_lama: form.data.password_lama, password: '', password_confirmation: '' }) });
    }

    return (
        <LayoutAuth>
            <Head title={teks.judul} />
            <Card className="w-full max-w-lg p-6 sm:p-10">
                <div className="mb-6 flex flex-col gap-2">
                    <h1 className="text-2xl font-medium">{teks.judul}</h1>
                    <p className="text-redup">{wajib ? teks.subWajib : teks.subBiasa}</p>
                </div>

                {wajib && (
                    <Alert variant="info" className="mb-6">
                        {teks.petunjuk}
                    </Alert>
                )}

                <form onSubmit={kirim} noValidate className="flex flex-col gap-5">
                    <BidangForm id="password_lama" label={wajib ? teks.lamaWajib : teks.lama} error={form.errors.password_lama}>
                        <Input
                            id="password_lama"
                            type="password"
                            autoComplete="current-password"
                            autoFocus
                            value={form.data.password_lama}
                            aria-invalid={Boolean(form.errors.password_lama)}
                            onChange={(kejadian) => form.setData('password_lama', kejadian.target.value)}
                        />
                    </BidangForm>
                    <BidangForm id="password" label={teks.baru} error={form.errors.password} petunjuk={teks.petunjuk}>
                        <Input
                            id="password"
                            type="password"
                            autoComplete="new-password"
                            value={form.data.password}
                            aria-invalid={Boolean(form.errors.password)}
                            onChange={(kejadian) => form.setData('password', kejadian.target.value)}
                        />
                    </BidangForm>
                    <BidangForm id="password_confirmation" label={teks.konfirmasi} error={form.errors.password_confirmation}>
                        <Input
                            id="password_confirmation"
                            type="password"
                            autoComplete="new-password"
                            value={form.data.password_confirmation}
                            onChange={(kejadian) => form.setData('password_confirmation', kejadian.target.value)}
                        />
                    </BidangForm>

                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        {wajib ? (
                            <Button type="button" variant="ghost" onClick={() => router.post(rute.keluar)}>
                                {teks.keluar}
                            </Button>
                        ) : (
                            <Button variant="outline" asChild>
                                <Link href={tujuanBatal}>{teks.batal}</Link>
                            </Button>
                        )}
                        <Button type="submit" disabled={form.processing}>
                            {teks.simpan}
                        </Button>
                    </div>
                </form>
            </Card>
        </LayoutAuth>
    );
}
