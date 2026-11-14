import { LuArrowDown, LuMessageCircle } from 'react-icons/lu';

import { IlustrasiBeranda } from '@/components/ilustrasi';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useHubungiAdmin } from '@/hooks/useHubungiAdmin';
import { teks } from '@/lang/id';

export function Hero() {
    const hubungiAdmin = useHubungiAdmin();

    return (
        <section className="mx-auto grid w-full max-w-6xl items-center gap-10 px-4 py-12 sm:px-6 md:py-16 lg:grid-cols-2 lg:gap-12 lg:py-20">
            <div className="flex flex-col items-start gap-6">
                <Badge>{teks.hero.lencana}</Badge>
                <h1 className="text-3xl leading-tight font-bold text-balance sm:text-4xl lg:text-5xl">{teks.hero.judul}</h1>
                <p className="max-w-xl text-base text-redup sm:text-lg">{teks.hero.sub}</p>
                <div className="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <Button size="lg" onClick={hubungiAdmin}>
                        <LuMessageCircle aria-hidden className="h-5 w-5" />
                        {teks.hero.ctaWhatsapp}
                    </Button>
                    <Button size="lg" variant="outline" asChild>
                        <a href="#cara-akses">
                            {teks.hero.ctaCaraAkses}
                            <LuArrowDown aria-hidden className="h-5 w-5" />
                        </a>
                    </Button>
                </div>
                <p className="text-sm text-redup">{teks.hero.catatan}</p>
            </div>
            <div className="mx-auto w-full max-w-md lg:max-w-none">
                <IlustrasiBeranda className="h-auto w-full" judul={teks.hero.ilustrasi} />
            </div>
        </section>
    );
}
