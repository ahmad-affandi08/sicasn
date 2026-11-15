import type { OpsiPilihan } from '@/types';

export async function muatOpsi(url: string, kataKunci = ''): Promise<OpsiPilihan[]> {
    const alamat = kataKunci === '' ? url : `${url}${url.includes('?') ? '&' : '?'}q=${encodeURIComponent(kataKunci)}`;
    const respons = await fetch(alamat, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

    if (!respons.ok) {
        throw new Error(`Gagal memuat opsi (${respons.status})`);
    }

    const isi = (await respons.json()) as { opsi: OpsiPilihan[] };

    return isi.opsi;
}
