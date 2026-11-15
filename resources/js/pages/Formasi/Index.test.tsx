import { render as renderPolos, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { formasiUji, opsiUji } from '@/features/formasi/data-uji';
import { FILTER_KOSONG } from '@/features/formasi/filter';

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
        url: '/formasi',
        props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Siti', email: 's@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import Index from './Index';

function render(isi: ReactNode) {
    return renderPolos(<DialogProvider>{isi}</DialogProvider>);
}

const dasar = {
    item: [formasiUji],
    paginasi: { total: 1, halaman: 1, terakhir: 1, perHalaman: 20 },
    totalAktif: 1,
    ambang: { longgarMaks: 5, ketatMin: 20 },
    dipantau: [] as number[],
    tingkat: [{ kode: 'ketat', label: 'Ketat' }],
    opsi: opsiUji,
    filter: FILTER_KOSONG,
};

describe('Formasi/Index', () => {
    beforeEach(() => {
        inertia.ambil.mockClear();
    });

    it('menampilkan daftar formasi dengan disclaimer dan tautan sumber resmi', () => {
        render(<Index {...dasar} />);

        expect(screen.getByRole('heading', { name: 'Penghulu Ahli Pertama' })).toBeInTheDocument();
        expect(screen.getByText(/SICASN bukan portal resmi pemerintah/)).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /Buka sscasn.bkn.go.id/ })).toHaveAttribute('href', 'https://sscasn.bkn.go.id');
        expect(screen.getByText(/Menampilkan 1 dari 1 formasi/)).toBeInTheDocument();
    });

    it('menampilkan keadaan kosong CPNS dan mengarahkan ke Peluang Instansi', () => {
        render(<Index {...dasar} item={[]} totalAktif={0} paginasi={{ total: 0, halaman: 1, terakhir: 1, perHalaman: 20 }} />);

        expect(screen.getByText('Formasi CPNS belum dibuka BKN')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Lihat Peluang Instansi' })).toHaveAttribute('href', '/peluang-instansi');
        expect(screen.queryByRole('search')).not.toBeInTheDocument();
    });

    it('menjelaskan bila filter tidak menemukan formasi', () => {
        render(<Index {...dasar} item={[]} paginasi={{ total: 0, halaman: 1, terakhir: 1, perHalaman: 20 }} filter={{ ...FILTER_KOSONG, cari: 'zzz' }} />);

        expect(screen.getByText('Tidak ada formasi yang cocok')).toBeInTheDocument();
    });

    it('mengirim pencarian ke server dengan mempertahankan filter lain di URL', async () => {
        render(<Index {...dasar} filter={{ ...FILTER_KOSONG, jenjang: ['S1'] }} />);

        await userEvent.type(screen.getByRole('searchbox', { name: 'Cari jabatan atau instansi' }), 'perawat');
        await userEvent.click(screen.getByRole('button', { name: 'Cari' }));

        expect(inertia.ambil).toHaveBeenCalledWith('/formasi', { cari: 'perawat', jenjang: ['S1'] }, expect.objectContaining({ preserveState: true }));
    });

    it('menampilkan tombol filter dengan penghitung di layar kecil', () => {
        render(<Index {...dasar} filter={{ ...FILTER_KOSONG, jenjang: ['S1'], tingkat: 'ketat' }} />);

        expect(screen.getByRole('button', { name: 'Filter (2)' })).toBeInTheDocument();
    });

    it('membuka panel filter di layar kecil', async () => {
        render(<Index {...dasar} />);

        await userEvent.click(screen.getByRole('button', { name: 'Filter' }));

        expect(await screen.findByRole('dialog')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Terapkan filter' })).toBeInTheDocument();
    });

    it('mengurutkan lewat pilihan di toolbar daftar dan menyimpannya di URL', async () => {
        render(<Index {...dasar} />);

        await userEvent.selectOptions(screen.getByLabelText('Urutkan'), 'kuota_terbanyak');

        expect(inertia.ambil).toHaveBeenCalledWith('/formasi', { urut: 'kuota_terbanyak' }, expect.objectContaining({ preserveState: true }));
    });
});
