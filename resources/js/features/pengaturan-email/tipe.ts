export interface PresetPenyedia {
    host: string;
    port: number;
    enkripsi: string;
    username: string;
    petunjuk: string;
}

export interface InfoPenyedia {
    kode: string;
    label: string;
    preset: PresetPenyedia;
}

export interface OpsiEnkripsi {
    kode: string;
    label: string;
}

export interface KonfigurasiTersimpan {
    host: string;
    port: number;
    enkripsi: string;
    username: string;
    passwordTersimpan: boolean;
}

export interface PengaturanEmailTampil {
    aktif: string | null;
    cadangan: string | null;
    pengirimNama: string;
    pengirimAlamat: string;
    alamatBalas: string;
    konfigurasi: Record<string, KonfigurasiTersimpan>;
    siap: boolean;
}

export interface IsianSmtp {
    host: string;
    port: string;
    enkripsi: string;
    username: string;
    password: string;
}

export interface IsianEmail {
    aktif: string;
    cadangan: string;
    pengirim_nama: string;
    pengirim_alamat: string;
    alamat_balas: string;
    konfigurasi: Record<string, IsianSmtp>;
}
