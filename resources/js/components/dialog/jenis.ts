export type JenisDialog = 'sukses' | 'gagal' | 'galat' | 'peringatan' | 'info' | 'konfirmasi';

export type JenisDialogPesan = Exclude<JenisDialog, 'konfirmasi'>;

export interface OpsiPesan {
    judul: string;
    pesan: string;
    teksTombol?: string;
}

export interface OpsiKonfirmasi extends OpsiPesan {
    teksBatal?: string;
    berisiko?: boolean;
}

export interface OpsiKonfirmasiKetik extends OpsiKonfirmasi {
    kataKunci: string;
}

export interface TampilanDialog {
    jenis: JenisDialog;
    judul: string;
    pesan: string;
    teksTombol?: string;
    teksBatal?: string;
    berisiko?: boolean;
    kataKunci?: string;
}

export interface PerintahDialog {
    sukses: (opsi: OpsiPesan) => Promise<void>;
    gagal: (opsi: OpsiPesan) => Promise<void>;
    galat: (opsi: OpsiPesan) => Promise<void>;
    peringatan: (opsi: OpsiPesan) => Promise<void>;
    info: (opsi: OpsiPesan) => Promise<void>;
    konfirmasi: (opsi: OpsiKonfirmasi) => Promise<boolean>;
    konfirmasiKetik: (opsi: OpsiKonfirmasiKetik) => Promise<boolean>;
}
