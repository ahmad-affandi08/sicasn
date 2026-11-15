import { render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';

import { instansiUji } from './data-uji';
import { RincianInstansi } from './RincianInstansi';

describe('RincianInstansi', () => {
    it('tidak menampilkan apa pun saat tidak ada instansi dipilih', () => {
        render(<RincianInstansi instansi={null} onTutup={vi.fn()} />);

        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
    });

    it('menampilkan seluruh riwayat snapshot dengan sumber, status, dan tingkat data', () => {
        render(<RincianInstansi instansi={instansiUji} onTutup={vi.fn()} />);

        expect(screen.getByRole('dialog')).toBeInTheDocument();
        expect(screen.getByText('17 September 2024')).toBeInTheDocument();
        expect(screen.getByText('29 Agustus 2024')).toBeInTheDocument();
        expect(screen.getByText('Akhir')).toBeInTheDocument();
        expect(screen.getByText('Sementara')).toBeInTheDocument();
        expect(screen.getAllByText('Tingkat data A')).toHaveLength(2);
        expect(screen.getByText(/Liputan6 mengutip BKN/)).toBeInTheDocument();
        expect(screen.getByText(/Media Awal/)).toBeInTheDocument();
    });

    it('menautkan sumber ke tab baru tanpa referrer', () => {
        render(<RincianInstansi instansi={instansiUji} onTutup={vi.fn()} />);

        const tautan = screen.getByRole('link', { name: /Sumber/ });

        expect(tautan).toHaveAttribute('href', 'https://contoh.id/sumber');
        expect(tautan).toHaveAttribute('target', '_blank');
        expect(tautan).toHaveAttribute('rel', 'noopener noreferrer');
    });
});
