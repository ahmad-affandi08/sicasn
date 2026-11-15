import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import { Paginasi } from './Paginasi';

describe('Paginasi', () => {
    it('tidak tampil bila hanya ada satu halaman', () => {
        const { container } = render(<Paginasi halaman={1} terakhir={1} onPindah={vi.fn()} />);

        expect(container).toBeEmptyDOMElement();
    });

    it('menonaktifkan tombol sebelumnya di halaman pertama', () => {
        render(<Paginasi halaman={1} terakhir={3} onPindah={vi.fn()} />);

        expect(screen.getByRole('button', { name: /Sebelumnya/ })).toBeDisabled();
        expect(screen.getByRole('button', { name: /Berikutnya/ })).toBeEnabled();
    });

    it('menonaktifkan tombol berikutnya di halaman terakhir', () => {
        render(<Paginasi halaman={3} terakhir={3} onPindah={vi.fn()} />);

        expect(screen.getByRole('button', { name: /Berikutnya/ })).toBeDisabled();
    });

    it('berpindah halaman', async () => {
        const pindah = vi.fn();
        render(<Paginasi halaman={2} terakhir={3} onPindah={pindah} />);

        await userEvent.click(screen.getByRole('button', { name: /Berikutnya/ }));
        await userEvent.click(screen.getByRole('button', { name: /Sebelumnya/ }));

        expect(pindah).toHaveBeenNthCalledWith(1, 3);
        expect(pindah).toHaveBeenNthCalledWith(2, 1);
        expect(screen.getByText('Halaman 2 dari 3')).toBeInTheDocument();
    });
});
