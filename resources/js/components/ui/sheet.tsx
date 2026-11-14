import * as SheetPrimitive from '@radix-ui/react-dialog';
import type { ComponentProps } from 'react';
import { LuX } from 'react-icons/lu';

import { cn } from '@/lib/utils';

export const Sheet = SheetPrimitive.Root;
export const SheetTrigger = SheetPrimitive.Trigger;
export const SheetClose = SheetPrimitive.Close;

export function SheetContent({ className, children, ...props }: ComponentProps<typeof SheetPrimitive.Content>) {
    return (
        <SheetPrimitive.Portal>
            <SheetPrimitive.Overlay className="dialog-overlay fixed inset-0 z-50 bg-black/50" />
            <SheetPrimitive.Content
                className={cn(
                    'sheet-konten fixed inset-y-0 right-0 z-50 flex h-dvh w-[85%] max-w-sm flex-col gap-4 border-l border-garis bg-kartu p-6 text-teks shadow-2xl',
                    className,
                )}
                {...props}
            >
                {children}
                <SheetPrimitive.Close
                    aria-label="Tutup"
                    className="absolute top-4 right-4 inline-flex h-11 w-11 items-center justify-center rounded-xl text-redup hover:bg-primer-lembut"
                >
                    <LuX aria-hidden className="h-5 w-5" />
                </SheetPrimitive.Close>
            </SheetPrimitive.Content>
        </SheetPrimitive.Portal>
    );
}

export function SheetTitle({ className, ...props }: ComponentProps<typeof SheetPrimitive.Title>) {
    return <SheetPrimitive.Title className={cn('text-lg font-medium', className)} {...props} />;
}

export function SheetDescription({ className, ...props }: ComponentProps<typeof SheetPrimitive.Description>) {
    return <SheetPrimitive.Description className={cn('text-sm text-redup', className)} {...props} />;
}
