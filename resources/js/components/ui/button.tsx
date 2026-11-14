import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

export const buttonVariants = cva(
    'inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                default: 'bg-primer text-primer-teks shadow-sm hover:opacity-90',
                secondary: 'bg-primer-lembut text-primer hover:opacity-90',
                outline: 'border border-garis bg-kartu text-teks hover:bg-primer-lembut',
                ghost: 'text-teks hover:bg-primer-lembut',
                destructive: 'bg-bahaya text-white shadow-sm hover:opacity-90',
                link: 'text-primer underline-offset-4 hover:underline',
            },
            size: {
                default: 'h-11 px-5',
                sm: 'h-9 px-3.5',
                lg: 'h-12 px-7 text-base',
                icon: 'h-11 w-11',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

type ButtonProps = ComponentProps<'button'> &
    VariantProps<typeof buttonVariants> & {
        asChild?: boolean;
    };

export function Button({ className, variant, size, asChild = false, ...props }: ButtonProps) {
    const Component = asChild ? Slot : 'button';

    return <Component className={cn(buttonVariants({ variant, size }), className)} {...props} />;
}
