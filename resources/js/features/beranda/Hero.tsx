import { LuArrowDown, LuMessageCircle } from 'react-icons/lu';

import { IlustrasiBeranda } from '@/components/ilustrasi';
import { Button } from '@/components/ui/button';
import { useHubungiAdmin } from '@/hooks/useHubungiAdmin';
import { teks } from '@/lang/id';

export function Hero() {
    const hubungiAdmin = useHubungiAdmin();

    return (
        <section className="mx-auto grid w-full max-w-6xl items-center gap-10 px-4 pt-12 pb-14 sm:px-6 md:pt-16 md:pb-20 lg:grid-cols-[1.05fr_1fr] lg:gap-16 lg:pt-24 lg:pb-28">
            <div className="flex flex-col items-start gap-6">
                <p className="text-sm font-medium text-primer">{teks.hero.lencana}</p>
                <h1 className="max-w-xl text-4xl leading-[1.1] font-medium text-balance sm:text-5xl">{teks.hero.judul}</h1>
                <p className="max-w-lg text-base text-redup sm:text-lg">{teks.hero.sub}</p>
                <div className="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                    <Button size="lg" onClick={hubungiAdmin}>
                        <LuMessageCircle aria-hidden className="h-5 w-5" />
                        {teks.hero.ctaWhatsapp}
                    </Button>
                    <Button size="lg" variant="ghost" asChild>
                        <a href="#cara-akses">
                            {teks.hero.ctaCaraAkses}
                            <LuArrowDown aria-hidden className="h-4 w-4" />
                        </a>
                    </Button>
                </div>
                <p className="text-sm text-redup">{teks.hero.catatan}</p>
            </div>
            <div className="mx-auto w-full max-w-lg lg:max-w-none">
                <IlustrasiBeranda className="h-auto w-full" judul={teks.hero.ilustrasi} />
            </div>
        </section>
    );
}
