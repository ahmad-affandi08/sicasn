import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { describe, expect, it, vi } from 'vitest';

import type { OpsiPilihan } from '@/types';

import { Combobox } from './Combobox';

const semua: OpsiPilihan[] = [
    { nilai: '1', label: 'HUKUM', jumlah: 0 },
    { nilai: '2', label: 'STATISTIKA', jumlah: 0 },
    { nilai: '3', label: 'TEKNIK INFORMATIKA', jumlah: 0 },
];

function Contoh({ banyak = false, muat }: { banyak?: boolean; muat?: (kataKunci: string) => Promise<OpsiPilihan[]> }) {
    const [terpilih, setTerpilih] = useState<OpsiPilihan[]>([]);
    const cari = muat ?? ((kataKunci: string) => Promise.resolve(semua.filter((butir) => butir.label.includes(kataKunci.toUpperCase()))));

    return <Combobox id="uji" label="Jurusan" placeholder="Ketik jurusan" banyak={banyak} terpilih={terpilih} muatOpsi={cari} onUbah={setTerpilih} jeda={0} />;
}

describe('Combobox', () => {
    it('memakai pola ARIA combobox dan listbox', async () => {
        render(<Contoh />);

        const kotak = screen.getByRole('combobox', { name: 'Jurusan' });
        expect(kotak).toHaveAttribute('aria-expanded', 'false');

        await userEvent.click(kotak);

        expect(kotak).toHaveAttribute('aria-expanded', 'true');
        expect(await screen.findByRole('listbox', { name: 'Jurusan' })).toBeInTheDocument();
        expect(await screen.findAllByRole('option')).toHaveLength(3);
    });

    it('menyaring pilihan lewat pencarian jarak jauh', async () => {
        render(<Contoh />);

        await userEvent.type(screen.getByRole('combobox'), 'stat');

        await waitFor(() => expect(screen.getAllByRole('option')).toHaveLength(1));
        expect(screen.getByRole('option', { name: 'STATISTIKA' })).toBeInTheDocument();
    });

    it('memilih satu pilihan dan menyembunyikan kotak ketik pada mode tunggal', async () => {
        render(<Contoh />);

        await userEvent.click(screen.getByRole('combobox'));
        await userEvent.click(await screen.findByRole('option', { name: 'HUKUM' }));

        expect(screen.queryByRole('combobox')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Hapus HUKUM' })).toBeInTheDocument();

        await userEvent.click(screen.getByRole('button', { name: 'Hapus HUKUM' }));

        expect(screen.getByRole('combobox')).toBeInTheDocument();
    });

    it('mendukung banyak pilihan dan tidak menawarkan lagi yang sudah dipilih', async () => {
        render(<Contoh banyak />);

        await userEvent.click(screen.getByRole('combobox'));
        await userEvent.click(await screen.findByRole('option', { name: 'HUKUM' }));
        await userEvent.click(await screen.findByRole('option', { name: 'STATISTIKA' }));

        expect(screen.getByRole('button', { name: 'Hapus HUKUM' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Hapus STATISTIKA' })).toBeInTheDocument();
        expect(screen.queryByRole('option', { name: 'HUKUM' })).not.toBeInTheDocument();
        expect(screen.getByRole('option', { name: 'TEKNIK INFORMATIKA' })).toBeInTheDocument();
    });

    it('dapat dioperasikan dengan keyboard', async () => {
        render(<Contoh banyak />);

        const kotak = screen.getByRole('combobox');
        await userEvent.click(kotak);
        await screen.findAllByRole('option');
        await userEvent.keyboard('{ArrowDown}{Enter}');

        expect(screen.getByRole('button', { name: 'Hapus STATISTIKA' })).toBeInTheDocument();

        await userEvent.keyboard('{Backspace}');

        expect(screen.queryByRole('button', { name: 'Hapus STATISTIKA' })).not.toBeInTheDocument();

        await userEvent.keyboard('{Escape}');

        expect(kotak).toHaveAttribute('aria-expanded', 'false');
    });

    it('menampilkan pesan bila tidak ada pilihan yang cocok', async () => {
        render(<Contoh />);

        await userEvent.type(screen.getByRole('combobox'), 'zzz');

        expect(await screen.findByText('Tidak ada pilihan yang cocok')).toBeInTheDocument();
    });

    it('menampilkan pesan galat bila pilihan gagal dimuat', async () => {
        render(<Contoh muat={() => Promise.reject(new Error('gagal'))} />);

        await userEvent.click(screen.getByRole('combobox'));

        expect(await screen.findByText('Pilihan tidak dapat dimuat. Coba lagi.')).toBeInTheDocument();
    });

    it('menutup daftar saat mengklik di luar', async () => {
        render(
            <div>
                <Contoh />
                <button type="button">luar</button>
            </div>,
        );

        const kotak = screen.getByRole('combobox');
        await userEvent.click(kotak);
        await userEvent.click(screen.getByRole('button', { name: 'luar' }));

        expect(kotak).toHaveAttribute('aria-expanded', 'false');
        expect(vi.isMockFunction(kotak)).toBe(false);
    });
});
