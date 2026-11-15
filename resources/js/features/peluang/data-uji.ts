import type { InstansiPeluangItem, SnapshotItem } from '@/types';

export const snapshotUji: SnapshotItem = {
    tanggal: '2024-09-17',
    status: 'akhir',
    formasi: 9070,
    pelamar: 567552,
    submit: null,
    lolosAdministrasi: null,
    tingkatData: 'A',
    keyakinan: 'turunan',
    sumberNama: 'Liputan6 mengutip BKN',
    sumberUrl: 'https://contoh.id/sumber',
    catatan: null,
};

export const instansiUji: InstansiPeluangItem = {
    id: 1,
    nama: 'Kementerian Hukum dan Hak Asasi Manusia',
    tipe: 'pusat',
    formasi: 9070,
    formasiDariTanggal: null,
    pelamar: 567552,
    submit: null,
    rasio: 62.6,
    batasBawah: false,
    tingkat: 'ketat',
    tingkatLabel: 'Ketat',
    formasiAktif: 0,
    terbaru: snapshotUji,
    riwayat: [snapshotUji, { ...snapshotUji, tanggal: '2024-08-29', status: 'sementara', pelamar: null, submit: 183931, sumberNama: 'Media Awal', sumberUrl: null }],
};
