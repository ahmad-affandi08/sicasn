import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { LuMenu, LuMessageCircle } from 'react-icons/lu';

import { Logo } from '@/components/Logo';
import { TombolTema } from '@/components/TombolTema';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useHubungiAdmin } from '@/hooks/useHubungiAdmin';
import { teksAuth } from '@/lang/auth';
import { teks } from '@/lang/id';
import { rute } from '@/lib/rute';
import type { PropsBersama } from '@/types';

const tautan = [
    { href: '#manfaat', label: teks.nav.manfaat },
    { href: '#cara-akses', label: teks.nav.caraAkses },
];

export function HeaderPublik() {
    const [menuTerbuka, setMenuTerbuka] = useState(false);
    const hubungiAdmin = useHubungiAdmin();
    const { auth } = usePage<PropsBersama>().props;
    const tujuanAkun = auth.pengguna?.peran === 'admin' ? rute.admin.dasbor : rute.akun;

    return (
        <header className="sticky top-0 z-40 border-b border-garis bg-latar/95 backdrop-blur">
            <div className="mx-auto flex h-14 w-full max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">
                <Logo />

                <nav aria-label="Navigasi utama" className="hidden items-center gap-1 md:flex">
                    {tautan.map((item) => (
                        <a key={item.href} href={item.href} className="rounded-md px-3 py-2 text-sm text-redup hover:text-teks">
                            {item.label}
                        </a>
                    ))}
                </nav>

                <div className="flex items-center gap-1.5">
                    <TombolTema />
                    <Button asChild variant="ghost" className="hidden sm:inline-flex">
                        <Link href={auth.pengguna ? tujuanAkun : rute.masuk}>{auth.pengguna ? teksAuth.nav.bukaAkun : teksAuth.nav.masuk}</Link>
                    </Button>
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
                                        className="rounded-lg px-3 py-3 text-base font-medium hover:bg-permukaan"
                                    >
                                        {item.label}
                                    </a>
                                ))}
                            </nav>
                            <Button asChild variant="outline" className="mt-2">
                                <Link href={auth.pengguna ? tujuanAkun : rute.masuk}>{auth.pengguna ? teksAuth.nav.bukaAkun : teksAuth.nav.masuk}</Link>
                            </Button>
                            <Button
                                onClick={() => {
                                    setMenuTerbuka(false);
                                    hubungiAdmin();
                                }}
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
