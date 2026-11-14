import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiLangkahMasuk(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 120 120" {...props}>
            <circle cx="60" cy="60" r="56" fill="var(--ilustrasi-primer-lembut)" />
            <path d="M44 56 V44 a16 16 0 0 1 30 -8" stroke="var(--ilustrasi-primer)" strokeWidth="8" strokeLinecap="round" />
            <rect x="34" y="54" width="52" height="38" rx="10" fill="var(--ilustrasi-primer)" />
            <circle cx="60" cy="71" r="6" fill="var(--ilustrasi-glif)" />
            <rect x="57.5" y="73" width="5" height="10" rx="2.5" fill="var(--ilustrasi-glif)" />
            <circle cx="90" cy="32" r="6" fill="var(--ilustrasi-aksen)" />
        </KerangkaSvg>
    );
}
