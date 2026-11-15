import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import { peluangJabatanUji, peluangUji } from './data-uji';
import { RingkasPeluang } from './RingkasPeluang';

describe('RingkasPeluang', () => {
    it('menampilkan pelamar, rasio, lencana, tingkat data, dan sumber', () => {
        render(<RingkasPeluang peluang={peluangUji} />);

        expect(screen.getByText('Ketat')).toBeInTheDocument();
        expect(screen.getByText('2.500')).toBeInTheDocument();
        expect(screen.getByText('25,0')).toBeInTheDocument();
        expect(screen.getByText(/Data tingkat A · Liputan6 mengutip BKN · 17 September 2024/)).toBeInTheDocument();
    });

    it('selalu menyebut angka adalah rata-rata instansi dan bukan jaminan', () => {
        render(<RingkasPeluang peluang={peluangUji} />);

        expect(screen.getByText('Rata-rata instansi, bukan per jabatan')).toBeInTheDocument();
        expect(screen.getByText('Berdasarkan data pelamar 2024, bukan jaminan tahun ini.')).toBeInTheDocument();
    });

    it('menandai angka sementara sebagai batas bawah', () => {
        render(<RingkasPeluang peluang={{ ...peluangUji, statusAngka: 'sementara', batasBawah: true, tingkat: 'belum_final' }} />);

        expect(screen.getByText('≥ 2.500')).toBeInTheDocument();
        expect(screen.getByText('≥ 25,0')).toBeInTheDocument();
        expect(screen.getByText('Belum final')).toBeInTheDocument();
    });

    it('memakai jumlah submit bila pelamar tidak ada dan menyebut tanggal formasi pinjaman', () => {
        render(<RingkasPeluang peluang={{ ...peluangUji, pelamar: null, submit: 900, formasiDariTanggal: '2024-09-11' }} />);

        expect(screen.getByText('≥ 900 submit')).toBeInTheDocument();
        expect(screen.getByText('Jumlah formasi per 11 September 2024')).toBeInTheDocument();
    });

    it('menyatakan belum ada data tanpa menebak dari instansi lain', () => {
        render(<RingkasPeluang peluang={null} />);

        expect(screen.getByText('Belum ada data')).toBeInTheDocument();
        expect(screen.getByText('Belum ada data pelamar 2024 untuk instansi ini.')).toBeInTheDocument();
    });

    it('menampilkan data per jabatan lebih dulu dengan label lolos administrasi dan rata-rata instansi', () => {
        render(<RingkasPeluang peluang={peluangUji} jabatan={peluangJabatanUji} ambang={{ longgarMaks: 5, ketatMin: 20 }} />);

        expect(screen.getByText('Per jabatan 2024')).toBeInTheDocument();
        expect(screen.getByText('Lolos administrasi')).toBeInTheDocument();
        expect(screen.getByText('100')).toBeInTheDocument();
        expect(screen.getByText('12,5')).toBeInTheDocument();
        expect(screen.getByText('Peserta yang dinyatakan lulus seleksi administrasi, bukan total pendaftar.')).toBeInTheDocument();
        expect(screen.getByText(/Data tingkat B · Pengumuman seleksi administrasi instansi · 19 September 2024/)).toBeInTheDocument();
        expect(screen.getByText('Rata-rata instansi')).toBeInTheDocument();
        expect(screen.queryByText('Rata-rata instansi, bukan per jabatan')).not.toBeInTheDocument();
    });

    it('menjelaskan bila rasio jabatan tidak bisa dihitung karena formasi 2024 tidak ada', () => {
        render(<RingkasPeluang peluang={peluangUji} jabatan={{ ...peluangJabatanUji, formasi: null, rasio: null, tingkat: 'belum_ada_data' }} />);

        expect(screen.getByText(/Rasio belum bisa dihitung karena jumlah formasi jabatan ini pada 2024 tidak tersedia/)).toBeInTheDocument();
        expect(screen.getByText('100')).toBeInTheDocument();
    });

    it('menyebut lokasi bila data jabatan spesifik lokasi', () => {
        render(<RingkasPeluang peluang={null} jabatan={{ ...peluangJabatanUji, lokasi: 'Kab. Sleman' }} />);

        expect(screen.getByText('Lokasi Kab. Sleman')).toBeInTheDocument();
        expect(screen.getByText('Rata-rata instansi')).toBeInTheDocument();
        expect(screen.getByText('Belum ada data')).toBeInTheDocument();
    });
});
