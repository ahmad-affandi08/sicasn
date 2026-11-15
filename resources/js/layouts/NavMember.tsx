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
    const atas = posisi === 'atas';

    return (
        <nav
            aria-label={teksPeluang.nav.utama}
            className={cn(atas ? 'hidden h-full items-stretch gap-1 sm:flex' : 'fixed inset-x-0 bottom-0 z-40 grid grid-cols-3 border-t border-garis bg-kartu pb-[env(safe-area-inset-bottom)] sm:hidden')}
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
                            'relative flex items-center justify-center text-sm font-medium transition-colors',
                            atas ? 'px-3' : 'min-h-14 flex-col gap-0.5 text-xs',
                            aktif ? 'text-teks' : 'text-redup hover:text-teks',
                            aktif && atas && 'after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:bg-primer',
                            aktif && !atas && 'text-primer before:absolute before:inset-x-6 before:top-0 before:h-0.5 before:bg-primer',
                        )}
                    >
                        {!atas && <Ikon aria-hidden className="h-5 w-5" />}
                        {butir.label}
                    </Link>
                );
            })}
        </nav>
    );
}
