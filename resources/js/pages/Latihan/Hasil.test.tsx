import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { hasilUji } from '@/features/latihan/uji';

const inertia = vi.hoisted(() => ({ kirim: vi.fn() }));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    router: { post: inertia.kirim },
    usePage: () => ({
        url: '/latihan/7/hasil',
        props: { whatsappUrl: null, dialog: null, panduan: false, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Uji', email: 'u@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import Hasil from './Hasil';

function tampilkan(lulus: boolean | null = false) {
    return render(
        <DialogProvider>
            <Hasil hasil={hasilUji(lulus)} />
        </DialogProvider>,
    );
}

beforeEach(() => inertia.kirim.mockClear());

describe('Hasil latihan', () => {
    it('menampilkan skor total terhadap maksimum', () => {
        tampilkan();

        expect(screen.getByText('420')).toBeInTheDocument();
        expect(screen.getByText('dari 550')).toBeInTheDocument();
    });

    it('menyebut tes yang belum memenuhi ambang pada simulasi', () => {
        tampilkan(false);

        expect(screen.getByText('Belum memenuhi ambang pada TIU')).toBeInTheDocument();
    });

    it('menyatakan lulus hanya bila semua tes mencapai ambang', () => {
        tampilkan(true);

        expect(screen.getByText('Memenuhi ambang di ketiga tes')).toBeInTheDocument();
    });

    it('tidak menyatakan lulus atau tidak pada latihan per kategori', () => {
        tampilkan(null);

        expect(screen.queryByText(/ambang di ketiga tes/)).not.toBeInTheDocument();
        expect(screen.queryByText(/Belum memenuhi ambang pada/)).not.toBeInTheDocument();
    });

    it('menampilkan tabel skor per tes dengan status yang ditulis, bukan hanya diwarnai', () => {
        tampilkan();

        const tabel = screen.getByRole('table');
        const baris = within(tabel).getAllByRole('row');
        expect(baris[1]).toHaveTextContent('TWK');
        expect(baris[1]).toHaveTextContent('Tercapai');
        expect(baris[2]).toHaveTextContent('TIU');
        expect(baris[2]).toHaveTextContent('Belum tercapai');
    });

    it('menampilkan pembahasan, kunci, pilihan pengguna, dan sumber tiap soal', () => {
        tampilkan();

        expect(screen.getByText('Pembahasan soal 2')).toBeInTheDocument();
        expect(screen.getAllByText('Kunci jawaban').length).toBeGreaterThan(0);
        expect(screen.getAllByText('Pilihan Anda').length).toBe(2);
        expect(screen.getAllByText(/Soal asli SICASN/).length).toBeGreaterThan(0);
    });

    it('menyaring soal berdasarkan status', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: /^Salah \(1\)$/ }));
        expect(screen.getByText('Pertanyaan hasil 2')).toBeInTheDocument();
        expect(screen.queryByText('Pertanyaan hasil 1')).not.toBeInTheDocument();

        await pengguna.click(screen.getByRole('button', { name: /^Kosong \(1\)$/ }));
        expect(screen.getByText('Pertanyaan hasil 3')).toBeInTheDocument();
        expect(screen.queryByText('Pertanyaan hasil 2')).not.toBeInTheDocument();

        await pengguna.click(screen.getByRole('button', { name: /^Semua soal \(3\)$/ }));
        expect(screen.getByText('Pertanyaan hasil 1')).toBeInTheDocument();
    });

    it('menandai tombol filter yang aktif dengan aria-pressed', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: /^Benar/ }));

        expect(screen.getByRole('button', { name: /^Benar/ })).toHaveAttribute('aria-pressed', 'true');
        expect(screen.getByRole('button', { name: /^Semua soal/ })).toHaveAttribute('aria-pressed', 'false');
    });

    it('mengulang latihan dengan mode yang sama', async () => {
        tampilkan(true);

        await userEvent.click(screen.getByRole('button', { name: 'Latihan lagi' }));

        expect(inertia.kirim).toHaveBeenCalledWith('/latihan', { mode: 'simulasi' });
    });

    it('menyertakan catatan bahwa skor bukan prediksi', () => {
        tampilkan();

        expect(screen.getByText(/bukan prediksi hasil ujian sebenarnya/)).toBeInTheDocument();
    });
});
