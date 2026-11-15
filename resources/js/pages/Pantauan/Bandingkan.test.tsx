import { render as renderPolos, screen, within } from '@testing-library/react';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { pantauanUji, peluangJabatanUji } from '@/features/formasi/data-uji';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...sisa }: { href: string; children: ReactNode }) => (
        <a href={href} {...sisa}>
            {children}
        </a>
    ),
    router: { get: vi.fn(), post: vi.fn() },
    usePage: () => ({
        url: '/pantauan/bandingkan',
        props: { whatsappUrl: null, dialog: null, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Siti', email: 's@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import Bandingkan from './Bandingkan';

function render(isi: ReactNode) {
    return renderPolos(<DialogProvider>{isi}</DialogProvider>);
}

const ambang = { longgarMaks: 5, ketatMin: 20 };

describe('Pantauan/Bandingkan', () => {
    it('menampilkan satu kolom per formasi dengan tautan detail', () => {
        render(<Bandingkan ambang={ambang} item={[pantauanUji(1), pantauanUji(2, { kuota: 12 })]} />);

        const tabel = screen.getByRole('table', { name: 'Perbandingan Formasi' });

        expect(within(tabel).getByRole('columnheader', { name: /Jabatan 1/ })).toBeInTheDocument();
        expect(within(tabel).getByRole('columnheader', { name: /Jabatan 2/ })).toBeInTheDocument();
        expect(within(tabel).getAllByRole('link', { name: 'Lihat detail' }).map((a) => a.getAttribute('href'))).toEqual(['/formasi/1', '/formasi/2']);
    });

    it('membandingkan atribut utama baris demi baris', () => {
        render(<Bandingkan ambang={ambang} item={[pantauanUji(1), pantauanUji(2, { kuota: 12 })]} />);

        for (const judul of ['Lokasi', 'Kuota', 'Batas usia', 'Jenis formasi', 'Syarat pendidikan', 'Persaingan instansi', 'Persaingan per jabatan', 'Catatan Anda']) {
            expect(screen.getByRole('rowheader', { name: judul })).toBeInTheDocument();
        }

        expect(screen.getByText('12')).toBeInTheDocument();
    });

    it('menampilkan persaingan instansi dengan sumber dan label rata-rata', () => {
        render(<Bandingkan ambang={ambang} item={[pantauanUji(1), pantauanUji(2)]} />);

        expect(screen.getAllByText('Ketat')).toHaveLength(2);
        expect(screen.getAllByText(/Liputan6 mengutip BKN/).length).toBe(2);
        expect(screen.getAllByText(/^Rata-rata instansi/).length).toBe(2);
    });

    it('menampilkan data per jabatan bila ada dan penjelasan bila tidak ada', () => {
        render(<Bandingkan ambang={ambang} item={[pantauanUji(1, { peluangJabatan: peluangJabatanUji }), pantauanUji(2)]} />);

        expect(screen.getByText('Sedang')).toBeInTheDocument();
        expect(screen.getByText(/Lolos administrasi · 100/)).toBeInTheDocument();
        expect(screen.getByText('Belum ada data per jabatan')).toBeInTheDocument();
    });

    it('tidak menebak persaingan bila instansi tidak punya data', () => {
        render(<Bandingkan ambang={ambang} item={[pantauanUji(1, { peluang: null }), pantauanUji(2)]} />);

        expect(screen.getByText('Belum ada data')).toBeInTheDocument();
    });

    it('menampilkan catatan pribadi atau penjelasan bila kosong dan kualifikasi kosong', () => {
        render(<Bandingkan ambang={ambang} item={[pantauanUji(1, { catatan: 'Tanya teman' }), pantauanUji(2, { kualifikasiTersedia: false, kualifikasi: null })]} />);

        expect(screen.getByText('Tanya teman')).toBeInTheDocument();
        expect(screen.getByText('Tidak ada catatan')).toBeInTheDocument();
        expect(screen.getByText('Mengikuti ketentuan jabatan')).toBeInTheDocument();
    });

    it('menyediakan tautan kembali ke daftar pantauan', () => {
        render(<Bandingkan ambang={ambang} item={[pantauanUji(1), pantauanUji(2)]} />);

        expect(screen.getByRole('link', { name: /Kembali ke daftar pantauan/ })).toHaveAttribute('href', '/pantauan');
    });
});
