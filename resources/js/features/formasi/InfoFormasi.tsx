import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';

interface PropsInfoFormasi {
    label: string;
    children: ReactNode;
    penuh?: boolean;
}

export function InfoFormasi({ label, children, penuh = false }: PropsInfoFormasi) {
    return (
        <div className={cn('flex flex-col gap-0.5', penuh && 'col-span-2')}>
            <dt className="text-xs text-redup">{label}</dt>
            <dd className="text-sm font-medium break-words sm:text-base">{children}</dd>
        </div>
    );
}
