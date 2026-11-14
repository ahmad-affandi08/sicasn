import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';

const halaman = vi.hoisted(() => ({
    props: {
        whatsappUrl: null as string | null,
        dialog: null,
        aplikasi: { nama: 'SICASN' },
        auth: { pengguna: null as { nama: string; email: string; peran: string; wajibGantiPassword: boolean } | null },
    },
}));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    usePage: () => ({ props: halaman.props }),
}));

import Beranda from './Beranda';

function tampilkan(pembayaran: Parameters<typeof Beranda>[0]['pembayaran'] = null) {
    return render(
        <DialogProvider>
            <Beranda pembayaran={pembayaran} />
        </DialogProvider>,
    );
}

afterEach(() => {
    halaman.props.whatsappUrl = null;
    halaman.props.auth.pengguna = null;
    vi.restoreAllMocks();
});

describe('Beranda', () => {
    it('menampilkan judul utama dan empat langkah akses', () => {
        tampilkan();

        expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Pilih formasi CPNS');
        expect(screen.getAllByRole('listitem').length).toBeGreaterThanOrEqual(4);
        expect(screen.getByText(/bukan situs resmi pemerintah/i)).toBeInTheDocument();
    });

    it('memberi tahu lewat dialog bila nomor WhatsApp admin belum diatur', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getAllByRole('button', { name: /Chat Admin via WhatsApp/i })[0]!);

        expect(await screen.findByRole('alertdialog')).toHaveTextContent('Kontak admin belum diatur');
    });

    it('membuka WhatsApp admin saat nomor sudah diatur', async () => {
        const buka = vi.spyOn(window, 'open').mockReturnValue(null);
        halaman.props.whatsappUrl = 'https://wa.me/6281234567890';
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getAllByRole('button', { name: /Chat Admin via WhatsApp/i })[0]!);

        expect(buka).toHaveBeenCalledWith('https://wa.me/6281234567890', '_blank', 'noopener,noreferrer');
        expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();
    });

    it('menampilkan tombol Masuk untuk tamu dan Buka akun untuk pengguna yang sudah masuk', () => {
        tampilkan();
        expect(screen.getAllByRole('link', { name: 'Masuk' })[0]).toHaveAttribute('href', '/masuk');

        halaman.props.auth.pengguna = { nama: 'Siti', email: 's@contoh.id', peran: 'member', wajibGantiPassword: false };
        tampilkan();
        expect(screen.getAllByRole('link', { name: 'Buka akun' })[0]).toHaveAttribute('href', '/akun');
    });

    it('mengarahkan admin ke dasbor admin', () => {
        halaman.props.auth.pengguna = { nama: 'Admin', email: 'a@contoh.id', peran: 'admin', wajibGantiPassword: false };
        tampilkan();

        expect(screen.getAllByRole('link', { name: 'Buka akun' })[0]).toHaveAttribute('href', '/admin');
    });

    it('menampilkan info paket hanya bila admin sudah mengisinya', () => {
        tampilkan();
        expect(screen.queryByText('Info paket')).not.toBeInTheDocument();
    });

    it('menampilkan harga, catatan, dan rekening bila tersedia', () => {
        tampilkan({ hargaTeks: 'Rp 99.000 / 3 bulan', catatan: 'Aktif setelah transfer.', rekening: [{ bank: 'BCA', nomor: '1234567890', atasNama: 'PT Contoh' }] });

        expect(screen.getByText('Rp 99.000 / 3 bulan')).toBeInTheDocument();
        expect(screen.getByText('1234567890')).toBeInTheDocument();
        expect(screen.getByText('a.n. PT Contoh')).toBeInTheDocument();
    });
});
