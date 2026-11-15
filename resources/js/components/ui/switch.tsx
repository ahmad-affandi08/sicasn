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
                'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full border border-garis-input transition-colors disabled:opacity-50',
                checked ? 'border-primer bg-primer' : 'bg-permukaan',
                className,
            )}
            {...props}
        >
            <span
                aria-hidden
                className={cn('inline-block h-4 w-4 rounded-full transition-transform', checked ? 'translate-x-6 bg-primer-teks' : 'translate-x-1 bg-garis-input')}
            />
        </button>
    );
}
