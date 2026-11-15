import { render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';

const halaman = vi.hoisted(() => ({ pengguna: null as null | { peran: 'admin' | 'member' } }));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    router: { post: vi.fn() },
    usePage: () => ({ url: '/', props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: halaman.pengguna } } }),
}));

import { DialogProvider } from '@/components/dialog/DialogProvider';

import Galat from './Galat';

function tampilkan(kode: 403 | 404 | 500) {
    return render(
        <DialogProvider>
            <Galat kode={kode} />
        </DialogProvider>,
    );
}

describe('Halaman galat', () => {
    it('menjelaskan 404 dengan bahasa awam dan mengarah ke beranda untuk tamu', () => {
        halaman.pengguna = null;
        tampilkan(404);

        expect(screen.getByRole('heading', { level: 1, name: 'Halaman tidak ditemukan' })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Kembali ke beranda' })).toHaveAttribute('href', '/');
    });

    it('mengarahkan member yang sudah masuk ke peluang instansi', () => {
        halaman.pengguna = { peran: 'member' };
        tampilkan(403);

        expect(screen.getByRole('heading', { level: 1, name: 'Halaman ini bukan untuk akun Anda' })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Kembali ke halaman utama' })).toHaveAttribute('href', '/peluang-instansi');
    });

    it('mengarahkan admin ke dasbor dan tidak membocorkan detail teknis pada 500', () => {
        halaman.pengguna = { peran: 'admin' };
        tampilkan(500);

        expect(screen.getByText('Terjadi gangguan di server')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Kembali ke halaman utama' })).toHaveAttribute('href', '/admin');
    });
});
