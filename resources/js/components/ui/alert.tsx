import { cva, type VariantProps } from 'class-variance-authority';
import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

const alertVariants = cva('flex gap-3 rounded-lg border border-garis border-l-4 bg-kartu px-4 py-3 text-sm text-teks', {
    variants: {
        variant: {
            info: 'border-l-info',
            peringatan: 'border-l-peringatan',
            sukses: 'border-l-sukses',
            bahaya: 'border-l-bahaya',
        },
    },
    defaultVariants: { variant: 'info' },
});

type PropsAlert = ComponentProps<'div'> & VariantProps<typeof alertVariants>;

export function Alert({ className, variant, ...props }: PropsAlert) {
    return <div role="status" className={cn(alertVariants({ variant }), className)} {...props} />;
}
