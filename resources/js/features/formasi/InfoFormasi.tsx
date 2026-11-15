import type { ReactNode } from 'react';

export function InfoFormasi({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="flex flex-col gap-0.5">
            <dt className="text-sm text-redup">{label}</dt>
            <dd className="font-medium break-words">{children}</dd>
        </div>
    );
}
