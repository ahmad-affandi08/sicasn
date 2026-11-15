import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import { PanelFilter } from './PanelFilter';

const tingkat = [
    { kode: 'longgar', label: 'Longgar' },
    { kode: 'ketat', label: 'Ketat' },
];
const awal = { cari: '', tipe: '', tingkat: '', urut: 'rasio_terendah' };

describe('PanelFilter', () => {
    it('mengirim kata kunci saat form dikirim', async () => {
        const terapkan = vi.fn();
        render(<PanelFilter filter={awal} tingkat={tingkat} onTerapkan={terapkan} />);

        await userEvent.type(screen.getByRole('searchbox', { name: 'Cari nama instansi' }), 'agama');
        await userEvent.click(screen.getByRole('button', { name: 'Cari' }));

        expect(terapkan).toHaveBeenCalledWith({ ...awal, cari: 'agama' });
    });

    it('langsung menerapkan saat pilihan tingkat diganti dan mempertahankan kata kunci', async () => {
        const terapkan = vi.fn();
        render(<PanelFilter filter={{ ...awal, cari: 'bkn' }} tingkat={tingkat} onTerapkan={terapkan} />);

        await userEvent.selectOptions(screen.getByLabelText('Tingkat persaingan'), 'ketat');

        expect(terapkan).toHaveBeenCalledWith({ cari: 'bkn', tipe: '', tingkat: 'ketat', urut: 'rasio_terendah' });
    });

    it('menghapus semua filter', async () => {
        const terapkan = vi.fn();
        render(<PanelFilter filter={{ cari: 'bkn', tipe: 'pusat', tingkat: 'ketat', urut: 'nama' }} tingkat={tingkat} onTerapkan={terapkan} />);

        await userEvent.click(screen.getByRole('button', { name: 'Hapus filter' }));

        expect(terapkan).toHaveBeenCalledWith(awal);
    });

    it('menampilkan jumlah filter aktif pada tombol untuk layar kecil', () => {
        render(<PanelFilter filter={{ cari: '', tipe: 'pusat', tingkat: 'ketat', urut: 'rasio_terendah' }} tingkat={tingkat} onTerapkan={vi.fn()} />);

        expect(screen.getByRole('button', { name: 'Filter (2)' })).toBeInTheDocument();
    });

    it('membuka dan menutup opsi filter di layar kecil lewat tombol dengan aria-expanded', async () => {
        render(<PanelFilter filter={awal} tingkat={tingkat} onTerapkan={vi.fn()} />);

        const tombol = screen.getByRole('button', { name: 'Filter' });
        expect(tombol).toHaveAttribute('aria-expanded', 'false');

        await userEvent.click(tombol);

        expect(tombol).toHaveAttribute('aria-expanded', 'true');
    });
});
