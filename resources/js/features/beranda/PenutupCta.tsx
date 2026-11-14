import { LuMessageCircle } from 'react-icons/lu';

import { Button } from '@/components/ui/button';
import { useHubungiAdmin } from '@/hooks/useHubungiAdmin';
import { teks } from '@/lang/id';

export function PenutupCta() {
    const hubungiAdmin = useHubungiAdmin();

    return (
        <section className="mx-auto w-full max-w-6xl px-4 pb-16 sm:px-6 md:pb-24">
            <div className="flex flex-col items-center gap-5 rounded-3xl bg-primer px-6 py-12 text-center text-primer-teks sm:px-12">
                <h2 className="text-2xl font-bold sm:text-3xl">{teks.penutup.judul}</h2>
                <p className="max-w-xl opacity-90">{teks.penutup.sub}</p>
                <Button size="lg" variant="secondary" onClick={hubungiAdmin}>
                    <LuMessageCircle aria-hidden className="h-5 w-5" />
                    {teks.hero.ctaWhatsapp}
                </Button>
            </div>
        </section>
    );
}
