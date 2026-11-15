import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

export function Input({ className, type = 'text', ...props }: ComponentProps<'input'>) {
    return (
        <input
            type={type}
            className={cn(
                'flex h-11 w-full rounded-lg border border-garis-input bg-kartu px-3 text-base text-teks placeholder:text-redup disabled:cursor-not-allowed disabled:opacity-50 md:h-10 md:text-sm',
                className,
            )}
            {...props}
        />
    );
}
