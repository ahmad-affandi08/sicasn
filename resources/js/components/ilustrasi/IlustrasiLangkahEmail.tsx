import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiLangkahEmail(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 120 120" {...props}>
            <circle cx="60" cy="60" r="56" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="24" y="36" width="72" height="50" rx="11" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-primer)" strokeWidth="4" />
            <path d="M28 44 L60 66 L92 44" stroke="var(--ilustrasi-primer)" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
            <circle cx="92" cy="34" r="10" fill="var(--ilustrasi-aksen)" />
            <path d="M88 34 L91 37 L96 31" stroke="var(--ilustrasi-glif)" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" />
        </KerangkaSvg>
    );
}
