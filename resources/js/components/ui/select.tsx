import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

export function Select({ className, children, ...props }: ComponentProps<'select'>) {
    return (
        <select
            className={cn('h-11 w-full rounded-lg border border-garis-input bg-kartu px-2.5 text-base text-teks disabled:cursor-not-allowed disabled:opacity-50 md:h-10 md:text-sm', className)}
            {...props}
        >
            {children}
        </select>
    );
}
