import * as AlertDialogPrimitive from '@radix-ui/react-alert-dialog';
import type { ComponentProps } from 'react';

import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export const AlertDialog = AlertDialogPrimitive.Root;
export const AlertDialogTrigger = AlertDialogPrimitive.Trigger;
export const AlertDialogPortal = AlertDialogPrimitive.Portal;

export function AlertDialogOverlay({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Overlay>) {
    return (
        <AlertDialogPrimitive.Overlay
            className={cn('dialog-overlay fixed inset-0 z-50 bg-black/55 backdrop-blur-[2px]', className)}
            {...props}
        />
    );
}

function fokusAwalTanpaBatal(kejadian: Event) {
    const konten = kejadian.currentTarget as HTMLElement | null;

    if (konten === null || konten.querySelector('[data-batal]') !== null || konten.contains(document.activeElement)) {
        return;
    }

    const sasaran = konten.querySelector<HTMLElement>('input:not([disabled]), button:not([disabled])');

    if (sasaran !== null) {
        kejadian.preventDefault();
        sasaran.focus({ preventScroll: true });
    }
}

export function AlertDialogContent({ className, onOpenAutoFocus, ...props }: ComponentProps<typeof AlertDialogPrimitive.Content>) {
    return (
        <AlertDialogPortal>
            <AlertDialogOverlay />
            <AlertDialogPrimitive.Content
                onOpenAutoFocus={(kejadian) => {
                    onOpenAutoFocus?.(kejadian);
                    fokusAwalTanpaBatal(kejadian);
                }}
                className={cn(
                    'dialog-konten fixed top-1/2 left-1/2 z-50 flex max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 flex-col gap-4 overflow-y-auto rounded-xl border border-garis bg-kartu p-6 text-teks shadow-xl sm:p-7',
                    className,
                )}
                {...props}
            />
        </AlertDialogPortal>
    );
}

export function AlertDialogHeader({ className, ...props }: ComponentProps<'div'>) {
    return <div className={cn('flex flex-col items-center gap-2 text-center', className)} {...props} />;
}

export function AlertDialogFooter({ className, ...props }: ComponentProps<'div'>) {
    return <div className={cn('flex flex-col-reverse gap-2 sm:flex-row sm:justify-center', className)} {...props} />;
}

export function AlertDialogTitle({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Title>) {
    return <AlertDialogPrimitive.Title className={cn('text-lg font-medium leading-snug', className)} {...props} />;
}

export function AlertDialogDescription({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Description>) {
    return <AlertDialogPrimitive.Description className={cn('text-sm text-redup', className)} {...props} />;
}

export function AlertDialogAction({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Action>) {
    return <AlertDialogPrimitive.Action className={cn(buttonVariants(), 'sm:min-w-32', className)} {...props} />;
}

export function AlertDialogCancel({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Cancel>) {
    return <AlertDialogPrimitive.Cancel data-batal="" className={cn(buttonVariants({ variant: 'outline' }), 'sm:min-w-32', className)} {...props} />;
}
