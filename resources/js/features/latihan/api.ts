import { rute } from '@/lib/rute';

export type HasilKirim = 'tersimpan' | 'waktuHabis' | 'gagal';

interface BerkasJawaban {
    soalId: number;
    opsiId: number | null;
    ragu: boolean;
}

function tokenXsrf(): string {
    const cocok = document.cookie.split('; ').find((butir) => butir.startsWith('XSRF-TOKEN='));

    return cocok ? decodeURIComponent(cocok.slice('XSRF-TOKEN='.length)) : '';
}

export async function kirimJawaban(percobaanId: number, berkas: BerkasJawaban): Promise<HasilKirim> {
    try {
        const respons = await fetch(rute.jawabLatihan(percobaanId), {
            method: 'PUT',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': tokenXsrf() },
            body: JSON.stringify({ soal_id: berkas.soalId, opsi_id: berkas.opsiId, ragu: berkas.ragu }),
        });

        if (respons.status === 409) {
            return 'waktuHabis';
        }

        return respons.ok ? 'tersimpan' : 'gagal';
    } catch {
        return 'gagal';
    }
}
