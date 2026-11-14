import type { JenisDialogPesan } from '@/components/dialog/jenis';

export interface FlashDialog {
    jenis: JenisDialogPesan;
    judul: string;
    pesan: string;
}

export interface PropsBersama {
    aplikasi: { nama: string };
    whatsappUrl: string | null;
    dialog: FlashDialog | null;
    [kunci: string]: unknown;
}
