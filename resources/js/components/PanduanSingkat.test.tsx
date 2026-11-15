import { render as renderBiasa, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useEffect, type ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { useDialog } from '@/components/dialog/konteks';

const inertia = vi.hoisted(() => ({ kirim: vi.fn(), panduan: true }));

vi.mock('@inertiajs/react', () => ({
    router: { post: inertia.kirim },
    usePage: () => ({ props: { panduan: inertia.panduan } }),
}));

import { PanduanSingkat } from './PanduanSingkat';

function render(isi: ReactNode) {
    return renderBiasa(<DialogProvider>{isi}</DialogProvider>);
}

function DialogAwal() {
    const dialog = useDialog();

    useEffect(() => {
        void dialog.sukses({ judul: 'Kata sandi diganti', pesan: 'Gunakan kata sandi baru saat masuk berikutnya.' });
    }, [dialog]);

    return null;
}

beforeEach(() => {
    inertia.kirim.mockClear();
    inertia.panduan = true;
});

describe('PanduanSingkat', () => {
    it('terbuka otomatis pada langkah pertama saat panduan belum dilihat', async () => {
        render(<PanduanSingkat />);

        const dialog = await screen.findByRole('alertdialog');
        expect(dialog).toHaveTextContent('Langkah 1 dari 3');
        expect(dialog).toHaveTextContent('Mulai dari peluang instansi');
    });

    it('memindahkan fokus keyboard ke dalam panduan saat terbuka', async () => {
        render(<PanduanSingkat />);

        await screen.findByRole('alertdialog');
        await waitFor(() => expect(screen.getByRole('alertdialog')).toContainElement(document.activeElement as HTMLElement));
    });

    it('berpindah antar langkah dan menandai selesai di langkah terakhir', async () => {
        const pengguna = userEvent.setup();
        render(<PanduanSingkat />);
        await screen.findByRole('alertdialog');

        await pengguna.click(screen.getByRole('button', { name: 'Berikutnya' }));
        expect(screen.getByRole('alertdialog')).toHaveTextContent('Saring formasi dengan filter');

        await pengguna.click(screen.getByRole('button', { name: 'Berikutnya' }));
        expect(screen.getByRole('alertdialog')).toHaveTextContent('Pantau dan bandingkan');
        expect(screen.queryByRole('button', { name: 'Berikutnya' })).not.toBeInTheDocument();

        await pengguna.click(screen.getByRole('button', { name: 'Mulai menjelajah' }));

        expect(inertia.kirim).toHaveBeenCalledWith('/akun/panduan-selesai', {}, { preserveScroll: true, preserveState: true });
        expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();
    });

    it('bisa dilewati dari langkah pertama dan tetap tercatat sudah dilihat', async () => {
        const pengguna = userEvent.setup();
        render(<PanduanSingkat />);
        await screen.findByRole('alertdialog');

        await pengguna.click(screen.getByRole('button', { name: 'Lewati' }));

        expect(inertia.kirim).toHaveBeenCalledTimes(1);
        expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();
    });

    it('bisa kembali ke langkah sebelumnya', async () => {
        const pengguna = userEvent.setup();
        render(<PanduanSingkat />);
        await screen.findByRole('alertdialog');

        await pengguna.click(screen.getByRole('button', { name: 'Berikutnya' }));
        await pengguna.click(screen.getByRole('button', { name: 'Sebelumnya' }));

        expect(screen.getByRole('alertdialog')).toHaveTextContent('Langkah 1 dari 3');
    });

    it('tertutup bila sudah pernah dilihat dan dibuka lagi dari tombol bantuan tanpa mencatat ulang', async () => {
        inertia.panduan = false;
        const pengguna = userEvent.setup();
        render(<PanduanSingkat />);

        expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();

        await pengguna.click(screen.getByRole('button', { name: 'Buka panduan singkat' }));
        expect(screen.getByRole('alertdialog')).toHaveTextContent('Langkah 1 dari 3');

        await pengguna.click(screen.getByRole('button', { name: 'Lewati' }));
        expect(inertia.kirim).not.toHaveBeenCalled();
    });

    it('menutup dengan tombol Esc dan tetap mencatat bila panduan pertama', async () => {
        const pengguna = userEvent.setup();
        render(<PanduanSingkat />);
        await screen.findByRole('alertdialog');

        await pengguna.keyboard('{Escape}');

        expect(inertia.kirim).toHaveBeenCalledTimes(1);
    });

    it('menunggu dialog lain selesai dulu agar dua modal tidak menumpuk', async () => {
        const pengguna = userEvent.setup();
        render(
            <>
                <DialogAwal />
                <PanduanSingkat />
            </>,
        );

        const pertama = await screen.findByRole('alertdialog');
        expect(pertama).toHaveTextContent('Kata sandi diganti');
        expect(screen.getAllByRole('alertdialog')).toHaveLength(1);

        await pengguna.click(screen.getByRole('button', { name: 'Mengerti' }));

        const kedua = await screen.findByRole('alertdialog');
        expect(kedua).toHaveTextContent('Langkah 1 dari 3');
        expect(screen.getAllByRole('alertdialog')).toHaveLength(1);
    });
});
