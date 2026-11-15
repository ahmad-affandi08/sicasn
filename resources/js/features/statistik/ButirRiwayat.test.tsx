import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import type { RiwayatStatistikItem } from '@/types';

const inertia = vi.hoisted(() => ({ kirim: vi.fn() }));

vi.mock('@inertiajs/react', () => ({ router: { post: inertia.kirim } }));

import { ButirRiwayat } from './ButirRiwayat';

const impor: RiwayatStatistikItem = {
    id: 7,
    status: 'sebagian',
    mulaiAt: '2026-11-15T10:00:00+07:00',
    selesaiAt: '2026-11-15T10:00:02+07:00',
    berkas: 'statistik-2024.csv',
    ringkasan: { baris_baru: 12, baris_duplikat: 3, baris_gagal: 2 },
    bisaDibatalkan: true,
};

function tampilkan(data: RiwayatStatistikItem) {
    return render(
        <DialogProvider>
            <ul>
                <ButirRiwayat impor={data} />
            </ul>
        </DialogProvider>,
    );
}

describe('ButirRiwayat', () => {
    beforeEach(() => {
        inertia.kirim.mockClear();
    });

    it('menampilkan angka impor dan tautan unduh baris gagal', () => {
        tampilkan(impor);

        expect(screen.getByText('statistik-2024.csv')).toBeInTheDocument();
        expect(screen.getByText('12')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /Unduh baris gagal/ })).toHaveAttribute('href', '/admin/statistik/7/galat');
    });

    it('meminta konfirmasi modal sebelum rollback dan tidak mengirim bila dibatalkan', async () => {
        tampilkan(impor);

        await userEvent.click(screen.getByRole('button', { name: /Batalkan impor/ }));

        expect(await screen.findByRole('alertdialog')).toBeInTheDocument();
        expect(inertia.kirim).not.toHaveBeenCalled();

        await userEvent.click(screen.getByRole('button', { name: 'Batal' }));

        expect(inertia.kirim).not.toHaveBeenCalled();
    });

    it('mengirim rollback setelah dikonfirmasi', async () => {
        tampilkan(impor);

        await userEvent.click(screen.getByRole('button', { name: /Batalkan impor/ }));
        await userEvent.click(await screen.findByRole('button', { name: 'Ya, batalkan impor' }));

        expect(inertia.kirim).toHaveBeenCalledWith('/admin/statistik/7/rollback', {}, { preserveScroll: true });
    });

    it('menyembunyikan tombol rollback pada impor yang sudah dibatalkan', () => {
        tampilkan({ ...impor, status: 'dibatalkan', bisaDibatalkan: false, ringkasan: { ...impor.ringkasan, baris_dihapus: 12 } });

        expect(screen.queryByRole('button', { name: /Batalkan impor/ })).not.toBeInTheDocument();
        expect(screen.getByText('Dibatalkan')).toBeInTheDocument();
    });
});
