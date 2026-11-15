import { render as renderPolos, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { detailUji, peluangJabatanUji, peluangUji } from '@/features/formasi/data-uji';
import { snapshotUji } from '@/features/peluang/data-uji';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    router: { get: vi.fn(), post: vi.fn() },
    usePage: () => ({
        url: '/formasi/11',
        props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Siti', email: 's@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import Detail from './Detail';

function render(isi: ReactNode) {
    return renderPolos(<DialogProvider>{isi}</DialogProvider>);
}

const dasar = { formasi: detailUji, peluang: peluangUji, peluangJabatan: null, riwayatJabatan: [], riwayat: [snapshotUji], tautanResmi: 'https://sscasn.bkn.go.id', ambang: { longgarMaks: 5, ketatMin: 20 }, dipantau: false };

describe('Formasi/Detail', () => {
    it('menampilkan informasi lengkap formasi', () => {
        render(<Detail {...dasar} />);

        expect(screen.getByRole('heading', { name: 'Penghulu Ahli Pertama' })).toBeInTheDocument();
        expect(screen.getByText('Seleksi CPNS 2026')).toBeInTheDocument();
        expect(screen.getByText('Daerah Istimewa Yogyakarta')).toBeInTheDocument();
        expect(screen.getByText('Usia 18–35 tahun')).toBeInTheDocument();
    });

    it('menampilkan kualifikasi per butir termasuk semua jurusan', () => {
        render(<Detail {...dasar} />);

        expect(screen.getByText(/· HUKUM/)).toBeInTheDocument();
        expect(screen.getByText(/· Semua jurusan/)).toBeInTheDocument();
    });

    it('menandai persyaratan wajib dan opsional', () => {
        render(<Detail {...dasar} />);

        expect(screen.getByText('Ijazah asli sesuai kualifikasi pendidikan.')).toBeInTheDocument();
        expect(screen.getByText('Wajib')).toBeInTheDocument();
        expect(screen.getByText('Opsional')).toBeInTheDocument();
    });

    it('menjelaskan kualifikasi kosong dan persyaratan kosong tanpa mengarang', () => {
        render(<Detail {...dasar} formasi={{ ...detailUji, kualifikasiTersedia: false, kualifikasiItem: [], persyaratan: [] }} />);

        expect(screen.getByText('Kualifikasi mengikuti ketentuan jabatan, lihat persyaratan')).toBeInTheDocument();
        expect(screen.getByText('BKN belum memuat rincian persyaratan untuk formasi ini.')).toBeInTheDocument();
    });

    it('menampilkan peluang, riwayat bersumber, dan tautan sumber resmi', () => {
        render(<Detail {...dasar} />);

        expect(screen.getByText('Rata-rata instansi, bukan per jabatan')).toBeInTheDocument();
        expect(screen.getAllByText(/Liputan6 mengutip BKN/).length).toBeGreaterThan(0);
        expect(screen.getByRole('link', { name: /Buka sscasn.bkn.go.id/ })).toHaveAttribute('href', 'https://sscasn.bkn.go.id');
        expect(screen.getByText(/SICASN bukan portal resmi pemerintah/)).toBeInTheDocument();
    });

    it('memperingatkan bila formasi sudah ditarik dari data BKN', () => {
        render(<Detail {...dasar} formasi={{ ...detailUji, status: 'ditarik' }} />);

        expect(screen.getByText(/tidak lagi tampil di data BKN terbaru/)).toBeInTheDocument();
    });

    it('menyatakan belum ada data persaingan bila instansi tidak punya statistik', () => {
        render(<Detail {...dasar} peluang={null} riwayat={[]} />);

        expect(screen.getByText('Belum ada data pelamar 2024 untuk instansi ini.')).toBeInTheDocument();
    });

    it('menampilkan rentang penghasilan bila tersedia', () => {
        render(<Detail {...dasar} formasi={{ ...detailUji, penghasilanMin: 3000000, penghasilanMaks: 5000000 }} />);

        expect(screen.getByText('Rp 3.000.000 – Rp 5.000.000')).toBeInTheDocument();
    });

    it('menampilkan riwayat angka jabatan beserta sumber bila ada data per jabatan', () => {
        render(<Detail {...dasar} peluangJabatan={peluangJabatanUji} riwayatJabatan={[{ ...snapshotUji, tingkatData: 'B', sumberNama: 'Pengumuman jabatan uji', lokasi: null }]} />);

        expect(screen.getByText('Riwayat angka jabatan ini')).toBeInTheDocument();
        expect(screen.getAllByText(/Pengumuman jabatan uji/).length).toBeGreaterThan(0);
        expect(screen.getByText('Per jabatan 2024')).toBeInTheDocument();
    });

    it('tidak menampilkan riwayat jabatan bila kosong', () => {
        render(<Detail {...dasar} />);

        expect(screen.queryByText('Riwayat angka jabatan ini')).not.toBeInTheDocument();
    });
});
