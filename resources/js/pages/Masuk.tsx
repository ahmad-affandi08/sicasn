import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { LuEye, LuEyeOff } from 'react-icons/lu';

import { BidangForm } from '@/components/form/BidangForm';
import { IlustrasiLogin } from '@/components/ilustrasi';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { teksAuth } from '@/lang/auth';
import { LayoutAuth } from '@/layouts/LayoutAuth';
import { rute } from '@/lib/rute';

export default function Masuk() {
    const form = useForm({ email: '', password: '', ingat: false });
    const [tampil, setTampil] = useState(false);
    const teks = teksAuth.masuk;

    function kirim(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.post(rute.masuk, { onFinish: () => form.setData('password', '') });
    }

    return (
        <LayoutAuth>
            <Head title={teks.tombol} />
            <Card className="grid w-full max-w-3xl overflow-hidden md:grid-cols-[1.1fr_1fr]">
                <div className="flex flex-col gap-6 p-6 sm:p-10">
                    <div className="flex flex-col gap-2">
                        <h1 className="text-2xl font-medium">{teks.judul}</h1>
                        <p className="text-redup">{teks.sub}</p>
                    </div>

                    <form onSubmit={kirim} noValidate className="flex flex-col gap-5">
                        <BidangForm id="email" label={teks.email} error={form.errors.email}>
                            <Input
                                id="email"
                                type="email"
                                inputMode="email"
                                autoComplete="username"
                                autoFocus
                                placeholder={teks.emailPlaceholder}
                                value={form.data.email}
                                aria-invalid={Boolean(form.errors.email)}
                                aria-describedby={form.errors.email ? 'email-galat' : undefined}
                                onChange={(kejadian) => form.setData('email', kejadian.target.value)}
                            />
                        </BidangForm>

                        <BidangForm id="password" label={teks.password} error={form.errors.password}>
                            <div className="relative">
                                <Input
                                    id="password"
                                    type={tampil ? 'text' : 'password'}
                                    autoComplete="current-password"
                                    className="pr-12"
                                    value={form.data.password}
                                    aria-invalid={Boolean(form.errors.password)}
                                    onChange={(kejadian) => form.setData('password', kejadian.target.value)}
                                />
                                <button
                                    type="button"
                                    onClick={() => setTampil((nilai) => !nilai)}
                                    aria-label={tampil ? teks.sembunyikan : teks.tampilkan}
                                    className="absolute top-0 right-0 inline-flex h-11 w-11 items-center justify-center rounded-lg text-redup hover:text-primer"
                                >
                                    {tampil ? <LuEyeOff aria-hidden className="h-5 w-5" /> : <LuEye aria-hidden className="h-5 w-5" />}
                                </button>
                            </div>
                        </BidangForm>

                        <label className="flex min-h-11 cursor-pointer items-center gap-3 text-sm">
                            <input
                                type="checkbox"
                                className="h-5 w-5 accent-[var(--primer)]"
                                checked={form.data.ingat}
                                onChange={(kejadian) => form.setData('ingat', kejadian.target.checked)}
                            />
                            {teks.ingat}
                        </label>

                        <Button type="submit" size="lg" disabled={form.processing}>
                            {form.processing ? teks.memproses : teks.tombol}
                        </Button>
                    </form>

                    <p className="text-sm text-redup">
                        {teks.belumPunya}{' '}
                        <Link href="/#cara-akses" className="font-medium text-primer hover:underline">
                            {teks.caraAkses}
                        </Link>
                    </p>
                </div>
                <div className="hidden items-center justify-center bg-permukaan p-8 md:flex">
                    <IlustrasiLogin className="h-auto w-full max-w-sm" judul={teks.ilustrasi} />
                </div>
            </Card>
        </LayoutAuth>
    );
}
