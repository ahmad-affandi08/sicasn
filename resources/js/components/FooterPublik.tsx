import { LuExternalLink } from 'react-icons/lu';

import { Logo } from '@/components/Logo';
import { teks } from '@/lang/id';

export function FooterPublik() {
    return (
        <footer className="border-t border-garis bg-kartu">
            <div className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6 md:flex-row md:items-start md:justify-between">
                <div className="flex max-w-xl flex-col gap-4">
                    <Logo />
                    <p className="text-sm text-redup">{teks.footer.disclaimer}</p>
                </div>
                <div className="flex flex-col gap-3 text-sm md:items-end">
                    <a
                        href="https://sscasn.bkn.go.id"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 rounded-xl font-medium text-primer hover:underline"
                    >
                        {teks.footer.portalResmi}
                        <LuExternalLink aria-hidden className="h-4 w-4" />
                    </a>
                    <span className="text-redup">{teks.footer.hakCipta(new Date().getFullYear())}</span>
                </div>
            </div>
        </footer>
    );
}
