import { cn } from '@/lib/utils';
import type { TingkatPersainganKode } from '@/types';

import { posisiMeter, warnaTingkat } from './posisiMeter';

interface PropsMeterRasio {
    rasio: number | null;
    tingkat: TingkatPersainganKode;
    longgarMaks: number;
    ketatMin: number;
    batasBawah?: boolean;
    className?: string;
}

export function MeterRasio({ rasio, tingkat, longgarMaks, ketatMin, batasBawah = false, className }: PropsMeterRasio) {
    return (
        <div aria-hidden className={cn('relative h-3 w-full min-w-20', className)}>
            <div className="absolute inset-x-0 top-1/2 flex h-1.5 -translate-y-1/2 gap-px overflow-hidden rounded-full">
                <span className="flex-1 bg-sukses-lembut" />
                <span className="flex-1 bg-peringatan-lembut" />
                <span className="flex-1 bg-bahaya-lembut" />
            </div>
            {rasio !== null && (
                <span className={cn('absolute top-0 h-3 w-1 -translate-x-1/2 rounded-full ring-2 ring-kartu', warnaTingkat(tingkat))} style={{ left: `${posisiMeter(rasio, longgarMaks, ketatMin)}%` }}>
                    {batasBawah && <span className="absolute top-1/2 left-full ml-px h-0 w-0 -translate-y-1/2 border-y-4 border-l-4 border-y-transparent border-l-current text-redup" />}
                </span>
            )}
        </div>
    );
}
