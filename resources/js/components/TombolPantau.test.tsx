import { render as renderPolos, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';

const inertia = vi.hoisted(() => ({ kirim: vi.fn(), hapus: vi.fn() }));

vi.mock('@inertiajs/react', () => ({ router: { post: inertia.kirim, delete: inertia.hapus } }));

import { TombolPantau } from './TombolPantau';

function render(isi: ReactNode) {
    return renderPolos(<DialogProvider>{isi}</DialogProvider>);
}

describe('TombolPantau', () => {
    beforeEach(() => {
        inertia.kirim.mockClear();
        inertia.hapus.mockClear();
    });

    it('memantau formasi tanpa konfirmasi dan memuat ulang hanya prop yang perlu', async () => {
        render(<TombolPantau formasiId={7} jabatan="Perawat" dipantau={false} />);

        const tombol = screen.getByRole('button', { name: 'Pantau Perawat' });
        expect(tombol).toHaveAttribute('aria-pressed', 'false');

        await userEvent.click(tombol);

        expect(inertia.kirim).toHaveBeenCalledWith('/pantauan', { formasi_id: 7 }, expect.objectContaining({ preserveScroll: true, only: ['dipantau', 'dialog', 'errors'] }));
        expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();
    });

    it('meminta konfirmasi berisiko sebelum menghentikan pantauan', async () => {
        render(<TombolPantau formasiId={7} jabatan="Perawat" dipantau />);

        const tombol = screen.getByRole('button', { name: 'Dipantau Perawat' });
        expect(tombol).toHaveAttribute('aria-pressed', 'true');

        await userEvent.click(tombol);

        expect(await screen.findByRole('alertdialog')).toHaveTextContent('Perawat akan dihapus dari daftar pantauan Anda, termasuk catatan pribadinya.');
        expect(inertia.hapus).not.toHaveBeenCalled();
    });

    it('tidak menghapus bila konfirmasi dibatalkan', async () => {
        render(<TombolPantau formasiId={7} jabatan="Perawat" dipantau />);

        await userEvent.click(screen.getByRole('button', { name: 'Dipantau Perawat' }));
        await userEvent.click(await screen.findByRole('button', { name: 'Batal' }));

        expect(inertia.hapus).not.toHaveBeenCalled();
    });

    it('menghapus setelah dikonfirmasi', async () => {
        render(<TombolPantau formasiId={7} jabatan="Perawat" dipantau muatUlang={['item']} />);

        await userEvent.click(screen.getByRole('button', { name: 'Dipantau Perawat' }));
        await userEvent.click(await screen.findByRole('button', { name: 'Ya, hentikan' }));

        expect(inertia.hapus).toHaveBeenCalledWith('/pantauan/7', expect.objectContaining({ only: ['item', 'dialog', 'errors'] }));
    });
});
