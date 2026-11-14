import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { LuPlus, LuTrash2 } from 'react-icons/lu';

import { BidangForm } from '@/components/form/BidangForm';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { rute } from '@/lib/rute';

interface Rekening {
    bank: string;
    nomor: string;
    atas_nama: string;
}

interface PropsHalaman {
    pengaturan: { rekening: Rekening[]; hargaTeks: string; catatan: string; tampilkanRekeningPublik: boolean };
}

const MAKS_REKENING = 5;

export default function Pembayaran({ pengaturan }: PropsHalaman) {
    const teks = teksAdmin.pengaturan.pembayaran;
    const form = useForm({
        rekening: pengaturan.rekening,
        harga_teks: pengaturan.hargaTeks,
        catatan: pengaturan.catatan,
        tampilkan_rekening_publik: pengaturan.tampilkanRekeningPublik,
    });
    const galat = form.errors as Record<string, string | undefined>;

    function ubahRekening(indeks: number, kolom: keyof Rekening, nilai: string) {
        form.setData('rekening', form.data.rekening.map((baris, posisi) => (posisi === indeks ? { ...baris, [kolom]: nilai } : baris)));
    }

    function tambahRekening() {
        form.setData('rekening', [...form.data.rekening, { bank: '', nomor: '', atas_nama: '' }]);
    }

    function hapusRekening(indeks: number) {
        form.setData('rekening', form.data.rekening.filter((_, posisi) => posisi !== indeks));
    }

    function simpan(kejadian: FormEvent) {
        kejadian.preventDefault();
        form.put(rute.admin.pembayaran, { preserveScroll: true });
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <form onSubmit={simpan} noValidate className="flex max-w-3xl flex-col gap-6">
                <Card>
                    <CardContent className="flex flex-col gap-5 pt-6">
                        <BidangForm id="harga_teks" label={teks.harga} error={form.errors.harga_teks}>
                            <Input id="harga_teks" placeholder={teks.hargaPlaceholder} value={form.data.harga_teks} onChange={(kejadian) => form.setData('harga_teks', kejadian.target.value)} />
                        </BidangForm>
                        <BidangForm id="catatan" label={teks.catatan} error={form.errors.catatan}>
                            <Textarea id="catatan" placeholder={teks.catatanPlaceholder} value={form.data.catatan} onChange={(kejadian) => form.setData('catatan', kejadian.target.value)} />
                        </BidangForm>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.rekening}</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-5">
                        {form.data.rekening.map((baris, indeks) => (
                            <fieldset key={indeks} className="grid gap-4 rounded-xl border border-garis p-4 sm:grid-cols-[1fr_1.4fr_1.4fr_auto] sm:items-end">
                                <legend className="sr-only">{`${teks.rekening} ${indeks + 1}`}</legend>
                                <BidangForm id={`bank-${indeks}`} label={teks.bank} error={galat[`rekening.${indeks}.bank`]}>
                                    <Input id={`bank-${indeks}`} value={baris.bank} onChange={(kejadian) => ubahRekening(indeks, 'bank', kejadian.target.value)} />
                                </BidangForm>
                                <BidangForm id={`nomor-${indeks}`} label={teks.nomorRekening} error={galat[`rekening.${indeks}.nomor`]}>
                                    <Input id={`nomor-${indeks}`} inputMode="numeric" value={baris.nomor} onChange={(kejadian) => ubahRekening(indeks, 'nomor', kejadian.target.value)} />
                                </BidangForm>
                                <BidangForm id={`atas-${indeks}`} label={teks.atasNama} error={galat[`rekening.${indeks}.atas_nama`]}>
                                    <Input id={`atas-${indeks}`} value={baris.atas_nama} onChange={(kejadian) => ubahRekening(indeks, 'atas_nama', kejadian.target.value)} />
                                </BidangForm>
                                <Button type="button" variant="ghost" size="icon" aria-label={teks.hapus} onClick={() => hapusRekening(indeks)}>
                                    <LuTrash2 aria-hidden className="h-5 w-5 text-bahaya" />
                                </Button>
                            </fieldset>
                        ))}
                        {form.data.rekening.length < MAKS_REKENING && (
                            <Button type="button" variant="outline" className="self-start" onClick={tambahRekening}>
                                <LuPlus aria-hidden className="h-4 w-4" />
                                {teks.tambah}
                            </Button>
                        )}
                        <div className="flex items-start gap-4 border-t border-garis pt-5">
                            <Switch
                                id="tampilkan_rekening_publik"
                                checked={form.data.tampilkan_rekening_publik}
                                onCheckedChange={(nilai) => form.setData('tampilkan_rekening_publik', nilai)}
                                aria-describedby="tampilkan-petunjuk"
                            />
                            <div>
                                <label htmlFor="tampilkan_rekening_publik" className="font-medium">
                                    {teks.tampilkanPublik}
                                </label>
                                <p id="tampilkan-petunjuk" className="text-sm text-redup">
                                    {teks.tampilkanPublikPetunjuk}
                                </p>
                            </div>
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
