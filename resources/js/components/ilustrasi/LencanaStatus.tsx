import type { ReactNode } from 'react';

import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

interface PropsLencana extends PropsIlustrasi {
    warna: string;
    warnaLembut: string;
    children: ReactNode;
}

export function LencanaStatus({ warna, warnaLembut, children, ...sisa }: PropsLencana) {
    return (
        <KerangkaSvg viewBox="0 0 160 160" {...sisa}>
            <circle cx="80" cy="80" r="72" fill={warnaLembut} />
            <circle cx="80" cy="80" r="46" fill={warna} />
            <circle cx="132" cy="34" r="6" fill="var(--ilustrasi-aksen)" />
            <circle cx="24" cy="116" r="5" fill={warna} opacity=".35" />
            <circle cx="40" cy="30" r="3.5" fill={warna} opacity=".5" />
            <g stroke="var(--ilustrasi-glif)" strokeWidth="9" strokeLinecap="round" strokeLinejoin="round" fill="none">
                {children}
            </g>
        </KerangkaSvg>
    );
}
