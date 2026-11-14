import { cva, type VariantProps } from 'class-variance-authority';
import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

const badgeVariants = cva('inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium', {
    variants: {
        variant: {
            default: 'bg-primer-lembut text-primer',
            sukses: 'bg-sukses-lembut text-sukses',
            peringatan: 'bg-peringatan-lembut text-peringatan',
            bahaya: 'bg-bahaya-lembut text-bahaya',
            info: 'bg-info-lembut text-info',
            netral: 'bg-garis text-redup',
        },
    },
    defaultVariants: { variant: 'default' },
});

type BadgeProps = ComponentProps<'span'> & VariantProps<typeof badgeVariants>;

export function Badge({ className, variant, ...props }: BadgeProps) {
    return <span className={cn(badgeVariants({ variant }), className)} {...props} />;
}
