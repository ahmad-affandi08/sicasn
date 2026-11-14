import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';

const halaman = vi.hoisted(() => ({
    props: { whatsappUrl: null as string | null, dialog: null, aplikasi: { nama: 'SICASN' } },
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

function tampilkan() {
    return render(
        <DialogProvider>
            <Beranda />
        </DialogProvider>,
    );
}

afterEach(() => {
    halaman.props.whatsappUrl = null;
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
});
