import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

export function Textarea({ className, ...props }: ComponentProps<'textarea'>) {
    return (
        <textarea
            className={cn(
                'flex min-h-24 w-full rounded-xl border border-garis bg-kartu px-4 py-3 text-base text-teks placeholder:text-redup disabled:cursor-not-allowed disabled:opacity-50',
                className,
            )}
            {...props}
        />
    );
}
