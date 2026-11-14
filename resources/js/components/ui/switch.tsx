import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

interface PropsSwitch extends Omit<ComponentProps<'button'>, 'onChange'> {
    checked: boolean;
    onCheckedChange: (nilai: boolean) => void;
}

export function Switch({ checked, onCheckedChange, className, ...props }: PropsSwitch) {
    return (
        <button
            type="button"
            role="switch"
            aria-checked={checked}
            onClick={() => onCheckedChange(!checked)}
            className={cn(
                'relative inline-flex h-7 w-12 shrink-0 items-center rounded-full border border-garis transition-colors disabled:opacity-50',
                checked ? 'bg-primer' : 'bg-garis',
                className,
            )}
            {...props}
        >
            <span
                aria-hidden
                className={cn('inline-block h-5 w-5 rounded-full bg-white shadow transition-transform', checked ? 'translate-x-6' : 'translate-x-1')}
            />
        </button>
    );
}
