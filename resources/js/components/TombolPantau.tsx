import { router } from '@inertiajs/react';
import { LuBookmark, LuBookmarkCheck } from 'react-icons/lu';

import { useDialog } from '@/components/dialog/konteks';
import { Button } from '@/components/ui/button';
import { teksPantauan } from '@/lang/pantauan';
import { rute } from '@/lib/rute';

interface PropsTombolPantau {
    formasiId: number;
    jabatan: string;
    dipantau: boolean;
    muatUlang?: string[];
}

export function TombolPantau({ formasiId, jabatan, dipantau, muatUlang = ['dipantau'] }: PropsTombolPantau) {
    const teks = teksPantauan;
    const dialog = useDialog();
    const opsi = { preserveScroll: true, preserveState: true, only: [...muatUlang, 'dialog', 'errors'] };

    async function ubah() {
        if (!dipantau) {
            router.post(rute.pantauan, { formasi_id: formasiId }, opsi);

            return;
        }

        const setuju = await dialog.konfirmasi({ judul: teks.konfirmasiHentikan.judul, pesan: teks.konfirmasiHentikan.pesan(jabatan), teksTombol: teks.konfirmasiHentikan.tombol, berisiko: true });

        if (setuju) {
            router.delete(rute.hapusPantauan(formasiId), opsi);
        }
    }

    return (
        <Button type="button" variant={dipantau ? 'secondary' : 'outline'} size="sm" aria-pressed={dipantau} aria-label={teks.tombol.aria(dipantau ? teks.tombol.dipantau : teks.tombol.pantau, jabatan)} onClick={ubah}>
            {dipantau ? <LuBookmarkCheck aria-hidden className="h-4 w-4" /> : <LuBookmark aria-hidden className="h-4 w-4" />}
            {dipantau ? teks.tombol.dipantau : teks.tombol.pantau}
        </Button>
    );
}
