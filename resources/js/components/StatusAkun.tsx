import { Badge } from '@/components/ui/badge';
import { teksAdmin } from '@/lang/admin';
import type { PenggunaItem } from '@/types';

export function LencanaAkun({ pengguna }: { pengguna: PenggunaItem }) {
    if (pengguna.peran === 'admin') {
        return <Badge variant="info">{teksAdmin.pengguna.admin}</Badge>;
    }

    return pengguna.aktif ? <Badge variant="sukses">{teksAdmin.pengguna.aktif}</Badge> : <Badge variant="peringatan">{teksAdmin.pengguna.nonaktif}</Badge>;
}

export function LencanaEmail({ status }: { status: PenggunaItem['statusEmail'] }) {
    const teks = teksAdmin.pengguna.statusEmail;

    if (status === null) {
        return <Badge variant="netral">{teks.belum}</Badge>;
    }

    const varian = { menunggu: 'info', terkirim: 'sukses', gagal: 'bahaya' } as const;

    return <Badge variant={varian[status]}>{teks[status]}</Badge>;
}
