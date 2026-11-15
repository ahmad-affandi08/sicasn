import type { IconType } from 'react-icons';
import { LuCircleAlert, LuCircleCheck, LuCircleHelp, LuClock, LuFlame } from 'react-icons/lu';

import { Badge } from '@/components/ui/badge';
import { teksPeluang } from '@/lang/peluang';
import type { TingkatPersainganKode } from '@/types';

const gaya: Record<TingkatPersainganKode, { varian: 'sukses' | 'peringatan' | 'bahaya' | 'info' | 'netral'; ikon: IconType }> = {
    longgar: { varian: 'sukses', ikon: LuCircleCheck },
    sedang: { varian: 'peringatan', ikon: LuCircleAlert },
    ketat: { varian: 'bahaya', ikon: LuFlame },
    belum_final: { varian: 'info', ikon: LuClock },
    belum_ada_data: { varian: 'netral', ikon: LuCircleHelp },
};

export function LencanaPersaingan({ tingkat }: { tingkat: TingkatPersainganKode }) {
    const { varian, ikon: Ikon } = gaya[tingkat];

    return (
        <Badge variant={varian} title={teksPeluang.lencana.penjelasan[tingkat]}>
            <Ikon aria-hidden className="h-3.5 w-3.5" />
            {teksPeluang.lencana[tingkat]}
        </Badge>
    );
}
