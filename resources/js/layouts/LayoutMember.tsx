import { router, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

import { PenangkapFlash } from '@/components/dialog/PenangkapFlash';
import { Logo } from '@/components/Logo';
import { TombolTema } from '@/components/TombolTema';
import { Button } from '@/components/ui/button';
import { teksAuth } from '@/lang/auth';
import { NavMember } from '@/layouts/NavMember';
import { rute } from '@/lib/rute';
import { cn } from '@/lib/utils';
import type { PropsBersama } from '@/types';

interface PropsLayoutMember {
    children: ReactNode;
    lebar?: boolean;
}

export function LayoutMember({ children, lebar = false }: PropsLayoutMember) {
    const { auth } = usePage<PropsBersama>().props;
    const kolom = lebar ? 'max-w-7xl' : 'max-w-5xl';

    return (
        <div className="flex min-h-dvh flex-col">
            <header className="sticky top-0 z-40 border-b border-garis bg-latar/95 backdrop-blur">
                <div className={cn('mx-auto flex h-14 w-full items-center gap-6 px-4 sm:px-6', kolom)}>
                    <Logo />
                    <NavMember posisi="atas" />
                    <div className="ml-auto flex items-center gap-1">
                        <span className="mr-2 hidden max-w-48 truncate text-sm text-redup md:block">{auth.pengguna?.nama}</span>
                        <TombolTema />
                        <Button variant="ghost" onClick={() => router.post(rute.keluar)}>
                            {teksAuth.akun.keluar}
                        </Button>
                    </div>
                </div>
            </header>
            <main className={cn('mx-auto w-full flex-1 px-4 pt-6 pb-24 sm:px-6 sm:pb-12 md:pt-8', kolom)}>{children}</main>
            <NavMember posisi="bawah" />
            <PenangkapFlash />
        </div>
    );
}
