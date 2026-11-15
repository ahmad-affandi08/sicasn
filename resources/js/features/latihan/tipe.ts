export type KategoriSoal = 'twk' | 'tiu' | 'tkp';

export type ModeLatihan = 'simulasi' | 'twk' | 'tiu' | 'tkp';

export type StatusSoal = 'benar' | 'salah' | 'kosong';

export interface OpsiSoal {
    id: number;
    label: string;
    teks: string;
}

export interface SoalDikerjakan {
    urutan: number;
    soalId: number;
    kategori: KategoriSoal;
    subkategori: string;
    pertanyaan: string;
    opsi: OpsiSoal[];
    opsiDipilihId: number | null;
    ragu: boolean;
}

export interface PercobaanBerjalan {
    id: number;
    mode: ModeLatihan;
    modeLabel: string;
    sisaDetik: number;
    durasiDetik: number;
    soal: SoalDikerjakan[];
}

export interface JawabanSoal {
    opsiId: number | null;
    ragu: boolean;
}

export type PetaJawaban = Record<number, JawabanSoal>;

export interface AmbangKategori {
    resmi: number;
    maks: number;
    jumlah: number;
}

export interface InfoMode {
    nilai: ModeLatihan;
    label: string;
    komposisi: Partial<Record<KategoriSoal, number>>;
}

export interface ItemRiwayat {
    id: number;
    mode: ModeLatihan;
    modeLabel: string;
    selesai: string;
    jumlahSoal: number;
    skor: Partial<Record<KategoriSoal, number>>;
    lulus: boolean | null;
}

export interface HasilKategori {
    kategori: KategoriSoal;
    label: string;
    jumlah: number;
    skor: number;
    skorMaks: number;
    benar: number;
    salah: number;
    kosong: number;
    ambangSetara: number;
    tercapai: boolean;
}

export interface OpsiHasil extends OpsiSoal {
    skor: number;
}

export interface SoalHasil {
    urutan: number;
    kategori: KategoriSoal;
    subkategori: string;
    pertanyaan: string;
    pembahasan: string;
    sumberNama: string;
    sumberUrl: string | null;
    lisensi: string;
    opsi: OpsiHasil[];
    opsiDipilihId: number | null;
    ragu: boolean;
    status: StatusSoal;
}

export interface HasilLatihan {
    id: number;
    mode: ModeLatihan;
    modeLabel: string;
    mulai: string;
    selesai: string;
    total: number;
    totalMaks: number;
    lulus: boolean | null;
    kategori: HasilKategori[];
    soal: SoalHasil[];
}
