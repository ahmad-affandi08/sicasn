import type { JenisDialogPesan, TautanDialog } from '@/components/dialog/jenis';

export interface FlashDialog {
    jenis: JenisDialogPesan;
    judul: string;
    pesan: string;
    tautan?: TautanDialog;
}

export interface PenggunaMasuk {
    nama: string;
    email: string;
    peran: 'admin' | 'member';
    wajibGantiPassword: boolean;
}

export interface InfoPembayaran {
    hargaTeks: string;
    catatan: string;
    rekening: { bank: string; nomor: string; atasNama: string }[];
}

export interface PropsBersama {
    aplikasi: { nama: string };
    whatsappUrl: string | null;
    auth: { pengguna: PenggunaMasuk | null };
    dialog: FlashDialog | null;
    [kunci: string]: unknown;
}

export interface PenggunaItem {
    id: number;
    nama: string;
    email: string;
    noWhatsapp: string | null;
    peran: 'admin' | 'member';
    aktif: boolean;
    catatanPembayaran: string | null;
    alasanNonaktif: string | null;
    wajibGantiPassword: boolean;
    dibuatAt: string;
    lastLoginAt: string | null;
    statusEmail: 'menunggu' | 'terkirim' | 'gagal' | null;
    galatEmail: string | null;
}

export interface CatatanAuditItem {
    aksi: string;
    aktor: string | null;
    rincian: Record<string, unknown>;
    waktu: string;
}

export interface BatchItem {
    id: number;
    status: 'berjalan' | 'sukses' | 'sebagian' | 'gagal';
    mulaiAt: string;
    selesaiAt: string | null;
    ringkasan: {
        total_baris?: number;
        baris_aktif?: number;
        diabaikan?: number;
        baru?: number;
        diubah?: number;
        tak_berubah?: number;
        ditarik?: number;
        gagal_baris?: number;
        penarikan_ditunda?: boolean;
        durasi_detik?: number;
        dilewati?: boolean;
        galat?: string;
    };
}

export interface EventItem {
    eventId: string;
    nama: string;
    jenis: string;
    jumlahBaris: number;
    diabaikan: boolean;
    pertamaKaliTerlihat: string;
    sudahDiberitahukan: boolean;
}
