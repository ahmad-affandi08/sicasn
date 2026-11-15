import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import { soalUji } from '@/features/latihan/uji';

import { AreaSoal } from './AreaSoal';

function tampilkan(jawaban = { opsiId: null as number | null, ragu: false }) {
    const aksi = { onPilih: vi.fn(), onRagu: vi.fn(), onKosongkan: vi.fn() };
    render(<AreaSoal soal={soalUji(4)} nomor={4} total={20} jawaban={jawaban} {...aksi} />);

    return aksi;
}

describe('AreaSoal', () => {
    it('menampilkan nomor soal, kategori, pertanyaan, dan lima pilihan', () => {
        tampilkan();

        expect(screen.getByText('Soal 4 dari 20')).toBeInTheDocument();
        expect(screen.getByText('Pertanyaan nomor 4')).toBeInTheDocument();
        expect(screen.getAllByRole('radio')).toHaveLength(5);
        expect(screen.getByRole('radio', { name: /A\. Teks A soal 4/ })).toBeInTheDocument();
    });

    it('memanggil onPilih dengan id opsi yang dipilih', async () => {
        const aksi = tampilkan();

        await userEvent.click(screen.getByRole('radio', { name: /C\. Teks C soal 4/ }));

        expect(aksi.onPilih).toHaveBeenCalledWith(402);
    });

    it('menandai pilihan yang tersimpan', () => {
        tampilkan({ opsiId: 401, ragu: false });

        expect(screen.getByRole('radio', { name: /B\. Teks B soal 4/ })).toBeChecked();
        expect(screen.getByRole('radio', { name: /A\. Teks A soal 4/ })).not.toBeChecked();
    });

    it('menyembunyikan tombol kosongkan bila belum ada jawaban dan menampilkannya setelah dijawab', async () => {
        const { unmount } = render(<AreaSoal soal={soalUji(1)} nomor={1} total={2} jawaban={{ opsiId: null, ragu: false }} onPilih={vi.fn()} onRagu={vi.fn()} onKosongkan={vi.fn()} />);
        expect(screen.queryByRole('button', { name: 'Kosongkan jawaban' })).not.toBeInTheDocument();
        unmount();

        const aksi = tampilkan({ opsiId: 400, ragu: false });
        await userEvent.click(screen.getByRole('button', { name: 'Kosongkan jawaban' }));

        expect(aksi.onKosongkan).toHaveBeenCalled();
    });

    it('mengubah tanda ragu-ragu lewat kotak centang', async () => {
        const aksi = tampilkan();

        await userEvent.click(screen.getByRole('checkbox', { name: 'Ragu-ragu' }));

        expect(aksi.onRagu).toHaveBeenCalledWith(true);
    });

    it('tidak menampilkan kunci jawaban atau pembahasan', () => {
        tampilkan();

        expect(screen.queryByText(/pembahasan/i)).not.toBeInTheDocument();
        expect(screen.queryByText(/skor/i)).not.toBeInTheDocument();
    });
});
