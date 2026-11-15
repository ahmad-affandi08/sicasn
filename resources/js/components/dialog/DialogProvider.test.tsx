import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { describe, expect, it } from 'vitest';

import { DialogProvider } from './DialogProvider';
import { useDialog } from './konteks';

function Uji() {
    const dialog = useDialog();
    const [hasil, setHasil] = useState('belum');

    return (
        <div>
            <button onClick={() => void dialog.sukses({ judul: 'Berhasil disimpan', pesan: 'Data sudah tersimpan.' })}>sukses</button>
            <button
                onClick={() =>
                    void dialog
                        .konfirmasi({ judul: 'Nonaktifkan akun?', pesan: 'Pengguna tidak bisa masuk.', berisiko: true })
                        .then((setuju) => setHasil(setuju ? 'ya' : 'tidak'))
                }
            >
                konfirmasi
            </button>
            <button
                onClick={() =>
                    void dialog
                        .konfirmasiKetik({ judul: 'Rollback impor?', pesan: 'Data batch dihapus.', kataKunci: 'ROLLBACK' })
                        .then((setuju) => setHasil(setuju ? 'ya' : 'tidak'))
                }
            >
                ketik
            </button>
            <button
                onClick={() => {
                    void dialog.sukses({ judul: 'Sedang tampil', pesan: 'Dialog yang ditunggu.' });
                    void dialog.tunggu().then(() => setHasil('selesai-menunggu'));
                }}
            >
                tunggu
            </button>
            <button
                onClick={() => {
                    void dialog.gagal({ judul: 'Pertama', pesan: 'Dialog pertama.' });
                    void dialog.galat({ judul: 'Kedua', pesan: 'Dialog kedua.' });
                }}
            >
                antre
            </button>
            <button
                onClick={() =>
                    void dialog.peringatan({
                        judul: 'Akun nonaktif',
                        pesan: 'Hubungi admin.',
                        tautan: { label: 'Hubungi admin via WhatsApp', url: 'https://wa.me/6281234567890' },
                    })
                }
            >
                tautan
            </button>
            <output>{hasil}</output>
        </div>
    );
}

function tampilkan() {
    return render(
        <DialogProvider>
            <Uji />
        </DialogProvider>,
    );
}

describe('DialogProvider', () => {
    it('menampilkan dialog sukses di tengah lalu menutupnya', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'sukses' }));

        const dialog = await screen.findByRole('alertdialog');
        expect(dialog).toHaveTextContent('Berhasil disimpan');
        expect(dialog).toHaveTextContent('Data sudah tersimpan.');

        await pengguna.click(screen.getByRole('button', { name: 'Mengerti' }));
        await waitFor(() => expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument());
    });

    it('menyelesaikan tunggu seketika bila tidak ada dialog yang tampil', async () => {
        const pengguna = userEvent.setup();
        let selesai = false;

        function Pemicu() {
            const dialog = useDialog();

            return (
                <button
                    onClick={() => {
                        void dialog.tunggu().then(() => {
                            selesai = true;
                        });
                    }}
                >
                    langsung
                </button>
            );
        }

        render(
            <DialogProvider>
                <Pemicu />
            </DialogProvider>,
        );
        await pengguna.click(screen.getByRole('button', { name: 'langsung' }));

        await waitFor(() => expect(selesai).toBe(true));
    });

    it('baru menyelesaikan tunggu setelah dialog yang sedang tampil ditutup', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'tunggu' }));
        await screen.findByRole('alertdialog');
        expect(screen.getByText('belum')).toBeInTheDocument();

        await pengguna.click(screen.getByRole('button', { name: 'Mengerti' }));

        await waitFor(() => expect(screen.getByText('selesai-menunggu')).toBeInTheDocument());
    });

    it('memindahkan fokus ke tombol utama pada dialog tanpa tombol batal', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'sukses' }));
        await screen.findByRole('alertdialog');

        await waitFor(() => expect(screen.getByRole('button', { name: 'Mengerti' })).toHaveFocus());
    });

    it('memfokuskan tombol batal pada dialog konfirmasi agar aksi berisiko tidak terpicu tak sengaja', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'konfirmasi' }));
        await screen.findByRole('alertdialog');

        await waitFor(() => expect(screen.getByRole('button', { name: 'Batal' })).toHaveFocus());
    });

    it('mengembalikan true saat konfirmasi disetujui', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'konfirmasi' }));
        await pengguna.click(await screen.findByRole('button', { name: 'Ya, lanjutkan' }));

        await waitFor(() => expect(screen.getByRole('status')).toHaveTextContent('ya'));
    });

    it('mengembalikan false saat konfirmasi dibatalkan', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'konfirmasi' }));
        await pengguna.click(await screen.findByRole('button', { name: 'Batal' }));

        await waitFor(() => expect(screen.getByRole('status')).toHaveTextContent('tidak'));
    });

    it('menahan tombol sampai kata kunci diketik dengan benar', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'ketik' }));

        const lanjut = await screen.findByRole('button', { name: 'Ya, lanjutkan' });
        expect(lanjut).toBeDisabled();

        await pengguna.type(screen.getByLabelText(/Ketik "ROLLBACK"/), 'ROLLBACK');
        expect(lanjut).toBeEnabled();

        await pengguna.click(lanjut);
        await waitFor(() => expect(screen.getByRole('status')).toHaveTextContent('ya'));
    });

    it('menampilkan dialog berurutan, bukan bertumpuk', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'antre' }));

        expect(await screen.findByRole('alertdialog')).toHaveTextContent('Pertama');
        expect(screen.getAllByRole('alertdialog')).toHaveLength(1);

        await pengguna.click(screen.getByRole('button', { name: 'Mengerti' }));

        await waitFor(() => expect(screen.getByRole('alertdialog')).toHaveTextContent('Kedua'));
    });

    it('menampilkan tombol tautan WhatsApp yang membuka tab baru dengan aman', async () => {
        const pengguna = userEvent.setup();
        tampilkan();

        await pengguna.click(screen.getByRole('button', { name: 'tautan' }));

        const tautan = await screen.findByRole('link', { name: 'Hubungi admin via WhatsApp' });
        expect(tautan).toHaveAttribute('href', 'https://wa.me/6281234567890');
        expect(tautan).toHaveAttribute('target', '_blank');
        expect(tautan).toHaveAttribute('rel', 'noopener noreferrer');
    });
});
