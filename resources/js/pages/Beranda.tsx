import { Head } from '@inertiajs/react';

import { CaraAkses } from '@/features/beranda/CaraAkses';
import { Hero } from '@/features/beranda/Hero';
import { Manfaat } from '@/features/beranda/Manfaat';
import { PenutupCta } from '@/features/beranda/PenutupCta';
import { teks } from '@/lang/id';
import { LayoutPublik } from '@/layouts/LayoutPublik';

export default function Beranda() {
    return (
        <LayoutPublik>
            <Head title={teks.tagline} />
            <Hero />
            <Manfaat />
            <CaraAkses />
            <PenutupCta />
        </LayoutPublik>
    );
}
