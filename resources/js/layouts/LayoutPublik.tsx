import type { ReactNode } from 'react';

import { PenangkapFlash } from '@/components/dialog/PenangkapFlash';
import { FooterPublik } from '@/components/FooterPublik';
import { HeaderPublik } from '@/components/HeaderPublik';

export function LayoutPublik({ children }: { children: ReactNode }) {
    return (
        <div className="flex min-h-dvh flex-col">
            <HeaderPublik />
            <main className="flex-1">{children}</main>
            <FooterPublik />
            <PenangkapFlash />
        </div>
    );
}
