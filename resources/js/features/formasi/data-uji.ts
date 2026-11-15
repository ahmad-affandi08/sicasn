import type { DetailFormasiItem, FormasiItem, OpsiFormasiItem, PeluangFormasiItem } from '@/types';

export const peluangUji: PeluangFormasiItem = {
    tingkat: 'ketat',
    tingkatLabel: 'Ketat',
    rasio: 25,
    batasBawah: false,
    pelamar: 2500,
    submit: null,
    formasi: 100,
    formasiDariTanggal: null,
    statusAngka: 'akhir',
    tingkatData: 'A',
    keyakinan: 'turunan',
    tanggal: '2024-09-17',
    sumberNama: 'Liputan6 mengutip BKN',
    sumberUrl: 'https://contoh.id/sumber',
    perJabatan: false,
};

export const formasiUji: FormasiItem = {
    id: 11,
    instansiId: 3,
    instansi: 'Kementerian Agama',
    jabatan: 'Penghulu Ahli Pertama',
    kodeJabatan: 'JF-PENGHULU',
    lokasi: 'Kabupaten Sleman',
    lokasiTeks: 'Kab. Sleman',
    kuota: 3,
    jenisFormasi: 'UMUM',
    disabilitas: false,
    usiaMin: 18,
    usiaMaks: 35,
    kualifikasiTersedia: true,
    kualifikasi: 'S-1 HUKUM / S-1 SYARIAH / D-IV HUKUM',
    peluang: peluangUji,
};

export const opsiUji: OpsiFormasiItem = {
    jenjang: ['D3', 'D4', 'S1'],
    provinsi: [
        { nilai: '34', label: 'Daerah Istimewa Yogyakarta', jumlah: 3 },
        { nilai: '33', label: 'Jawa Tengah', jumlah: 2 },
    ],
    kabupatenKota: [],
    jenisFormasi: [
        { nilai: 'UMUM', label: 'UMUM', jumlah: 5 },
        { nilai: 'DISABILITAS', label: 'DISABILITAS', jumlah: 1 },
    ],
    instansiTerpilih: null,
    jurusanTerpilih: [],
};

export const detailUji: DetailFormasiItem = {
    ...formasiUji,
    status: 'aktif',
    namaEvent: 'Seleksi CPNS 2026',
    jenisPengadaan: 'CPNS',
    periode: 1,
    tipeLokasi: 'kabupaten',
    provinsi: 'Daerah Istimewa Yogyakarta',
    penghasilanMin: null,
    penghasilanMaks: null,
    uraianTugas: null,
    kualifikasiItem: [
        { jenjang: 'S1', jurusan: 'HUKUM', semuaJurusan: false },
        { jenjang: 'D4', jurusan: null, semuaJurusan: true },
    ],
    persyaratan: [
        { uraian: 'Ijazah asli sesuai kualifikasi pendidikan.', wajib: true },
        { uraian: 'Surat keterangan sehat.', wajib: false },
    ],
    pertamaKaliTerlihat: '2026-11-15T10:00:00+07:00',
    terakhirTerlihat: '2026-11-16T02:00:00+07:00',
};
