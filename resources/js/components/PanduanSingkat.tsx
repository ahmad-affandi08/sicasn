import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { ComponentType } from 'react';
import { LuCircleHelp } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { IlustrasiCariFormasi, IlustrasiInfo, IlustrasiPantau } from '@/components/ilustrasi';
import type { PropsIlustrasi } from '@/components/ilustrasi';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { teksSistem } from '@/lang/sistem';
import { rute } from '@/lib/rute';
import type { PropsBersama } from '@/types';

const ilustrasi: ComponentType<PropsIlustrasi>[] = [IlustrasiInfo, IlustrasiCariFormasi, IlustrasiPantau];

export function PanduanSingkat() {
    const { panduan } = usePage<PropsBersama>().props;
    const dialog = useDialog();
    const [terbuka, setTerbuka] = useState(false);
    const [langkah, setLangkah] = useState(0);
    const teks = teksSistem.panduan;
    const total = teks.daftar.length;
    const butir = teks.daftar[langkah] ?? teks.daftar[0];
    const Gambar = ilustrasi[langkah] ?? IlustrasiInfo;
    const terakhir = langkah === total - 1;

    useEffect(() => {
        if (!panduan) {
            return;
        }

        let dibatalkan = false;
        const penanda = window.setTimeout(() => {
            void dialog.tunggu().then(() => {
                if (!dibatalkan) {
                    setLangkah(0);
                    setTerbuka(true);
                }
            });
        }, 0);

        return () => {
            dibatalkan = true;
            window.clearTimeout(penanda);
        };
    }, [panduan, dialog]);

    function ubahTerbuka(nilai: boolean) {
        setTerbuka(nilai);

        if (nilai) {
            setLangkah(0);
            return;
        }

        if (panduan) {
            router.post(rute.panduanSelesai, {}, { preserveScroll: true, preserveState: true });
        }
    }

    return (
        <>
            <Button variant="ghost" size="icon" aria-label={teks.tombolBuka} onClick={() => ubahTerbuka(true)}>
                <LuCircleHelp aria-hidden className="h-5 w-5" />
            </Button>
            <AlertDialog open={terbuka} onOpenChange={ubahTerbuka}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <Gambar className="h-32 w-40" />
                        <p className="text-xs font-medium text-redup tabular-nums">{teks.langkah(langkah + 1, total)}</p>
                        <AlertDialogTitle>{butir.judul}</AlertDialogTitle>
                        <AlertDialogDescription>{butir.isi}</AlertDialogDescription>
                    </AlertDialogHeader>
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                        {langkah === 0 ? (
                            <Button variant="ghost" onClick={() => ubahTerbuka(false)}>
                                {teks.lewati}
                            </Button>
                        ) : (
                            <Button variant="outline" onClick={() => setLangkah(langkah - 1)}>
                                {teks.sebelumnya}
                            </Button>
                        )}
                        {terakhir ? (
                            <Button onClick={() => ubahTerbuka(false)}>{teks.selesai}</Button>
                        ) : (
                            <Button onClick={() => setLangkah(langkah + 1)}>{teks.berikutnya}</Button>
                        )}
                    </div>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
