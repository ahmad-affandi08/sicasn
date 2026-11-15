import { Link, router, usePage } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { LuExternalLink, LuLayoutDashboard, LuLogOut, LuMail, LuMenu, LuMessageCircle, LuChartColumn, LuRefreshCw, LuSettings, LuTarget, LuUsers, LuWallet } from 'react-icons/lu';
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
    { href: rute.admin.sinkron, label: teksAdmin.nav.sinkron, ikon: LuRefreshCw, cocok: (url) => url.startsWith(rute.admin.sinkron) },
    { href: rute.admin.statistik, label: teksAdmin.nav.statistik, ikon: LuChartColumn, cocok: (url) => url.startsWith(rute.admin.statistik) },
];

const pengaturan: ItemNav[] = [
    { href: rute.admin.email, label: teksAdmin.nav.email, ikon: LuMail, cocok: (url) => url.startsWith(rute.admin.email) },
    { href: rute.admin.whatsapp, label: teksAdmin.nav.whatsapp, ikon: LuMessageCircle, cocok: (url) => url.startsWith(rute.admin.whatsapp) },
    { href: rute.admin.pembayaran, label: teksAdmin.nav.pembayaran, ikon: LuWallet, cocok: (url) => url.startsWith(rute.admin.pembayaran) },
    { href: rute.admin.persaingan, label: teksAdmin.nav.persaingan, ikon: LuTarget, cocok: (url) => url.startsWith(rute.admin.persaingan) },
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
                'relative flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm transition-colors md:min-h-9',
                aktif ? 'bg-kartu font-medium text-teks ring-1 ring-garis before:absolute before:inset-y-2 before:left-0 before:w-0.5 before:rounded-full before:bg-primer' : 'text-redup hover:bg-kartu/70 hover:text-teks',
            )}
        >
            <Ikon aria-hidden className="h-4 w-4 shrink-0" />
            {item.label}
        </Link>
    );
}

function IsiNavigasi({ url, onPilih }: { url: string; onPilih?: () => void }) {
    return (
        <nav aria-label={teksAdmin.nav.utama} className="flex flex-col gap-1">
            {utama.map((item) => (
                <TautanNav key={item.href} item={item} url={url} onPilih={onPilih} />
            ))}
            <p className="mt-5 mb-1 px-3 text-xs font-medium text-redup">{teksAdmin.nav.pengaturan}</p>
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
            <aside className="sticky top-0 hidden h-dvh flex-col gap-6 overflow-y-auto border-r border-garis bg-permukaan p-3 lg:flex">
                <div className="flex items-center justify-between px-2 pt-2">
                    <Logo />
                    <span className="text-xs text-redup">{teksAdmin.nav.judul}</span>
                </div>
                <IsiNavigasi url={url} />
                <div className="mt-auto flex flex-col gap-1 border-t border-garis pt-4">
                    <a
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex min-h-9 items-center gap-3 rounded-lg px-3 text-sm text-redup hover:bg-kartu/70 hover:text-teks"
                    >
                        <LuExternalLink aria-hidden className="h-4 w-4" />
                        {teksAdmin.nav.lihatSitus}
                    </a>
                </div>
            </aside>

            <div className="flex min-w-0 flex-col">
                <header className="sticky top-0 z-40 flex h-14 items-center justify-between gap-3 border-b border-garis bg-latar/95 px-4 backdrop-blur sm:px-6">
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
                        <Button variant="ghost" onClick={() => router.post(rute.keluar)}>
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
