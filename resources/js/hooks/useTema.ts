import { useCallback, useState } from 'react';

export function useTema() {
    const [gelap, setGelap] = useState(() => document.documentElement.classList.contains('dark'));

    const ganti = useCallback(() => {
        const berikutnya = !document.documentElement.classList.contains('dark');
        document.documentElement.classList.toggle('dark', berikutnya);

        try {
            localStorage.setItem('tema', berikutnya ? 'gelap' : 'terang');
        } catch {
            return;
        } finally {
            setGelap(berikutnya);
        }
    }, []);

    return { gelap, ganti };
}
