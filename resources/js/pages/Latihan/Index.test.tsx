import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import type { InfoMode, ItemRiwayat } from '@/features/latihan/tipe';

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
        url: '/latihan',
        props: { whatsappUrl: null, dialog: null, panduan: false, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Uji', email: 'u@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import Index from './Index';

const mode: InfoMode[] = [
    { nilai: 'simulasi', label: 'Simulasi SKD lengkap', komposisi: { twk: 30, tiu: 35, tkp: 45 } },
    { nilai: 'twk', label: 'Latihan TWK', komposisi: { twk: 20 } },
    { nilai: 'tiu', label: 'Latihan TIU', komposisi: { tiu: 20 } },
    { nilai: 'tkp', label: 'Latihan TKP', komposisi: { tkp: 20 } },
];

const ambang = {
    twk: { resmi: 65, maks: 150, jumlah: 30 },
    tiu: { resmi: 80, maks: 175, jumlah: 35 },
    tkp: { resmi: 166, maks: 225, jumlah: 45 },
};

const riwayat: ItemRiwayat[] = [
    { id: 3, mode: 'simulasi', modeLabel: 'Simulasi SKD lengkap', selesai: '2026-11-14T10:00:00+07:00', jumlahSoal: 110, skor: { twk: 120, tiu: 100, tkp: 180 }, lulus: true },
    { id: 2, mode: 'tiu', modeLabel: 'Latihan TIU', selesai: '2026-11-13T10:00:00+07:00', jumlahSoal: 20, skor: { tiu: 55 }, lulus: null },
];

function tampilkan(ubah: Partial<Parameters<typeof Index>[0]> = {}) {
    return render(
        <DialogProvider>
            <Index ketersediaan={{ twk: 77, tiu: 357, tkp: 57 }} percobaanBerjalanId={null} ambang={ambang} mode={mode} riwayat={[]} {...ubah} />
        </DialogProvider>,
    );
}

beforeEach(() => inertia.kirim.mockClear());

describe('Beranda latihan', () => {
    it('menampilkan keempat mode dengan komposisi dan durasi', () => {
        tampilkan();

        expect(screen.getByRole('heading', { name: 'Simulasi SKD lengkap' })).toBeInTheDocument();
        expect(screen.getByText('30 TWK · 35 TIU · 45 TKP · 100 menit, urutan seperti CAT')).toBeInTheDocument();
        expect(screen.getByText('20 soal intelegensia umum · 30 menit')).toBeInTheDocument();
        expect(screen.getAllByRole('button', { name: 'Mulai' })).toHaveLength(4);
    });

    it('memulai mode yang dipilih lewat permintaan POST', async () => {
        tampilkan();

        const baris = screen.getByRole('heading', { name: 'Latihan TWK' }).closest('li') as HTMLElement;
        await userEvent.click(within(baris).getByRole('button', { name: 'Mulai' }));

        expect(inertia.kirim).toHaveBeenCalledWith('/latihan', { mode: 'twk' });
    });

    it('menonaktifkan mode yang bank soalnya belum tersedia', () => {
        tampilkan({ ketersediaan: { twk: 77, tiu: 0, tkp: 57 } });

        const simulasi = screen.getByRole('heading', { name: 'Simulasi SKD lengkap' }).closest('li') as HTMLElement;
        const tiu = screen.getByRole('heading', { name: 'Latihan TIU' }).closest('li') as HTMLElement;
        const twk = screen.getByRole('heading', { name: 'Latihan TWK' }).closest('li') as HTMLElement;

        expect(within(simulasi).getByRole('button', { name: 'Soal belum tersedia' })).toBeDisabled();
        expect(within(tiu).getByRole('button', { name: 'Soal belum tersedia' })).toBeDisabled();
        expect(within(twk).getByRole('button', { name: 'Mulai' })).toBeEnabled();
    });

    it('mengajak melanjutkan latihan yang berjalan dan mengunci pemulaian baru', () => {
        tampilkan({ percobaanBerjalanId: 9 });

        expect(screen.getByRole('link', { name: 'Lanjutkan latihan' })).toHaveAttribute('href', '/latihan/9');
        screen.getAllByRole('button', { name: 'Mulai' }).forEach((tombol) => expect(tombol).toBeDisabled());
    });

    it('menampilkan ambang resmi, maksimum, dan jumlah soal tersedia', () => {
        tampilkan();

        expect(screen.getByText('Ambang lolos SKD CPNS 2024')).toBeInTheDocument();
        expect(screen.getByText(/Bank soal: 357 soal/)).toBeInTheDocument();
        expect(screen.getByText('166')).toBeInTheDocument();
    });

    it('menyatakan dengan jelas bahwa soal buatan SICASN bukan soal ujian asli', () => {
        tampilkan();

        expect(screen.getByText(/bukan soal ujian asli/)).toBeInTheDocument();
    });

    it('menampilkan keadaan kosong riwayat', () => {
        tampilkan();

        expect(screen.getByText(/Belum ada latihan yang selesai/)).toBeInTheDocument();
    });

    it('menampilkan riwayat dengan skor dan status ambang hanya untuk simulasi', () => {
        tampilkan({ riwayat });

        const daftar = screen.getByRole('list', { name: 'Riwayat latihan' });
        const baris = within(daftar).getAllByRole('listitem');
        expect(baris).toHaveLength(2);
        expect(baris[0]).toHaveTextContent('TWK 120 · TIU 100 · TKP 180');
        expect(baris[0]).toHaveTextContent('Memenuhi ambang');
        expect(baris[1]).toHaveTextContent('TIU 55');
        expect(baris[1]).not.toHaveTextContent('ambang');
        expect(within(baris[0] as HTMLElement).getByRole('link', { name: 'Lihat hasil' })).toHaveAttribute('href', '/latihan/3/hasil');
    });
});
