import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

export function Table({ className, ...props }: ComponentProps<'table'>) {
    return (
        <div className="w-full overflow-x-auto">
            <table className={cn('w-full caption-bottom text-left text-sm', className)} {...props} />
        </div>
    );
}

export function TableHeader({ className, ...props }: ComponentProps<'thead'>) {
    return <thead className={cn('border-b border-garis text-redup', className)} {...props} />;
}

export function TableBody({ className, ...props }: ComponentProps<'tbody'>) {
    return <tbody className={cn('[&_tr:last-child]:border-0', className)} {...props} />;
}

export function TableRow({ className, ...props }: ComponentProps<'tr'>) {
    return <tr className={cn('border-b border-garis hover:bg-primer-lembut/40', className)} {...props} />;
}

export function TableHead({ className, ...props }: ComponentProps<'th'>) {
    return <th className={cn('h-11 px-4 text-xs font-medium tracking-wide uppercase', className)} {...props} />;
}

export function TableCell({ className, ...props }: ComponentProps<'td'>) {
    return <td className={cn('px-4 py-3 align-middle', className)} {...props} />;
}
