import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

import type { PropsBersama } from '@/types';

import { useDialog } from './konteks';

export function PenangkapFlash() {
    const { dialog } = usePage<PropsBersama>().props;
    const perintah = useDialog();

    useEffect(() => {
        if (dialog) {
            void perintah[dialog.jenis]({ judul: dialog.judul, pesan: dialog.pesan });
        }
    }, [dialog, perintah]);

    return null;
}
