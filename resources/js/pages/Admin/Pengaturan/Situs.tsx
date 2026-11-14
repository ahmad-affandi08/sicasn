import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Switch } from '@/components/ui/switch';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { rute } from '@/lib/rute';

interface PropsHalaman {
    pengaturan: { satuSesi: boolean };
}

export default function Situs({ pengaturan }: PropsHalaman) {
    const teks = teksAdmin.pengaturan.situs;
    const form = useForm({ satu_sesi: pengaturan.satuSesi });

    function simpan(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.put(rute.admin.situs, { preserveScroll: true });
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <form onSubmit={simpan} className="flex max-w-2xl flex-col gap-6">
                <Card>
                    <CardContent className="flex items-start gap-4 pt-6">
                        <Switch id="satu_sesi" checked={form.data.satu_sesi} onCheckedChange={(nilai) => form.setData('satu_sesi', nilai)} aria-describedby="satu-sesi-petunjuk" />
                        <div>
                            <label htmlFor="satu_sesi" className="font-medium">
                                {teks.satuSesi}
                            </label>
                            <p id="satu-sesi-petunjuk" className="text-sm text-redup">
                                {teks.satuSesiPetunjuk}
                            </p>
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
