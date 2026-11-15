import { Link } from '@inertiajs/react';

import { teks } from '@/lang/id';

export function Logo() {
    return (
        <Link href="/" className="flex items-center gap-2 rounded-md" aria-label={`${teks.aplikasi}, ${teks.tagline}`}>
            <svg viewBox="0 0 28 28" className="h-7 w-7" aria-hidden focusable="false">
                <rect width="28" height="28" rx="7" fill="var(--primer)" />
                <path d="M8 14.5 L12.2 18.7 L20 9.5" stroke="var(--primer-teks)" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" fill="none" />
            </svg>
            <span className="text-base font-bold tracking-tight">{teks.aplikasi}</span>
        </Link>
    );
}
