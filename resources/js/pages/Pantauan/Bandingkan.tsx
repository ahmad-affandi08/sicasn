import { Head, Link } from '@inertiajs/react';
import { LuArrowLeft } from 'react-icons/lu';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Button } from '@/components/ui/button';
import { TabelPerbandingan } from '@/features/pantauan/TabelPerbandingan';
import { teksPantauan } from '@/lang/pantauan';
import { LayoutMember } from '@/layouts/LayoutMember';
import { rute } from '@/lib/rute';
import type { PantauanItem } from '@/types';

interface PropsHalaman {
    item: PantauanItem[];
    ambang: { longgarMaks: number; ketatMin: number };
}

export default function Bandingkan({ item, ambang }: PropsHalaman) {
    const teks = teksPantauan.halamanBandingkan;

    return (
        <LayoutMember lebar>
            <Head title={teks.judul} />
            <div className="mb-4">
                <Button asChild variant="ghost" className="-ml-3">
                    <Link href={rute.pantauan}>
                        <LuArrowLeft aria-hidden className="h-4 w-4" />
                        {teks.kembali}
                    </Link>
                </Button>
            </div>
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />
            <TabelPerbandingan item={item} ambang={ambang} />
        </LayoutMember>
    );
}
