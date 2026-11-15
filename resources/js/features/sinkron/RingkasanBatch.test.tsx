import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import type { BatchItem } from '@/types';

import { LencanaBatch } from './LencanaBatch';
import { RingkasanBatch } from './RingkasanBatch';

function batch(ringkasan: BatchItem['ringkasan'], status: BatchItem['status'] = 'sukses'): BatchItem {
    return { id: 1, status, mulaiAt: '2026-11-15T02:00:00+07:00', selesaiAt: '2026-11-15T02:00:11+07:00', ringkasan };
}

describe('RingkasanBatch', () => {
    it('menampilkan angka ringkasan dengan pemisah ribuan dan durasi', () => {
        render(<RingkasanBatch batch={batch({ baris_aktif: 12500, baru: 300, diubah: 5, ditarik: 2, diabaikan: 1754, gagal_baris: 0, durasi_detik: 11 })} />);

        expect(screen.getByText('12.500')).toBeInTheDocument();
        expect(screen.getByText('1.754')).toBeInTheDocument();
        expect(screen.getByText('Selesai dalam 11 detik')).toBeInTheDocument();
    });

    it('menjelaskan sinkron yang dilewati karena tidak ada perubahan', () => {
        render(<RingkasanBatch batch={batch({ dilewati: true })} />);

        expect(screen.getByText('Tidak ada perubahan pada data BKN sejak sinkron sebelumnya.')).toBeInTheDocument();
    });

    it('menampilkan pesan galat sumber bila sinkron gagal', () => {
        render(<RingkasanBatch batch={batch({ galat: 'BKN menolak permintaan (HTTP 403).' }, 'gagal')} />);

        expect(screen.getByText('BKN menolak permintaan (HTTP 403).')).toBeInTheDocument();
    });

    it('memperingatkan admin saat penarikan formasi ditunda', () => {
        render(<RingkasanBatch batch={batch({ baris_aktif: 1, penarikan_ditunda: true })} />);

        expect(screen.getByRole('status')).toHaveTextContent('Penarikan formasi ditunda');
    });
});

describe('LencanaBatch', () => {
    it.each([
        ['berjalan', 'Berjalan'],
        ['sukses', 'Sukses'],
        ['sebagian', 'Sebagian'],
        ['gagal', 'Gagal'],
    ] as const)('menampilkan label untuk status %s', (status, label) => {
        render(<LencanaBatch status={status} />);

        expect(screen.getByText(label)).toBeInTheDocument();
    });
});
