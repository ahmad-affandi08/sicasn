import { useRef, useState } from 'react';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    IlustrasiGagal,
    IlustrasiGalat,
    IlustrasiInfo,
    IlustrasiKonfirmasi,
    IlustrasiPeringatan,
    IlustrasiSukses,
} from '@/components/ilustrasi';
import { teks } from '@/lang/id';
import { cn } from '@/lib/utils';

import type { TampilanDialog } from './jenis';

interface PropsKotakDialog {
    tampilan: TampilanDialog;
    onTutup: (hasil: boolean) => void;
}

function IlustrasiDialog({ tampilan }: { tampilan: TampilanDialog }) {
    const kelas = 'h-28 w-28';

    switch (tampilan.jenis) {
        case 'sukses':
            return <IlustrasiSukses className={kelas} />;
        case 'gagal':
            return <IlustrasiGagal className={kelas} />;
        case 'galat':
            return <IlustrasiGalat className={kelas} />;
        case 'peringatan':
            return <IlustrasiPeringatan className={kelas} />;
        case 'info':
            return <IlustrasiInfo className={kelas} />;
        case 'konfirmasi':
            return tampilan.berisiko ? <IlustrasiPeringatan className={kelas} /> : <IlustrasiKonfirmasi className={kelas} />;
    }
}

export function KotakDialog({ tampilan, onTutup }: PropsKotakDialog) {
    const hasilRef = useRef(false);
    const [ketikan, setKetikan] = useState('');

    const perluKonfirmasi = tampilan.jenis === 'konfirmasi';
    const kataKunciCocok = tampilan.kataKunci === undefined || ketikan.trim() === tampilan.kataKunci;
    const teksTombolUtama = tampilan.teksTombol ?? (perluKonfirmasi ? teks.dialog.lanjutkan : teks.dialog.mengerti);

    return (
        <AlertDialog
            open
            onOpenChange={(buka) => {
                if (!buka) {
                    onTutup(hasilRef.current);
                }
            }}
        >
            <AlertDialogContent
                onEscapeKeyDown={(kejadian) => {
                    if (tampilan.berisiko) {
                        kejadian.preventDefault();
                    }
                }}
            >
                <AlertDialogHeader>
                    <IlustrasiDialog tampilan={tampilan} />
                    <AlertDialogTitle>{tampilan.judul}</AlertDialogTitle>
                    <AlertDialogDescription>{tampilan.pesan}</AlertDialogDescription>
                </AlertDialogHeader>

                {tampilan.kataKunci !== undefined && (
                    <div className="flex flex-col gap-2">
                        <label htmlFor="kata-kunci-dialog" className="text-sm text-redup">
                            {teks.dialog.ketikUntukLanjut(tampilan.kataKunci)}
                        </label>
                        <Input
                            id="kata-kunci-dialog"
                            value={ketikan}
                            autoComplete="off"
                            autoFocus
                            onChange={(kejadian) => setKetikan(kejadian.target.value)}
                        />
                    </div>
                )}

                <AlertDialogFooter>
                    {perluKonfirmasi && <AlertDialogCancel>{tampilan.teksBatal ?? teks.dialog.batal}</AlertDialogCancel>}
                    {tampilan.tautan && (
                        <a
                            href={tampilan.tautan.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className={cn(buttonVariants({ variant: 'secondary' }), 'sm:min-w-32')}
                        >
                            {tampilan.tautan.label}
                        </a>
                    )}
                    <AlertDialogAction
                        disabled={!kataKunciCocok}
                        className={cn(tampilan.berisiko && buttonVariants({ variant: 'destructive' }))}
                        onClick={() => {
                            hasilRef.current = true;
                        }}
                    >
                        {teksTombolUtama}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
