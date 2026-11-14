import { Link } from '@inertiajs/react';

import { teks } from '@/lang/id';

export function Logo() {
    return (
        <Link href="/" className="flex items-center gap-2.5 rounded-lg" aria-label={`${teks.aplikasi}, ${teks.tagline}`}>
            <svg viewBox="0 0 40 40" className="h-9 w-9" aria-hidden focusable="false">
                <rect width="40" height="40" rx="11" fill="var(--primer)" />
                <path d="M12 21.5 L18 27.5 L29 14" stroke="var(--primer-teks)" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" fill="none" />
            </svg>
            <span className="flex flex-col leading-none">
                <span className="text-lg font-bold tracking-wide">{teks.aplikasi}</span>
                <span className="hidden text-xs text-redup sm:block">{teks.tagline}</span>
            </span>
        </Link>
    );
}
