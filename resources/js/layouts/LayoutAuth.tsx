import type { ReactNode } from 'react';

import { PenangkapFlash } from '@/components/dialog/PenangkapFlash';
import { Logo } from '@/components/Logo';
import { TombolTema } from '@/components/TombolTema';

export function LayoutAuth({ children }: { children: ReactNode }) {
    return (
        <div className="flex min-h-dvh flex-col">
            <header className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between px-4 sm:px-6">
                <Logo />
                <TombolTema />
            </header>
            <main className="flex flex-1 items-center justify-center px-4 py-8 sm:px-6">{children}</main>
            <PenangkapFlash />
        </div>
    );
}
