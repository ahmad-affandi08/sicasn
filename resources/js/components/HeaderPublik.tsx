import { useState } from 'react';
import { LuMenu, LuMessageCircle } from 'react-icons/lu';

import { Logo } from '@/components/Logo';
import { TombolTema } from '@/components/TombolTema';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useHubungiAdmin } from '@/hooks/useHubungiAdmin';
import { teks } from '@/lang/id';

const tautan = [
    { href: '#manfaat', label: teks.nav.manfaat },
    { href: '#cara-akses', label: teks.nav.caraAkses },
];

export function HeaderPublik() {
    const [menuTerbuka, setMenuTerbuka] = useState(false);
    const hubungiAdmin = useHubungiAdmin();

    return (
        <header className="sticky top-0 z-40 border-b border-garis bg-latar/90 backdrop-blur">
            <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">
                <Logo />

                <nav aria-label="Navigasi utama" className="hidden items-center gap-1 md:flex">
                    {tautan.map((item) => (
                        <a key={item.href} href={item.href} className="rounded-xl px-4 py-2 text-sm font-medium text-redup hover:bg-primer-lembut hover:text-primer">
                            {item.label}
                        </a>
                    ))}
                </nav>

                <div className="flex items-center gap-1.5">
                    <TombolTema />
                    <Button onClick={hubungiAdmin} className="hidden sm:inline-flex">
                        <LuMessageCircle aria-hidden className="h-4 w-4" />
                        {teks.hero.ctaWhatsapp}
                    </Button>

                    <Sheet open={menuTerbuka} onOpenChange={setMenuTerbuka}>
                        <SheetTrigger asChild>
                            <Button variant="ghost" size="icon" className="md:hidden" aria-label={teks.nav.bukaMenu}>
                                <LuMenu aria-hidden className="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent>
                            <SheetTitle>{teks.nav.menu}</SheetTitle>
                            <SheetDescription className="sr-only">{teks.nav.menu}</SheetDescription>
                            <nav aria-label="Navigasi seluler" className="flex flex-col gap-1">
                                {tautan.map((item) => (
                                    <a
                                        key={item.href}
                                        href={item.href}
                                        onClick={() => setMenuTerbuka(false)}
                                        className="rounded-xl px-4 py-3 text-base font-medium hover:bg-primer-lembut"
                                    >
                                        {item.label}
                                    </a>
                                ))}
                            </nav>
                            <Button
                                onClick={() => {
                                    setMenuTerbuka(false);
                                    hubungiAdmin();
                                }}
                                className="mt-2"
                            >
                                <LuMessageCircle aria-hidden className="h-4 w-4" />
                                {teks.hero.ctaWhatsapp}
                            </Button>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
