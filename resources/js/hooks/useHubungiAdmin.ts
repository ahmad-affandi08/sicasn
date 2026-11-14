import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';

import { useDialog } from '@/components/dialog/konteks';
import { teks } from '@/lang/id';
import type { PropsBersama } from '@/types';

export function useHubungiAdmin() {
    const { whatsappUrl } = usePage<PropsBersama>().props;
    const dialog = useDialog();

    return useCallback(() => {
        if (whatsappUrl) {
            window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
            return;
        }

        void dialog.peringatan({
            judul: teks.dialog.kontakBelumAdaJudul,
            pesan: teks.dialog.kontakBelumAdaPesan,
        });
    }, [whatsappUrl, dialog]);
}
