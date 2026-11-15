import { render as renderPolos, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { pantauanUji } from '@/features/formasi/data-uji';

const inertia = vi.hoisted(() => ({ ambil: vi.fn() }));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    router: { get: inertia.ambil, post: vi.fn(), delete: vi.fn() },
    useForm: () => ({ data: { catatan: '' }, setData: vi.fn(), put: vi.fn(), errors: {}, processing: false, recentlySuccessful: false }),
    usePage: () => ({
        url: '/pantauan',
        props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Siti', email: 's@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import Index from './Index';

function render(isi: ReactNode) {
    return renderPolos(<DialogProvider>{isi}</DialogProvider>);
}

const dasar = {
    ambang: { longgarMaks: 5, ketatMin: 20 },
    batas: { perbandingan: 4, catatan: 500, pantauan: 100 },
};

function item(jumlah: number) {
    return Array.from({ length: jumlah }, (_, i) => pantauanUji(i + 1));
}

describe('Pantauan/Index', () => {
    beforeEach(() => {
        inertia.ambil.mockClear();
    });

    it('menampilkan keadaan kosong dan mengarahkan ke jelajah formasi', () => {
        render(<Index {...dasar} item={[]} />);

        expect(screen.getByText('Belum ada formasi yang dipantau')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Jelajahi formasi' })).toHaveAttribute('href', '/formasi');
    });

    it('menampilkan jumlah pantauan dan setiap formasi dengan tombol dipantau', () => {
        render(<Index {...dasar} item={item(3)} />);

        expect(screen.getByText('3 dari 100 formasi dipantau')).toBeInTheDocument();
        expect(screen.getAllByRole('button', { name: /^Dipantau Jabatan/ })).toHaveLength(3);
        expect(screen.getAllByLabelText('Catatan pribadi')).toHaveLength(3);
    });

    it('menonaktifkan tombol bandingkan sampai minimal dua formasi dipilih', async () => {
        render(<Index {...dasar} item={item(3)} />);

        const tombol = screen.getByRole('button', { name: 'Bandingkan (0)' });
        expect(tombol).toBeDisabled();

        await userEvent.click(screen.getByLabelText('Pilih Jabatan 1 untuk dibandingkan'));
        expect(screen.getByRole('button', { name: 'Bandingkan (1)' })).toBeDisabled();

        await userEvent.click(screen.getByLabelText('Pilih Jabatan 2 untuk dibandingkan'));
        expect(screen.getByRole('button', { name: 'Bandingkan (2)' })).toBeEnabled();
    });

    it('mengirim formasi terpilih ke halaman perbandingan', async () => {
        render(<Index {...dasar} item={item(3)} />);

        await userEvent.click(screen.getByLabelText('Pilih Jabatan 3 untuk dibandingkan'));
        await userEvent.click(screen.getByLabelText('Pilih Jabatan 1 untuk dibandingkan'));
        await userEvent.click(screen.getByRole('button', { name: 'Bandingkan (2)' }));

        expect(inertia.ambil).toHaveBeenCalledWith('/pantauan/bandingkan', { id: [3, 1] });
    });

    it('membatasi pilihan perbandingan sampai empat formasi', async () => {
        render(<Index {...dasar} item={item(5)} />);

        for (const nomor of [1, 2, 3, 4]) {
            await userEvent.click(screen.getByLabelText(`Pilih Jabatan ${nomor} untuk dibandingkan`));
        }

        expect(screen.getByLabelText('Pilih Jabatan 5 untuk dibandingkan')).toBeDisabled();
        expect(screen.getByLabelText('Pilih Jabatan 1 untuk dibandingkan')).toBeEnabled();
        expect(screen.getByText('Maksimal 4 formasi dibandingkan sekaligus.')).toBeInTheDocument();
    });

    it('memperingatkan formasi yang sudah ditarik dari data BKN', () => {
        render(<Index {...dasar} item={[pantauanUji(1, { status: 'ditarik' })]} />);

        expect(screen.getByText(/tidak lagi tampil di data BKN terbaru/)).toBeInTheDocument();
    });
});
