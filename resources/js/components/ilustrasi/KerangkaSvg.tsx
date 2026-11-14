import type { ReactNode } from 'react';

import type { PropsIlustrasi } from './tipe';

interface PropsKerangka extends PropsIlustrasi {
    viewBox: string;
    children: ReactNode;
}

export function KerangkaSvg({ viewBox, className, judul, children }: PropsKerangka) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox={viewBox}
            fill="none"
            className={className}
            role={judul ? 'img' : undefined}
            aria-label={judul}
            aria-hidden={judul ? undefined : true}
            focusable="false"
        >
            {children}
        </svg>
    );
}
