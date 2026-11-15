import { useSyncExternalStore } from 'react';

const KUERI = '(min-width: 1024px)';

function berlangganan(panggil: () => void): () => void {
    if (typeof window.matchMedia !== 'function') {
        return () => undefined;
    }

    const media = window.matchMedia(KUERI);
    media.addEventListener('change', panggil);

    return () => media.removeEventListener('change', panggil);
}

function ambil(): boolean {
    return typeof window.matchMedia === 'function' && window.matchMedia(KUERI).matches;
}

export function useLayarLebar(): boolean {
    return useSyncExternalStore(berlangganan, ambil, () => false);
}
