import { Head } from '@inertiajs/react';

import { CaraAkses } from '@/features/beranda/CaraAkses';
import { InfoPembayaran } from '@/features/beranda/InfoPembayaran';
import { Hero } from '@/features/beranda/Hero';
import { Manfaat } from '@/features/beranda/Manfaat';
import { PenutupCta } from '@/features/beranda/PenutupCta';
import { teks } from '@/lang/id';
import { LayoutPublik } from '@/layouts/LayoutPublik';
import type { InfoPembayaran as DataPembayaran } from '@/types';

interface PropsHalaman {
    pembayaran: DataPembayaran | null;
}

export default function Beranda({ pembayaran }: PropsHalaman) {
    return (
        <LayoutPublik>
            <Head title={teks.tagline} />
            <Hero />
            <Manfaat />
            <CaraAkses />
            {pembayaran && <InfoPembayaran info={pembayaran} />}
            <PenutupCta />
        </LayoutPublik>
    );
}
