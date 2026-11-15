import { Link, usePage } from '@inertiajs/react';
import type { IconType } from 'react-icons';
import { LuBriefcaseBusiness, LuChartColumn, LuUser } from 'react-icons/lu';

import { teksFormasi } from '@/lang/formasi';
import { teksPeluang } from '@/lang/peluang';
import { rute } from '@/lib/rute';
import { cn } from '@/lib/utils';

interface ItemNav {
    href: string;
    label: string;
    ikon: IconType;
}

const item: ItemNav[] = [
    { href: rute.formasi, label: teksFormasi.nav, ikon: LuBriefcaseBusiness },
    { href: rute.peluangInstansi, label: teksPeluang.nav.peluang, ikon: LuChartColumn },
    { href: rute.akun, label: teksPeluang.nav.akun, ikon: LuUser },
];

export function NavMember({ posisi }: { posisi: 'atas' | 'bawah' }) {
    const { url } = usePage();

    return (
        <nav
            aria-label={teksPeluang.nav.utama}
            className={cn(posisi === 'atas' ? 'hidden items-center gap-1 sm:flex' : 'fixed inset-x-0 bottom-0 z-40 grid grid-cols-3 border-t border-garis bg-kartu sm:hidden')}
        >
            {item.map((butir) => {
                const aktif = url.startsWith(butir.href);
                const Ikon = butir.ikon;

                return (
                    <Link
                        key={butir.href}
                        href={butir.href}
                        aria-current={aktif ? 'page' : undefined}
                        className={cn(
                            'flex min-h-14 items-center justify-center gap-2 text-sm font-medium transition-colors',
                            posisi === 'atas' ? 'min-h-11 rounded-xl px-4' : 'flex-col gap-0.5 text-xs',
                            aktif ? (posisi === 'atas' ? 'bg-primer text-primer-teks' : 'text-primer') : 'text-redup hover:text-primer',
                        )}
                    >
                        <Ikon aria-hidden className="h-5 w-5" />
                        {butir.label}
                    </Link>
                );
            })}
        </nav>
    );
}
