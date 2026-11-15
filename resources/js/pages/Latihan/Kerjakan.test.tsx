import { act, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { percobaanUji } from '@/features/latihan/uji';

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
        url: '/latihan/7',
        props: { whatsappUrl: null, dialog: null, panduan: false, aplikasi: { nama: 'SICASN' }, auth: { pengguna: { nama: 'Uji', email: 'u@uji.test', peran: 'member', wajibGantiPassword: false } } },
    }),
}));

import Kerjakan from './Kerjakan';

const fetchPalsu = vi.fn();

function tampilkan(jumlah = 3, sisaDetik = 600) {
    return render(
        <DialogProvider>
            <Kerjakan percobaan={percobaanUji(jumlah, sisaDetik)} />
        </DialogProvider>,
    );
}

function responsOk(status = 200) {
    return Promise.resolve({ ok: status >= 200 && status < 300, status } as Response);
}

beforeEach(() => {
    inertia.kirim.mockClear();
    fetchPalsu.mockReset();
    fetchPalsu.mockImplementation(() => responsOk());
    vi.stubGlobal('fetch', fetchPalsu);
    document.cookie = 'XSRF-TOKEN=token%20uji';
});

afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
});

describe('Halaman mengerjakan latihan', () => {
    it('menampilkan soal pertama, timer, dan daftar soal', () => {
        tampilkan();

        expect(screen.getByText('Pertanyaan nomor 1')).toBeInTheDocument();
        expect(screen.getByRole('timer')).toHaveTextContent('10:00');
        expect(screen.getByRole('navigation', { name: 'Daftar soal' })).toBeInTheDocument();
        expect(screen.getByText('0 dari 3 soal dijawab · Jawaban tersimpan')).toBeInTheDocument();
    });

    it('menyimpan jawaban ke server dengan token CSRF dan menandai soal sebagai dijawab', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('radio', { name: /B\. Teks B soal 1/ }));

        await waitFor(() => expect(fetchPalsu).toHaveBeenCalledTimes(1));
        const [url, opsi] = fetchPalsu.mock.calls[0] as [string, RequestInit];
        expect(url).toBe('/latihan/7/jawaban');
        expect(opsi.method).toBe('PUT');
        expect((opsi.headers as Record<string, string>)['X-XSRF-TOKEN']).toBe('token uji');
        expect(JSON.parse(String(opsi.body))).toEqual({ soal_id: 10, opsi_id: 101, ragu: false });
        expect(screen.getByRole('button', { name: 'Soal 1, dijawab' })).toBeInTheDocument();
        expect(screen.getByText(/1 dari 3 soal dijawab/)).toBeInTheDocument();
    });

    it('menyimpan tanda ragu-ragu dan menampilkannya pada daftar soal', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('checkbox', { name: 'Ragu-ragu' }));

        await waitFor(() => expect(fetchPalsu).toHaveBeenCalled());
        expect(JSON.parse(String((fetchPalsu.mock.calls[0] as [string, RequestInit])[1].body))).toEqual({ soal_id: 10, opsi_id: null, ragu: true });
        expect(screen.getByRole('button', { name: 'Soal 1, dijawab dan ragu-ragu' })).toBeInTheDocument();
    });

    it('berpindah soal lewat tombol berikutnya, sebelumnya, dan daftar soal', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'Berikutnya' }));
        expect(screen.getByText('Pertanyaan nomor 2')).toBeInTheDocument();

        await pengguna.click(screen.getByRole('button', { name: 'Sebelumnya' }));
        expect(screen.getByText('Pertanyaan nomor 1')).toBeInTheDocument();

        await pengguna.click(screen.getByRole('button', { name: /Soal 3/ }));
        expect(screen.getByText('Pertanyaan nomor 3')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'Berikutnya' })).not.toBeInTheDocument();
    });

    it('menonaktifkan tombol sebelumnya di soal pertama', () => {
        tampilkan();

        expect(screen.getByRole('button', { name: 'Sebelumnya' })).toBeDisabled();
    });

    it('meminta konfirmasi dengan jumlah soal kosong dan ragu sebelum menyelesaikan', async () => {
        const pengguna = userEvent.setup();
        tampilkan();
        await pengguna.click(screen.getByRole('radio', { name: /A\. Teks A soal 1/ }));
        await pengguna.click(screen.getByRole('checkbox', { name: 'Ragu-ragu' }));

        await pengguna.click(screen.getAllByRole('button', { name: 'Selesaikan latihan' })[0] as HTMLElement);

        const dialog = await screen.findByRole('alertdialog');
        expect(dialog).toHaveTextContent('2 soal belum dijawab dan 1 soal ditandai ragu-ragu');
        expect(inertia.kirim).not.toHaveBeenCalled();
    });

    it('menyelesaikan latihan setelah dikonfirmasi dan menunggu penyimpanan terakhir', async () => {
        const pengguna = userEvent.setup();
        tampilkan();
        await pengguna.click(screen.getByRole('radio', { name: /A\. Teks A soal 1/ }));
        await pengguna.click(screen.getAllByRole('button', { name: 'Selesaikan latihan' })[0] as HTMLElement);

        await pengguna.click(await screen.findByRole('button', { name: 'Ya, selesaikan' }));

        await waitFor(() => expect(inertia.kirim).toHaveBeenCalledWith('/latihan/7/selesai'));
        expect(fetchPalsu).toHaveBeenCalledTimes(1);
    });

    it('tidak menyelesaikan latihan bila pengguna memilih lanjut mengerjakan', async () => {
        const pengguna = userEvent.setup();
        tampilkan();
        await pengguna.click(screen.getAllByRole('button', { name: 'Selesaikan latihan' })[0] as HTMLElement);

        await pengguna.click(await screen.findByRole('button', { name: 'Lanjut mengerjakan' }));

        expect(inertia.kirim).not.toHaveBeenCalled();
    });

    it('mengulang pengiriman sekali lalu memberi tahu bila tetap gagal', async () => {
        const pengguna = userEvent.setup();
        fetchPalsu.mockImplementation(() => Promise.reject(new Error('jaringan putus')));
        tampilkan();

        await pengguna.click(screen.getByRole('radio', { name: /A\. Teks A soal 1/ }));

        const dialog = await screen.findByRole('alertdialog', undefined, { timeout: 4000 });
        expect(dialog).toHaveTextContent('Jawaban belum tersimpan');
        expect(fetchPalsu).toHaveBeenCalledTimes(2);
    });

    it('menyelesaikan otomatis bila server menyatakan waktu sudah habis', async () => {
        const pengguna = userEvent.setup();
        fetchPalsu.mockImplementation(() => responsOk(409));
        tampilkan();

        await pengguna.click(screen.getByRole('radio', { name: /A\. Teks A soal 1/ }));

        await pengguna.click(await screen.findByRole('button', { name: 'Lihat hasil' }));
        await waitFor(() => expect(inertia.kirim).toHaveBeenCalledWith('/latihan/7/selesai'));
    });

    it('menghitung mundur dan menyelesaikan otomatis saat waktu habis', async () => {
        vi.useFakeTimers({ shouldAdvanceTime: true });
        tampilkan(3, 2);
        expect(screen.getByRole('timer')).toHaveTextContent('00:02');

        await act(async () => {
            await vi.advanceTimersByTimeAsync(3000);
        });

        expect(screen.getByRole('timer')).toHaveTextContent('00:00');
        const tombol = await screen.findByRole('button', { name: 'Lihat hasil' });
        await act(async () => {
            tombol.click();
        });
        await waitFor(() => expect(inertia.kirim).toHaveBeenCalledWith('/latihan/7/selesai'));
    });

    it('menampilkan jawaban yang sudah tersimpan dari percobaan sebelumnya', () => {
        const percobaan = percobaanUji(3);
        const pertama = percobaan.soal[0];

        if (!pertama) {
            throw new Error('soal uji tidak ada');
        }

        pertama.opsiDipilihId = 102;
        pertama.ragu = true;

        render(
            <DialogProvider>
                <Kerjakan percobaan={percobaan} />
            </DialogProvider>,
        );

        expect(screen.getByRole('radio', { name: /C\. Teks C soal 1/ })).toBeChecked();
        expect(screen.getByRole('checkbox', { name: 'Ragu-ragu' })).toBeChecked();
        expect(screen.getByText(/1 dari 3 soal dijawab/)).toBeInTheDocument();
    });
});
