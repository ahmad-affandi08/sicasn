import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { BidangForm } from '@/components/form/BidangForm';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { rute } from '@/lib/rute';

interface PropsHalaman {
    pengaturan: { longgarMaks: number; ketatMin: number };
}

export default function Persaingan({ pengaturan }: PropsHalaman) {
    const teks = teksAdmin.pengaturan.persaingan;
    const form = useForm({ longgar_maks: String(pengaturan.longgarMaks), ketat_min: String(pengaturan.ketatMin) });

    function simpan(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.put(rute.admin.persaingan, { preserveScroll: true });
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <form onSubmit={simpan} noValidate className="flex max-w-2xl flex-col gap-6">
                <Card>
                    <CardContent className="flex flex-col gap-5 pt-6">
                        <BidangForm id="longgar_maks" label={teks.longgar} error={form.errors.longgar_maks} petunjuk={teks.longgarPetunjuk}>
                            <Input
                                id="longgar_maks"
                                type="number"
                                inputMode="decimal"
                                min="0"
                                step="0.5"
                                value={form.data.longgar_maks}
                                aria-invalid={Boolean(form.errors.longgar_maks)}
                                onChange={(kejadian) => form.setData('longgar_maks', kejadian.target.value)}
                            />
                        </BidangForm>
                        <BidangForm id="ketat_min" label={teks.ketat} error={form.errors.ketat_min} petunjuk={teks.ketatPetunjuk}>
                            <Input
                                id="ketat_min"
                                type="number"
                                inputMode="decimal"
                                min="0"
                                step="0.5"
                                value={form.data.ketat_min}
                                aria-invalid={Boolean(form.errors.ketat_min)}
                                onChange={(kejadian) => form.setData('ketat_min', kejadian.target.value)}
                            />
                        </BidangForm>
                        <Alert variant="info">{teks.catatan}</Alert>
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
