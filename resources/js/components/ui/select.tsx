import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

export function Select({ className, children, ...props }: ComponentProps<'select'>) {
    return (
        <select
            className={cn('h-11 w-full rounded-xl border border-garis bg-kartu px-3 text-base text-teks disabled:cursor-not-allowed disabled:opacity-50', className)}
            {...props}
        >
            {children}
        </select>
    );
}
