import { Logo } from '@/components/Logo';
import { teks } from '@/lang/id';

export function FooterPublik() {
    return (
        <footer className="border-t border-garis">
            <div className="mx-auto grid w-full max-w-6xl gap-6 px-4 py-10 text-sm sm:px-6 md:grid-cols-[1fr_auto] md:items-start">
                <div className="flex max-w-xl flex-col gap-3">
                    <Logo />
                    <p className="text-redup">{teks.footer.disclaimer}</p>
                </div>
                <div className="flex flex-col gap-2 md:items-end">
                    <a href="https://sscasn.bkn.go.id" target="_blank" rel="noopener noreferrer" className="font-medium text-primer underline-offset-4 hover:underline">
                        {teks.footer.portalResmi} ↗
                    </a>
                    <span className="text-redup">{teks.footer.hakCipta(new Date().getFullYear())}</span>
                </div>
            </div>
        </footer>
    );
}
