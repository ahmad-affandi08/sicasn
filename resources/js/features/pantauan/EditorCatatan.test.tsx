import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const inertia = vi.hoisted(() => ({ put: vi.fn(), galat: '' }));

vi.mock('@inertiajs/react', () => ({
    useForm: (awal: { catatan: string }) => {
        const [data, setNilai] = useState(awal);

        return {
            data,
            setData: (kunci: 'catatan', nilai: string) => setNilai((sekarang) => ({ ...sekarang, [kunci]: nilai })),
            put: inertia.put,
            errors: inertia.galat ? { catatan: inertia.galat } : {},
            processing: false,
            recentlySuccessful: false,
        };
    },
}));

import { EditorCatatan } from './EditorCatatan';

describe('EditorCatatan', () => {
    beforeEach(() => {
        inertia.put.mockClear();
        inertia.galat = '';
    });

    it('menampilkan catatan yang sudah ada dan menonaktifkan tombol simpan sampai berubah', () => {
        render(<EditorCatatan formasiId={3} catatan="Cek syarat usia" maks={500} />);

        expect(screen.getByLabelText('Catatan pribadi')).toHaveValue('Cek syarat usia');
        expect(screen.getByRole('button', { name: 'Simpan catatan' })).toBeDisabled();
        expect(screen.getByText('15/500')).toBeInTheDocument();
    });

    it('mengirim catatan ke route formasi yang benar', async () => {
        render(<EditorCatatan formasiId={3} catatan={null} maks={500} />);

        await userEvent.type(screen.getByLabelText('Catatan pribadi'), 'Tanya teman');
        await userEvent.click(screen.getByRole('button', { name: 'Simpan catatan' }));

        expect(inertia.put).toHaveBeenCalledWith('/pantauan/3/catatan', { preserveScroll: true });
    });

    it('menampilkan galat validasi inline di bawah isian', () => {
        inertia.galat = 'Catatan maksimal 500 karakter.';
        render(<EditorCatatan formasiId={3} catatan="x" maks={500} />);

        expect(screen.getByRole('alert')).toHaveTextContent('Catatan maksimal 500 karakter.');
        expect(screen.getByLabelText('Catatan pribadi')).toHaveAttribute('aria-invalid', 'true');
    });
});
