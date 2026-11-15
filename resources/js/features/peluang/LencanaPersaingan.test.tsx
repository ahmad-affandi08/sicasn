import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import type { TingkatPersainganKode } from '@/types';

import { LencanaPersaingan } from './LencanaPersaingan';

describe('LencanaPersaingan', () => {
    it.each<[TingkatPersainganKode, string]>([
        ['longgar', 'Longgar'],
        ['sedang', 'Sedang'],
        ['ketat', 'Ketat'],
        ['belum_final', 'Belum final'],
        ['belum_ada_data', 'Belum ada data'],
    ])('menampilkan teks %s sehingga status tidak hanya lewat warna', (tingkat, teks) => {
        render(<LencanaPersaingan tingkat={tingkat} />);

        expect(screen.getByText(teks)).toBeInTheDocument();
    });

    it('menjelaskan arti lencana lewat title', () => {
        render(<LencanaPersaingan tingkat="belum_final" />);

        expect(screen.getByTitle('Angka masih sementara, persaingan sebenarnya bisa lebih ketat')).toBeInTheDocument();
    });
});
