import type { ReactNode } from 'react';

import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

interface PropsBidangForm {
    id: string;
    label: string;
    error?: string;
    petunjuk?: string;
    className?: string;
    children: ReactNode;
}

export function BidangForm({ id, label, error, petunjuk, className, children }: PropsBidangForm) {
    return (
        <div className={cn('flex flex-col gap-2', className)}>
            <Label htmlFor={id}>{label}</Label>
            {children}
            {petunjuk && !error && (
                <p id={`${id}-petunjuk`} className="text-sm text-redup">
                    {petunjuk}
                </p>
            )}
            {error && (
                <p id={`${id}-galat`} role="alert" className="text-sm font-medium text-bahaya">
                    {error}
                </p>
            )}
        </div>
    );
}
