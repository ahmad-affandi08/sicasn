import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiLogin(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 320 240" {...props}>
            <ellipse cx="160" cy="124" rx="140" ry="104" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="82" y="46" width="156" height="150" rx="20" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <circle cx="160" cy="88" r="20" fill="var(--ilustrasi-primer)" />
            <path d="M150 88 a10 10 0 0 1 20 0" stroke="var(--ilustrasi-glif)" strokeWidth="4" strokeLinecap="round" />
            <rect x="104" y="124" width="112" height="14" rx="7" fill="var(--ilustrasi-garis)" />
            <rect x="104" y="148" width="112" height="14" rx="7" fill="var(--ilustrasi-garis)" />
            <rect x="124" y="172" width="72" height="12" rx="6" fill="var(--ilustrasi-primer)" />
            <circle cx="256" cy="62" r="8" fill="var(--ilustrasi-aksen)" />
            <circle cx="64" cy="170" r="6" fill="var(--ilustrasi-primer)" opacity=".4" />
        </KerangkaSvg>
    );
}
