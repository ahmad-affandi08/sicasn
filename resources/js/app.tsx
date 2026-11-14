import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';

import { DialogProvider } from '@/components/dialog/DialogProvider';
import { teks } from '@/lang/id';

import '../css/app.css';

const halaman = import.meta.glob<{ default: ComponentType }>(['./pages/**/*.tsx', '!./pages/**/*.test.tsx']);

void createInertiaApp({
    title: (judul) => (judul ? `${judul} · ${teks.aplikasi}` : teks.aplikasi),
    resolve: async (nama) => {
        const muat = halaman[`./pages/${nama}.tsx`];

        if (!muat) {
            throw new Error(`Halaman tidak ditemukan: ${nama}`);
        }

        const modul = await muat();

        return modul.default;
    },
    withApp: (aplikasi) => <DialogProvider>{aplikasi}</DialogProvider>,
});
