import { Badge } from '@/components/ui/badge';
import { teksAdmin } from '@/lang/admin';
import type { BatchItem } from '@/types';

const varian = { berjalan: 'info', sukses: 'sukses', sebagian: 'peringatan', gagal: 'bahaya' } as const;

export function LencanaBatch({ status }: { status: BatchItem['status'] }) {
    return <Badge variant={varian[status]}>{teksAdmin.sinkron.status[status]}</Badge>;
}
