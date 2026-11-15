import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import { soalUji } from '@/features/latihan/uji';

import { PetaSoal } from './PetaSoal';

const soal = [soalUji(1), soalUji(2), soalUji(3)];

describe('PetaSoal', () => {
    it('menjelaskan status tiap soal lewat teks, bukan hanya warna', () => {
        render(<PetaSoal soal={soal} indeks={0} jawaban={{ 10: { opsiId: 100, ragu: false }, 20: { opsiId: 200, ragu: true } }} onPilih={vi.fn()} />);

        expect(screen.getByRole('button', { name: 'Soal 1, dijawab' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Soal 2, dijawab dan ragu-ragu' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Soal 3, belum dijawab' })).toBeInTheDocument();
    });

    it('menandai soal yang sedang dibuka dengan aria-current', () => {
        render(<PetaSoal soal={soal} indeks={1} jawaban={{}} onPilih={vi.fn()} />);

        expect(screen.getByRole('button', { name: /Soal 2/ })).toHaveAttribute('aria-current', 'step');
        expect(screen.getByRole('button', { name: /Soal 1/ })).not.toHaveAttribute('aria-current');
    });

    it('berpindah ke soal yang diklik', async () => {
        const pilih = vi.fn();
        render(<PetaSoal soal={soal} indeks={0} jawaban={{}} onPilih={pilih} />);

        await userEvent.click(screen.getByRole('button', { name: /Soal 3/ }));

        expect(pilih).toHaveBeenCalledWith(2);
    });

    it('menyertakan keterangan simbol', () => {
        render(<PetaSoal soal={soal} indeks={0} jawaban={{}} onPilih={vi.fn()} />);

        expect(screen.getByText(/Dijawab/)).toBeInTheDocument();
        expect(screen.getByText(/Ragu-ragu/)).toBeInTheDocument();
        expect(screen.getByText(/Belum dijawab/)).toBeInTheDocument();
    });
});
