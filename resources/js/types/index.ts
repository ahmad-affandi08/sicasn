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
    panduan: boolean;
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

export type TingkatPersainganKode = 'longgar' | 'sedang' | 'ketat' | 'belum_final' | 'belum_ada_data';

export interface SnapshotItem {
    tanggal: string;
    status: 'sementara' | 'akhir';
    formasi: number | null;
    pelamar: number | null;
    submit: number | null;
    lolosAdministrasi: number | null;
    tingkatData: 'A' | 'B' | 'C';
    keyakinan: 'resmi' | 'turunan';
    sumberNama: string;
    sumberUrl: string | null;
    catatan: string | null;
    lokasi?: string | null;
}

export interface InstansiPeluangItem {
    id: number;
    nama: string;
    tipe: 'pusat' | 'daerah' | 'unknown';
    formasi: number | null;
    formasiDariTanggal: string | null;
    pelamar: number | null;
    submit: number | null;
    rasio: number | null;
    batasBawah: boolean;
    tingkat: TingkatPersainganKode;
    tingkatLabel: string;
    terbaru: SnapshotItem;
    riwayat: SnapshotItem[];
    formasiAktif: number;
}

export interface AngkaNasionalItem {
    jenis: 'pelamar' | 'submit' | 'formasi' | 'lolos_administrasi';
    nilai: number;
    tanggal: string;
    sumberNama: string;
    sumberUrl: string | null;
    keyakinan: 'resmi' | 'turunan';
}

export interface FilterPeluangItem {
    cari: string;
    tipe: string;
    tingkat: string;
    urut: string;
}

export interface RiwayatStatistikItem {
    id: number;
    status: 'berjalan' | 'sukses' | 'sebagian' | 'gagal' | 'dibatalkan';
    mulaiAt: string;
    selesaiAt: string | null;
    berkas: string | null;
    ringkasan: { total_baris?: number; baris_baru?: number; baris_duplikat?: number; baris_gagal?: number; baris_dihapus?: number; galat?: string };
    bisaDibatalkan: boolean;
}

export interface PratinjauStatistikItem {
    token: string | null;
    namaBerkas: string;
    totalBaris: number;
    barisValid: number;
    jumlahGalat: number;
    galat: { baris: number; kolom: string | null; pesan: string; nilai: string | null }[];
    ringkasanSumber: { sumber: string; tanggal: string; status: string; cakupan: string; jumlah: number }[];
    galatBerkas: string | null;
}

export interface OpsiPilihan {
    nilai: string;
    label: string;
    jumlah: number;
}

export interface PeluangFormasiItem {
    tingkat: TingkatPersainganKode;
    tingkatLabel: string;
    rasio: number | null;
    batasBawah: boolean;
    pelamar: number | null;
    submit: number | null;
    formasi: number | null;
    formasiDariTanggal: string | null;
    statusAngka: 'sementara' | 'akhir';
    tingkatData: 'A' | 'B' | 'C';
    keyakinan: 'resmi' | 'turunan';
    tanggal: string;
    sumberNama: string;
    sumberUrl: string | null;
    perJabatan: boolean;
}

export interface PeluangJabatanItem {
    tingkat: TingkatPersainganKode;
    tingkatLabel: string;
    rasio: number | null;
    batasBawah: boolean;
    formasi: number | null;
    pelamar: number | null;
    submit: number | null;
    lolosAdministrasi: number | null;
    lokasi: string | null;
    statusAngka: 'sementara' | 'akhir';
    tingkatData: 'A' | 'B' | 'C';
    keyakinan: 'resmi' | 'turunan';
    tanggal: string;
    sumberNama: string;
    sumberUrl: string | null;
    catatan: string | null;
}

export interface FormasiItem {
    id: number;
    instansiId: number;
    instansi: string;
    jabatan: string;
    kodeJabatan: string;
    lokasi: string;
    lokasiTeks: string;
    kuota: number;
    jenisFormasi: string;
    disabilitas: boolean | null;
    usiaMin: number | null;
    usiaMaks: number | null;
    kualifikasiTersedia: boolean;
    kualifikasi: string | null;
    peluang: PeluangFormasiItem | null;
    peluangJabatan: PeluangJabatanItem | null;
}

export interface FilterFormasiItem {
    cari: string;
    jenjang: string[];
    jurusan: number[];
    provinsi: number | null;
    wilayah: number | null;
    instansi: number | null;
    jenis: string;
    tingkat: string;
    urut: string;
    sertakan: boolean;
}

export interface OpsiFormasiItem {
    jenjang: string[];
    provinsi: OpsiPilihan[];
    kabupatenKota: OpsiPilihan[];
    jenisFormasi: OpsiPilihan[];
    instansiTerpilih: OpsiPilihan | null;
    jurusanTerpilih: OpsiPilihan[];
}

export interface DetailFormasiItem extends Omit<FormasiItem, 'peluang' | 'peluangJabatan'> {
    status: 'aktif' | 'ditarik';
    namaEvent: string;
    jenisPengadaan: string;
    periode: number;
    tipeLokasi: string;
    provinsi: string | null;
    penghasilanMin: number | null;
    penghasilanMaks: number | null;
    uraianTugas: string | null;
    kualifikasiItem: { jenjang: string; jurusan: string | null; semuaJurusan: boolean }[];
    persyaratan: { uraian: string; wajib: boolean }[];
    pertamaKaliTerlihat: string;
    terakhirTerlihat: string;
}

export interface PantauanItem extends FormasiItem {
    status: 'aktif' | 'ditarik';
    catatan: string | null;
    ditambahkanAt: string;
}
