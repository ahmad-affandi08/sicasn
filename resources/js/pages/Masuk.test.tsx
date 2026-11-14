import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';

vi.mock('@inertiajs/react', async (impor) => {
    const asli = await impor<typeof import('@inertiajs/react')>();

    return {
        ...asli,
        Head: () => null,
        Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
            <a href={href} {...sisa}>
                {children}
            </a>
        ),
        usePage: () => ({ props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: null } } }),
    };
});

import Masuk from './Masuk';

function tampilkan() {
    return render(
        <DialogProvider>
            <Masuk />
        </DialogProvider>,
    );
}

describe('Masuk', () => {
    it('memiliki kolom email dan kata sandi berlabel serta tombol masuk', () => {
        tampilkan();

        expect(screen.getByLabelText('Email')).toBeInTheDocument();
        expect(screen.getByLabelText('Kata sandi')).toHaveAttribute('type', 'password');
        expect(screen.getByRole('button', { name: 'Masuk' })).toBeInTheDocument();
    });

    it('tidak menyediakan tautan pendaftaran, hanya petunjuk cara mendapatkan akses', () => {
        tampilkan();

        expect(screen.queryByRole('link', { name: /daftar/i })).not.toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Lihat cara mendapatkan akses' })).toHaveAttribute('href', '/#cara-akses');
    });

    it('menampilkan dan menyembunyikan kata sandi lewat tombol mata', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'Tampilkan kata sandi' }));
        expect(screen.getByLabelText('Kata sandi')).toHaveAttribute('type', 'text');

        await pengguna.click(screen.getByRole('button', { name: 'Sembunyikan kata sandi' }));
        expect(screen.getByLabelText('Kata sandi')).toHaveAttribute('type', 'password');
    });
});
