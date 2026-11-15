import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import { peluangUji } from './data-uji';
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
});
