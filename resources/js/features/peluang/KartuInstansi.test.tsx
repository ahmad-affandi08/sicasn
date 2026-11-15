import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import { instansiUji } from './data-uji';
import { KartuInstansi } from './KartuInstansi';

describe('KartuInstansi', () => {
    it('menampilkan nama, angka, rasio, lencana, dan sumber', () => {
        render(<KartuInstansi instansi={instansiUji} onBuka={vi.fn()} />);

        expect(screen.getByText('Kementerian Hukum dan Hak Asasi Manusia')).toBeInTheDocument();
        expect(screen.getByText('567.552')).toBeInTheDocument();
        expect(screen.getByText('9.070')).toBeInTheDocument();
        expect(screen.getByText('62,6')).toBeInTheDocument();
        expect(screen.getByText('Ketat')).toBeInTheDocument();
        expect(screen.getByText(/Liputan6 mengutip BKN/)).toBeInTheDocument();
    });

    it('menandai angka sementara sebagai batas bawah dan jumlah submit', () => {
        const sementara = { ...instansiUji, pelamar: null, submit: 183931, rasio: 20.3, batasBawah: true, terbaru: { ...instansiUji.terbaru, status: 'sementara' as const } };
        render(<KartuInstansi instansi={sementara} onBuka={vi.fn()} />);

        expect(screen.getByText('≥ 183.931 submit')).toBeInTheDocument();
        expect(screen.getByText('≥ 20,3')).toBeInTheDocument();
    });

    it('menyebut tanggal bila jumlah formasi dipinjam dari snapshot lain', () => {
        render(<KartuInstansi instansi={{ ...instansiUji, formasiDariTanggal: '2024-09-11' }} onBuka={vi.fn()} />);

        expect(screen.getByText('Formasi per 11 September 2024')).toBeInTheDocument();
    });

    it('menampilkan strip untuk angka yang tidak tersedia', () => {
        render(<KartuInstansi instansi={{ ...instansiUji, formasi: null, pelamar: null, submit: null, rasio: null, tingkat: 'belum_ada_data' }} onBuka={vi.fn()} />);

        expect(screen.getAllByText('-').length).toBeGreaterThanOrEqual(3);
        expect(screen.getByText('Belum ada data')).toBeInTheDocument();
    });

    it('membuka rincian saat tombol ditekan', async () => {
        const buka = vi.fn();
        render(<KartuInstansi instansi={instansiUji} onBuka={buka} />);

        await userEvent.click(screen.getByRole('button', { name: /Lihat rincian/ }));

        expect(buka).toHaveBeenCalledWith(instansiUji);
    });
});
