import type { ReactNode } from 'react';

interface PropsHeaderHalaman {
    judul: string;
    sub?: string;
    aksi?: ReactNode;
}

export function HeaderHalaman({ judul, sub, aksi }: PropsHeaderHalaman) {
    return (
        <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div className="flex flex-col gap-1">
                <h1 className="text-2xl leading-tight font-medium sm:text-[1.75rem]">{judul}</h1>
                {sub && <p className="max-w-2xl text-sm text-redup sm:text-base">{sub}</p>}
            </div>
            {aksi && <div className="flex shrink-0 flex-wrap gap-2">{aksi}</div>}
        </div>
    );
}
