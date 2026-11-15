import type { ReactNode } from 'react';
import { LuInfo } from 'react-icons/lu';

import { cn } from '@/lib/utils';

export function Catatan({ children, className }: { children: ReactNode; className?: string }) {
    return (
        <p className={cn('flex gap-2 border-l-2 border-garis-input pl-3 text-sm text-redup', className)}>
            <LuInfo aria-hidden className="mt-0.5 h-4 w-4 shrink-0" />
            <span>{children}</span>
        </p>
    );
}
