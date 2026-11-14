import type { InfoPenyedia, IsianEmail, IsianSmtp, PengaturanEmailTampil } from './tipe';

export function isianAwalPenyedia(kode: string, penyedia: InfoPenyedia[], tersimpan: PengaturanEmailTampil['konfigurasi']): IsianSmtp {
    const simpanan = tersimpan[kode];

    if (simpanan) {
        return { host: simpanan.host, port: String(simpanan.port), enkripsi: simpanan.enkripsi, username: simpanan.username, password: '' };
    }

    const preset = penyedia.find((item) => item.kode === kode)?.preset;

    return {
        host: preset?.host ?? '',
        port: String(preset?.port ?? 587),
        enkripsi: preset?.enkripsi ?? 'tls',
        username: preset?.username ?? '',
        password: '',
    };
}

export function isianAwal(pengaturan: PengaturanEmailTampil, penyedia: InfoPenyedia[]): IsianEmail {
    const aktif = pengaturan.aktif ?? penyedia[0]?.kode ?? 'hostinger';
    const cadangan = pengaturan.cadangan ?? '';
    const konfigurasi: IsianEmail['konfigurasi'] = { [aktif]: isianAwalPenyedia(aktif, penyedia, pengaturan.konfigurasi) };

    if (cadangan !== '') {
        konfigurasi[cadangan] = isianAwalPenyedia(cadangan, penyedia, pengaturan.konfigurasi);
    }

    return {
        aktif,
        cadangan,
        pengirim_nama: pengaturan.pengirimNama,
        pengirim_alamat: pengaturan.pengirimAlamat,
        alamat_balas: pengaturan.alamatBalas,
        konfigurasi,
    };
}

export function keMuatan(data: IsianEmail) {
    const dipakai = [data.aktif, data.cadangan].filter((kode) => kode !== '');
    const konfigurasi: Record<string, { host: string; port: number; enkripsi: string; username: string; password: string }> = {};

    for (const kode of dipakai) {
        const isian = data.konfigurasi[kode];

        if (isian) {
            konfigurasi[kode] = { ...isian, port: Number(isian.port) };
        }
    }

    return { ...data, cadangan: data.cadangan === '' ? null : data.cadangan, alamat_balas: data.alamat_balas === '' ? null : data.alamat_balas, konfigurasi };
}
