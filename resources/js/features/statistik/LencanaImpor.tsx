import { Badge } from '@/components/ui/badge';
import { teksAdmin } from '@/lang/admin';
import type { RiwayatStatistikItem } from '@/types';

const varian = { berjalan: 'info', sukses: 'sukses', sebagian: 'peringatan', gagal: 'bahaya', dibatalkan: 'netral' } as const;

export function LencanaImpor({ status }: { status: RiwayatStatistikItem['status'] }) {
    return <Badge variant={varian[status]}>{teksAdmin.statistik.riwayat.status[status]}</Badge>;
}
