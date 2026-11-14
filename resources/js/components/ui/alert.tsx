import { cva, type VariantProps } from 'class-variance-authority';
import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

const alertVariants = cva('flex gap-3 rounded-xl border p-4 text-sm', {
    variants: {
        variant: {
            info: 'border-info/30 bg-info-lembut text-teks',
            peringatan: 'border-peringatan/30 bg-peringatan-lembut text-teks',
            sukses: 'border-sukses/30 bg-sukses-lembut text-teks',
            bahaya: 'border-bahaya/30 bg-bahaya-lembut text-teks',
        },
    },
    defaultVariants: { variant: 'info' },
});

type PropsAlert = ComponentProps<'div'> & VariantProps<typeof alertVariants>;

export function Alert({ className, variant, ...props }: PropsAlert) {
    return <div role="status" className={cn(alertVariants({ variant }), className)} {...props} />;
}
