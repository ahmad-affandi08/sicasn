import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import { PenghitungWaktu } from './PenghitungWaktu';

describe('PenghitungWaktu', () => {
    it('menampilkan menit dan detik', () => {
        render(<PenghitungWaktu sisaDetik={605} />);

        expect(screen.getByRole('timer')).toHaveTextContent('10:05');
    });

    it('menampilkan jam bila lebih dari satu jam', () => {
        render(<PenghitungWaktu sisaDetik={3725} />);

        expect(screen.getByRole('timer')).toHaveTextContent('1:02:05');
    });

    it('menampilkan nol saat waktu habis', () => {
        render(<PenghitungWaktu sisaDetik={0} />);

        expect(screen.getByRole('timer')).toHaveTextContent('00:00');
    });

    it('memberi pengumuman sisa waktu hanya pada menit tertentu', () => {
        const { container, rerender } = render(<PenghitungWaktu sisaDetik={300} />);
        expect(container.querySelector('[aria-live=polite]')).toHaveTextContent('Sisa waktu 5 menit.');

        rerender(<PenghitungWaktu sisaDetik={299} />);
        expect(container.querySelector('[aria-live=polite]')).toBeEmptyDOMElement();

        rerender(<PenghitungWaktu sisaDetik={60} />);
        expect(container.querySelector('[aria-live=polite]')).toHaveTextContent('Sisa waktu 1 menit.');
    });

    it('menyediakan label tersembunyi untuk pembaca layar', () => {
        render(<PenghitungWaktu sisaDetik={120} />);

        expect(screen.getByText(/Sisa waktu:/)).toHaveClass('sr-only');
    });
});
