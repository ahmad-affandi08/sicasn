import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiLangkahTransfer(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 120 120" {...props}>
            <circle cx="60" cy="60" r="56" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="24" y="36" width="64" height="44" rx="10" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-primer)" strokeWidth="4" />
            <rect x="24" y="48" width="64" height="9" fill="var(--ilustrasi-primer)" />
            <rect x="34" y="66" width="22" height="6" rx="3" fill="var(--ilustrasi-garis)" />
            <circle cx="86" cy="78" r="16" fill="var(--ilustrasi-aksen)" />
            <path d="M80 78 H92 M88 73 L93 78 L88 83" stroke="var(--ilustrasi-glif)" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round" />
        </KerangkaSvg>
    );
}
