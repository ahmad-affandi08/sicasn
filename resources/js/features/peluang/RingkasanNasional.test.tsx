import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import type { AngkaNasionalItem } from '@/types';

import { RingkasanNasional } from './RingkasanNasional';

const angka: AngkaNasionalItem[] = [
    { jenis: 'pelamar', nilai: 3963832, tanggal: '2024-09-17', sumberNama: 'BKN', sumberUrl: 'https://contoh.id/bkn', keyakinan: 'resmi' },
    { jenis: 'submit', nilai: 3572414, tanggal: '2024-09-17', sumberNama: 'detikEdu mengutip BKN', sumberUrl: null, keyakinan: 'turunan' },
];

describe('RingkasanNasional', () => {
    it('menampilkan angka, tanggal, sumber, dan tingkat keyakinan', () => {
        render(<RingkasanNasional angka={angka} />);

        expect(screen.getByText('3.963.832')).toBeInTheDocument();
        expect(screen.getByText('Resmi')).toBeInTheDocument();
        expect(screen.getByText('Kutipan media')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /BKN/ })).toHaveAttribute('href', 'https://contoh.id/bkn');
        expect(screen.getByText(/detikEdu mengutip BKN/)).toBeInTheDocument();
    });

    it('tidak menampilkan kartu bila tidak ada data nasional', () => {
        const { container } = render(<RingkasanNasional angka={[]} />);

        expect(container).toBeEmptyDOMElement();
    });
});
