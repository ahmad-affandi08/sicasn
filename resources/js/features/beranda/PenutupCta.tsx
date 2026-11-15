import { LuMessageCircle } from 'react-icons/lu';

import { Button } from '@/components/ui/button';
import { useHubungiAdmin } from '@/hooks/useHubungiAdmin';
import { teks } from '@/lang/id';

export function PenutupCta() {
    const hubungiAdmin = useHubungiAdmin();

    return (
        <section className="border-t border-garis">
            <div className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-14 sm:px-6 md:flex-row md:items-center md:justify-between md:py-16">
                <div className="flex max-w-xl flex-col gap-2">
                    <h2 className="text-2xl font-medium sm:text-3xl">{teks.penutup.judul}</h2>
                    <p className="text-redup">{teks.penutup.sub}</p>
                </div>
                <Button size="lg" onClick={hubungiAdmin} className="self-start md:self-auto">
                    <LuMessageCircle aria-hidden className="h-5 w-5" />
                    {teks.hero.ctaWhatsapp}
                </Button>
            </div>
        </section>
    );
}
