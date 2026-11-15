import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const muat = vi.hoisted(() => ({ opsi: vi.fn() }));

vi.mock('@/lib/opsiRemote', () => ({ muatOpsi: muat.opsi }));

import { FILTER_KOSONG } from './filter';
import { opsiUji } from './data-uji';
import { PanelFilterFormasi } from './PanelFilterFormasi';

const tingkat = [
    { kode: 'longgar', label: 'Longgar' },
    { kode: 'ketat', label: 'Ketat' },
];

describe('PanelFilterFormasi', () => {
    beforeEach(() => {
        muat.opsi.mockReset();
        muat.opsi.mockResolvedValue([]);
    });

    it('memilih beberapa jenjang lalu menerapkan', async () => {
        const terapkan = vi.fn();
        render(<PanelFilterFormasi filter={FILTER_KOSONG} opsi={opsiUji} tingkat={tingkat} onTerapkan={terapkan} />);

        await userEvent.click(screen.getByRole('button', { name: 'S1' }));
        await userEvent.click(screen.getByRole('button', { name: 'D4' }));
        await userEvent.click(screen.getByRole('button', { name: /Terapkan filter/ }));

        expect(terapkan).toHaveBeenCalledWith(expect.objectContaining({ jenjang: ['S1', 'D4'] }));
    });

    it('menandai jenjang terpilih dengan aria-pressed dan bisa dibatalkan', async () => {
        render(<PanelFilterFormasi filter={{ ...FILTER_KOSONG, jenjang: ['S1'] }} opsi={opsiUji} tingkat={tingkat} onTerapkan={vi.fn()} />);

        const tombol = screen.getByRole('button', { name: 'S1' });
        expect(tombol).toHaveAttribute('aria-pressed', 'true');

        await userEvent.click(tombol);

        expect(tombol).toHaveAttribute('aria-pressed', 'false');
    });

    it('menonaktifkan kabupaten/kota sebelum provinsi dipilih', () => {
        render(<PanelFilterFormasi filter={FILTER_KOSONG} opsi={opsiUji} tingkat={tingkat} onTerapkan={vi.fn()} />);

        expect(screen.getByLabelText('Kabupaten/Kota')).toBeDisabled();
    });

    it('memuat kabupaten/kota setelah provinsi dipilih dan mengosongkan pilihan lama', async () => {
        muat.opsi.mockResolvedValue([{ nilai: '77', label: 'Kabupaten Sleman', jumlah: 3 }]);
        const terapkan = vi.fn();
        render(<PanelFilterFormasi filter={FILTER_KOSONG} opsi={opsiUji} tingkat={tingkat} onTerapkan={terapkan} />);

        await userEvent.selectOptions(screen.getByLabelText('Provinsi'), '34');

        expect(muat.opsi).toHaveBeenCalledWith('/formasi/opsi/provinsi/34/wilayah');

        const wilayah = screen.getByLabelText('Kabupaten/Kota');
        expect(await screen.findByRole('option', { name: /Kabupaten Sleman/ })).toBeInTheDocument();

        await userEvent.selectOptions(wilayah, '77');
        await userEvent.click(screen.getByRole('button', { name: /Terapkan filter/ }));

        expect(terapkan).toHaveBeenCalledWith(expect.objectContaining({ provinsi: 34, wilayah: 77 }));
    });

    it('menerapkan tingkat persaingan, urutan, dan opsi sertakan tanpa kualifikasi', async () => {
        const terapkan = vi.fn();
        render(<PanelFilterFormasi filter={FILTER_KOSONG} opsi={opsiUji} tingkat={tingkat} onTerapkan={terapkan} />);

        await userEvent.selectOptions(screen.getByLabelText('Tingkat persaingan'), 'ketat');
        await userEvent.selectOptions(screen.getByLabelText('Urutkan'), 'kuota_terbanyak');
        await userEvent.click(screen.getByRole('switch', { name: /Sertakan formasi tanpa rincian kualifikasi/ }));
        await userEvent.click(screen.getByRole('button', { name: /Terapkan filter/ }));

        expect(terapkan).toHaveBeenCalledWith(expect.objectContaining({ tingkat: 'ketat', urut: 'kuota_terbanyak', sertakan: true }));
    });

    it('menghapus semua filter tetapi mempertahankan kata pencarian', async () => {
        const terapkan = vi.fn();
        render(<PanelFilterFormasi filter={{ ...FILTER_KOSONG, cari: 'perawat', jenjang: ['S1'], tingkat: 'ketat' }} opsi={opsiUji} tingkat={tingkat} onTerapkan={terapkan} />);

        await userEvent.click(screen.getByRole('button', { name: 'Hapus filter' }));

        expect(terapkan).toHaveBeenCalledWith({ ...FILTER_KOSONG, cari: 'perawat' });
    });

    it('menyembunyikan pilihan jenis formasi bila hanya ada satu jenis', () => {
        render(<PanelFilterFormasi filter={FILTER_KOSONG} opsi={{ ...opsiUji, jenisFormasi: [{ nilai: 'UMUM', label: 'UMUM', jumlah: 5 }] }} tingkat={tingkat} onTerapkan={vi.fn()} />);

        expect(screen.queryByLabelText('Jenis formasi')).not.toBeInTheDocument();
    });
});
