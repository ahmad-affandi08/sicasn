import { router, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { LuLogOut } from 'react-icons/lu';

import { PenangkapFlash } from '@/components/dialog/PenangkapFlash';
import { Logo } from '@/components/Logo';
import { TombolTema } from '@/components/TombolTema';
import { Button } from '@/components/ui/button';
import { teksAuth } from '@/lang/auth';
import { rute } from '@/lib/rute';
import { NavMember } from '@/layouts/NavMember';
import type { PropsBersama } from '@/types';

export function LayoutMember({ children }: { children: ReactNode }) {
    const { auth } = usePage<PropsBersama>().props;

    return (
        <div className="flex min-h-dvh flex-col">
            <header className="sticky top-0 z-40 border-b border-garis bg-latar/90 backdrop-blur">
                <div className="mx-auto flex h-16 w-full max-w-5xl items-center justify-between gap-3 px-4 sm:px-6">
                    <Logo />
                    <NavMember posisi="atas" />
                    <div className="flex items-center gap-1.5">
                        <span className="hidden max-w-48 truncate text-sm text-redup sm:block">{auth.pengguna?.nama}</span>
                        <TombolTema />
                        <Button variant="outline" onClick={() => router.post(rute.keluar)}>
                            <LuLogOut aria-hidden className="h-4 w-4" />
                            <span className="hidden sm:inline">{teksAuth.akun.keluar}</span>
                            <span className="sr-only sm:hidden">{teksAuth.akun.keluar}</span>
                        </Button>
                    </div>
                </div>
            </header>
            <main className="mx-auto w-full max-w-5xl flex-1 px-4 pt-8 pb-24 sm:px-6 sm:pb-12 md:py-12">{children}</main>
            <NavMember posisi="bawah" />
            <PenangkapFlash />
        </div>
    );
}
