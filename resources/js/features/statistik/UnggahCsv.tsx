import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { LuDownload, LuUpload } from 'react-icons/lu';

import { BidangForm } from '@/components/form/BidangForm';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { teksAdmin } from '@/lang/admin';
import { rute } from '@/lib/rute';

export function UnggahCsv() {
    const teks = teksAdmin.statistik.unggah;
    const form = useForm<{ berkas: File | null }>({ berkas: null });

    function kirim(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.post(rute.admin.pratinjauStatistik, { forceFormData: true });
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>{teks.judul}</CardTitle>
                <CardDescription>{teks.petunjuk}</CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={kirim} noValidate className="flex flex-col gap-4">
                    <BidangForm id="berkas" label={teks.berkas} error={form.errors.berkas}>
                        <Input
                            id="berkas"
                            type="file"
                            accept=".csv,text/csv,text/plain"
                            className="h-auto py-2.5"
                            aria-invalid={Boolean(form.errors.berkas)}
                            onChange={(kejadian) => form.setData('berkas', kejadian.target.files?.[0] ?? null)}
                        />
                    </BidangForm>
                    <div className="flex flex-col gap-2 sm:flex-row sm:justify-between">
                        <Button asChild variant="outline">
                            <a href={rute.admin.templateStatistik}>
                                <LuDownload aria-hidden className="h-4 w-4" />
                                {teks.template}
                            </a>
                        </Button>
                        <Button type="submit" disabled={form.processing || form.data.berkas === null}>
                            <LuUpload aria-hidden className="h-4 w-4" />
                            {form.processing ? teks.memproses : teks.tombol}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
