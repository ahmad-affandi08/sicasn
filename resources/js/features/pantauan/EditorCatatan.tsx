import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { teksPantauan } from '@/lang/pantauan';
import { rute } from '@/lib/rute';

interface PropsEditorCatatan {
    formasiId: number;
    catatan: string | null;
    maks: number;
}

export function EditorCatatan({ formasiId, catatan, maks }: PropsEditorCatatan) {
    const teks = teksPantauan.catatan;
    const form = useForm({ catatan: catatan ?? '' });
    const idKolom = `catatan-${formasiId}`;
    const berubah = form.data.catatan.trim() !== (catatan ?? '');

    function simpan(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.put(rute.catatanPantauan(formasiId), { preserveScroll: true });
    }

    return (
        <form onSubmit={simpan} noValidate className="flex flex-col gap-2">
            <label htmlFor={idKolom} className="text-sm font-medium">
                {teks.label}
            </label>
            <Textarea id={idKolom} rows={2} maxLength={maks + 50} placeholder={teks.placeholder} value={form.data.catatan} aria-invalid={Boolean(form.errors.catatan)} onChange={(kejadian) => form.setData('catatan', kejadian.target.value)} />
            {form.errors.catatan && (
                <p role="alert" className="text-sm font-medium text-bahaya">
                    {form.errors.catatan}
                </p>
            )}
            <div className="flex flex-wrap items-center justify-between gap-2">
                <span className="text-xs text-redup">
                    {teks.sisa(form.data.catatan.length, maks)}
                    {form.recentlySuccessful && <span className="ml-2 font-medium text-sukses">{teks.tersimpan}</span>}
                </span>
                <Button type="submit" variant="outline" size="sm" disabled={!berubah || form.processing}>
                    {form.processing ? teks.menyimpan : teks.simpan}
                </Button>
            </div>
        </form>
    );
}
