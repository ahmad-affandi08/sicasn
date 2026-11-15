import { render as renderPolos, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';

vi.mock('@inertiajs/react', () => ({
    router: { post: vi.fn(), delete: vi.fn() },
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
}));

import { formasiUji, peluangJabatanUji } from './data-uji';
import { KartuFormasi } from './KartuFormasi';

function render(isi: ReactNode) {
    return renderPolos(<DialogProvider>{isi}</DialogProvider>);
}

describe('KartuFormasi', () => {
    it('menampilkan instansi, jabatan, lokasi, kuota, usia, dan syarat pendidikan', () => {
        render(<KartuFormasi formasi={formasiUji} />);

        expect(screen.getByRole('heading', { name: 'Penghulu Ahli Pertama' })).toBeInTheDocument();
        expect(screen.getByText('Kementerian Agama')).toBeInTheDocument();
        expect(screen.getByText(/Kabupaten Sleman/)).toBeInTheDocument();
        expect(screen.getByText('3')).toBeInTheDocument();
        expect(screen.getByText(/Usia 18–35 tahun/)).toBeInTheDocument();
        expect(screen.getByText(/S-1 HUKUM \/ S-1 SYARIAH \/ D-IV HUKUM/)).toBeInTheDocument();
    });

    it('menautkan ke halaman detail dengan nama yang jelas bagi pembaca layar', () => {
        render(<KartuFormasi formasi={formasiUji} />);

        const tautan = screen.getByRole('link', { name: /Lihat detail.*Penghulu Ahli Pertama/s });

        expect(tautan).toHaveAttribute('href', '/formasi/11');
    });

    it('menyertakan tingkat persaingan instansi dengan lencana, ikon, dan teks', () => {
        render(<KartuFormasi formasi={formasiUji} />);

        expect(screen.getByText('Ketat')).toBeInTheDocument();
        expect(screen.getByText('Rata-rata instansi, bukan per jabatan')).toBeInTheDocument();
    });

    it('menjelaskan kualifikasi yang kosong tanpa mengarang jurusan', () => {
        render(<KartuFormasi formasi={{ ...formasiUji, kualifikasiTersedia: false, kualifikasi: null }} />);

        expect(screen.getByText('Kualifikasi mengikuti ketentuan jabatan, lihat persyaratan')).toBeInTheDocument();
    });

    it('menandai formasi disabilitas dan menyatakan belum ada data persaingan', () => {
        render(<KartuFormasi formasi={{ ...formasiUji, disabilitas: true, jenisFormasi: 'DISABILITAS', peluang: null }} />);

        expect(screen.getByText('Disabilitas')).toBeInTheDocument();
        expect(screen.getByText('Belum ada data')).toBeInTheDocument();
    });

    it('mengutamakan data per jabatan bila tersedia', () => {
        render(<KartuFormasi formasi={{ ...formasiUji, peluangJabatan: peluangJabatanUji }} ambang={{ longgarMaks: 5, ketatMin: 20 }} />);

        expect(screen.getByText('Per jabatan 2024')).toBeInTheDocument();
        expect(screen.getByText('Lolos administrasi')).toBeInTheDocument();
        expect(screen.queryByText('Rata-rata instansi, bukan per jabatan')).not.toBeInTheDocument();
    });
});
