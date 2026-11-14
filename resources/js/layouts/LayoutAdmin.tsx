import { Link, router, usePage } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { LuChevronRight, LuExternalLink, LuLayoutDashboard, LuLogOut, LuMail, LuMenu, LuMessageCircle, LuSettings, LuUsers, LuWallet } from 'react-icons/lu';
import type { IconType } from 'react-icons';

import { PenangkapFlash } from '@/components/dialog/PenangkapFlash';
import { Logo } from '@/components/Logo';
import { TombolTema } from '@/components/TombolTema';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { teksAdmin } from '@/lang/admin';
import { rute } from '@/lib/rute';
import { cn } from '@/lib/utils';
import type { PropsBersama } from '@/types';

interface ItemNav {
    href: string;
    label: string;
    ikon: IconType;
    cocok: (url: string) => boolean;
}

const utama: ItemNav[] = [
    { href: rute.admin.dasbor, label: teksAdmin.nav.dasbor, ikon: LuLayoutDashboard, cocok: (url) => url === rute.admin.dasbor },
    { href: rute.admin.pengguna, label: teksAdmin.nav.pengguna, ikon: LuUsers, cocok: (url) => url.startsWith(rute.admin.pengguna) },
];

const pengaturan: ItemNav[] = [
    { href: rute.admin.email, label: teksAdmin.nav.email, ikon: LuMail, cocok: (url) => url.startsWith(rute.admin.email) },
    { href: rute.admin.whatsapp, label: teksAdmin.nav.whatsapp, ikon: LuMessageCircle, cocok: (url) => url.startsWith(rute.admin.whatsapp) },
    { href: rute.admin.pembayaran, label: teksAdmin.nav.pembayaran, ikon: LuWallet, cocok: (url) => url.startsWith(rute.admin.pembayaran) },
    { href: rute.admin.situs, label: teksAdmin.nav.situs, ikon: LuSettings, cocok: (url) => url.startsWith(rute.admin.situs) },
];

function TautanNav({ item, url, onPilih }: { item: ItemNav; url: string; onPilih?: () => void }) {
    const aktif = item.cocok(url);
    const Ikon = item.ikon;

    return (
        <Link
            href={item.href}
            onClick={onPilih}
            aria-current={aktif ? 'page' : undefined}
            className={cn(
                'flex min-h-11 items-center gap-3 rounded-xl px-3.5 text-sm font-medium transition-colors',
                aktif ? 'bg-primer text-primer-teks' : 'text-redup hover:bg-primer-lembut hover:text-primer',
            )}
        >
            <Ikon aria-hidden className="h-5 w-5 shrink-0" />
            {item.label}
            {aktif && <LuChevronRight aria-hidden className="ml-auto h-4 w-4" />}
        </Link>
    );
}

function IsiNavigasi({ url, onPilih }: { url: string; onPilih?: () => void }) {
    return (
        <nav aria-label={teksAdmin.nav.utama} className="flex flex-col gap-1">
            {utama.map((item) => (
                <TautanNav key={item.href} item={item} url={url} onPilih={onPilih} />
            ))}
            <p className="mt-4 mb-1 px-3.5 text-xs font-medium tracking-wide text-redup uppercase">{teksAdmin.nav.pengaturan}</p>
            {pengaturan.map((item) => (
                <TautanNav key={item.href} item={item} url={url} onPilih={onPilih} />
            ))}
        </nav>
    );
}

export function LayoutAdmin({ children }: { children: ReactNode }) {
    const { url, props } = usePage<PropsBersama>();
    const [menuTerbuka, setMenuTerbuka] = useState(false);

    return (
        <div className="min-h-dvh lg:grid lg:grid-cols-[17rem_1fr]">
            <aside className="sticky top-0 hidden h-dvh flex-col gap-6 overflow-y-auto border-r border-garis bg-kartu p-4 lg:flex">
                <div className="px-1.5 pt-1">
                    <Logo />
                    <p className="mt-3 px-1 text-xs font-medium tracking-wide text-redup uppercase">{teksAdmin.nav.judul}</p>
                </div>
                <IsiNavigasi url={url} />
                <div className="mt-auto flex flex-col gap-1 border-t border-garis pt-4">
                    <a
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex min-h-11 items-center gap-3 rounded-xl px-3.5 text-sm font-medium text-redup hover:bg-primer-lembut hover:text-primer"
                    >
                        <LuExternalLink aria-hidden className="h-5 w-5" />
                        {teksAdmin.nav.lihatSitus}
                    </a>
                </div>
            </aside>

            <div className="flex min-w-0 flex-col">
                <header className="sticky top-0 z-40 flex h-16 items-center justify-between gap-3 border-b border-garis bg-latar/90 px-4 backdrop-blur sm:px-6">
                    <div className="flex items-center gap-2 lg:hidden">
                        <Sheet open={menuTerbuka} onOpenChange={setMenuTerbuka}>
                            <SheetTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={teksAdmin.nav.buka}>
                                    <LuMenu aria-hidden className="h-5 w-5" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent sisi="kiri">
                                <SheetTitle>{teksAdmin.nav.judul}</SheetTitle>
                                <SheetDescription className="sr-only">{teksAdmin.nav.utama}</SheetDescription>
                                <IsiNavigasi url={url} onPilih={() => setMenuTerbuka(false)} />
                            </SheetContent>
                        </Sheet>
                        <Logo />
                    </div>
                    <p className="hidden text-sm text-redup lg:block">{teksAdmin.nav.judul}</p>
                    <div className="flex items-center gap-1.5">
                        <span className="hidden max-w-56 truncate text-sm text-redup sm:block">{props.auth.pengguna?.nama}</span>
                        <TombolTema />
                        <Button variant="outline" onClick={() => router.post(rute.keluar)}>
                            <LuLogOut aria-hidden className="h-4 w-4" />
                            <span className="hidden sm:inline">{teksAdmin.nav.keluar}</span>
                            <span className="sr-only sm:hidden">{teksAdmin.nav.keluar}</span>
                        </Button>
                    </div>
                </header>
                <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:px-6 md:py-8">{children}</main>
            </div>
            <PenangkapFlash />
        </div>
    );
}
