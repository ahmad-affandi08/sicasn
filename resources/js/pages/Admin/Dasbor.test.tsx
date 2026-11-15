import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import type { EventItem } from '@/types';

const inertia = vi.hoisted(() => ({ kirim: vi.fn() }));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    router: { post: inertia.kirim },
    usePage: () => ({
        url: '/admin',
        props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Admin Uji', email: 'a@uji.test', peran: 'admin', wajibGantiPassword: false } } },
    }),
}));

import Dasbor from './Dasbor';

const kesiapan = { email: true, whatsapp: true, pembayaran: false, sinkron: false };
interface Kesehatan {
    antreanMenunggu: number;
    antreanGagal: number;
    emailGagal: number;
    schedulerAktif: boolean;
    detakTerakhir: string | null;
}

const kesehatanSehat: Kesehatan = { antreanMenunggu: 0, antreanGagal: 0, emailGagal: 0, schedulerAktif: true, detakTerakhir: '2026-11-15T02:00:00+07:00' };

function tampilkan(eventBaru: EventItem[] = [], formasiAktif = 0, kesehatan: Kesehatan = kesehatanSehat) {
    return render(
        <DialogProvider>
            <Dasbor ringkasan={{ aktif: 3, nonaktif: 1 }} kesiapan={kesiapan} sinkron={{ terakhir: null, formasiAktif }} kesehatan={kesehatan} eventBaru={eventBaru} />
        </DialogProvider>,
    );
}

const event: EventItem = {
    eventId: 'evt-1',
    nama: 'Seleksi CPNS 2026',
    jenis: 'cpns',
    jumlahBaris: 120,
    diabaikan: false,
    pertamaKaliTerlihat: '2026-11-15T02:00:00+07:00',
    sudahDiberitahukan: false,
};

beforeEach(() => inertia.kirim.mockClear());
afterEach(() => vi.restoreAllMocks());

describe('Dasbor admin', () => {
    it('menampilkan jumlah member dan keadaan kosong formasi CPNS dengan penjelasan', () => {
        tampilkan();

        expect(screen.getByText('3')).toBeInTheDocument();
        expect(screen.getByText(/Belum ada formasi CPNS/)).toBeInTheDocument();
        expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();
    });

    it('menandai butir kesiapan yang belum siap dan tautan pengaturannya', () => {
        tampilkan();

        expect(screen.getAllByText('Belum siap')).toHaveLength(2);
        expect(screen.getAllByRole('link', { name: 'Atur' }).map((a) => a.getAttribute('href'))).toContain('/admin/sinkron');
    });

    it('menandai penjadwal yang berjalan tanpa peringatan', () => {
        tampilkan();

        expect(screen.getByText('Berjalan')).toBeInTheDocument();
        expect(screen.queryByText(/Penjadwal belum terdeteksi/)).not.toBeInTheDocument();
    });

    it('memperingatkan bila penjadwal mati dan ada email atau antrean yang gagal', () => {
        tampilkan([], 0, { antreanMenunggu: 4, antreanGagal: 2, emailGagal: 1, schedulerAktif: false, detakTerakhir: null });

        expect(screen.getByText('Tidak terdeteksi')).toBeInTheDocument();
        expect(screen.getByText(/Penjadwal belum terdeteksi/)).toBeInTheDocument();
        expect(screen.getByText(/Ada email atau pekerjaan yang gagal/)).toBeInTheDocument();
        expect(screen.getByText('Belum pernah berjalan')).toBeInTheDocument();
    });

    it('menyembunyikan penjelasan kosong saat sudah ada formasi aktif', () => {
        tampilkan([], 1250);

        expect(screen.getByText('1.250')).toBeInTheDocument();
        expect(screen.queryByText(/Belum ada formasi CPNS/)).not.toBeInTheDocument();
    });

    it('menampilkan modal saat event CPNS baru terdeteksi lalu menandainya sudah diberitahukan', async () => {
        const pengguna = userEvent.setup();
        tampilkan([event]);

        const dialog = await screen.findByRole('alertdialog');
        expect(dialog).toHaveTextContent('Event CPNS baru terdeteksi');
        expect(dialog).toHaveTextContent('Seleksi CPNS 2026');
        expect(inertia.kirim).not.toHaveBeenCalled();

        await pengguna.click(screen.getByRole('button', { name: 'Lihat sinkron' }));

        expect(inertia.kirim).toHaveBeenCalledWith('/admin/sinkron/event-diberitahukan', {}, { preserveScroll: true });
    });
});
