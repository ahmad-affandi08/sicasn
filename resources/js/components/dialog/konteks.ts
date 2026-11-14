import { createContext, useContext } from 'react';

import type { PerintahDialog } from './jenis';

export const KonteksDialog = createContext<PerintahDialog | null>(null);

export function useDialog(): PerintahDialog {
    const perintah = useContext(KonteksDialog);

    if (perintah === null) {
        throw new Error('useDialog harus dipakai di dalam DialogProvider.');
    }

    return perintah;
}
