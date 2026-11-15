import { render as renderPolos, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { instansiUji } from '@/features/peluang/data-uji';

const inertia = vi.hoisted(() => ({ ambil: vi.fn() }));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    router: { get: inertia.ambil, post: vi.fn() },
    usePage: () => ({
        url: '/peluang-instansi',
        props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Siti', email: 's@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import PeluangInstansi from './PeluangInstansi';

function render(isi: ReactNode) {
    return renderPolos(<DialogProvider>{isi}</DialogProvider>);
}

const dasar = {
    item: [instansiUji],
    paginasi: { total: 1, halaman: 1, terakhir: 1, perHalaman: 25 },
    totalInstansi: 1,
    nasional: [],
    ambang: { longgarMaks: 5, ketatMin: 20 },
    tingkat: [{ kode: 'ketat', label: 'Ketat' }],
    filter: { cari: '', tipe: '', tingkat: '', urut: 'rasio_terendah' },
};

describe('PeluangInstansi', () => {
    beforeEach(() => {
        inertia.ambil.mockClear();
    });

    it('menampilkan disclaimer, ambang, dan tautan sumber resmi', () => {
        render(<PeluangInstansi {...dasar} />);

        expect(screen.getAllByText(/bukan prediksi dan bukan jaminan/).length).toBeGreaterThan(0);
        expect(screen.getByRole('list', { name: 'Skala rasio pelamar per formasi' })).toHaveTextContent('Longgar < 5');
        expect(screen.getByRole('list', { name: 'Skala rasio pelamar per formasi' })).toHaveTextContent('Ketat > 20');
        expect(screen.getByRole('link', { name: /sscasn.bkn.go.id/ })).toHaveAttribute('href', 'https://sscasn.bkn.go.id');
        expect(screen.getAllByText('Kementerian Hukum dan Hak Asasi Manusia').length).toBeGreaterThan(0);
    });

    it('menjelaskan bila data statistik belum dimuat', () => {
        render(<PeluangInstansi {...dasar} item={[]} totalInstansi={0} paginasi={{ total: 0, halaman: 1, terakhir: 1, perHalaman: 25 }} />);

        expect(screen.getByText('Data statistik belum dimuat')).toBeInTheDocument();
    });

    it('menjelaskan bila filter tidak menemukan apa pun', () => {
        render(<PeluangInstansi {...dasar} item={[]} paginasi={{ total: 0, halaman: 1, terakhir: 1, perHalaman: 25 }} filter={{ ...dasar.filter, cari: 'zzz' }} />);

        expect(screen.getByText('Tidak ada instansi yang cocok')).toBeInTheDocument();
    });

    it('mengirim filter ke server lewat query string saja', async () => {
        render(<PeluangInstansi {...dasar} />);

        await userEvent.selectOptions(screen.getByLabelText('Tingkat persaingan'), 'ketat');

        expect(inertia.ambil).toHaveBeenCalledWith('/peluang-instansi', { tingkat: 'ketat' }, expect.objectContaining({ preserveState: true }));
    });
});
