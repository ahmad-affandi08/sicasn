import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiAkunNonaktif(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 240 200" {...props}>
            <ellipse cx="120" cy="104" rx="104" ry="86" fill="var(--ilustrasi-primer-lembut)" />
            <path d="M92 90 V70 a28 28 0 0 1 56 0 V90" stroke="var(--ilustrasi-primer)" strokeWidth="12" strokeLinecap="round" />
            <rect x="76" y="88" width="88" height="66" rx="16" fill="var(--ilustrasi-primer)" />
            <circle cx="120" cy="116" r="9" fill="var(--ilustrasi-glif)" />
            <rect x="116" y="120" width="8" height="16" rx="4" fill="var(--ilustrasi-glif)" />
            <circle cx="190" cy="44" r="8" fill="var(--ilustrasi-aksen)" />
            <circle cx="46" cy="140" r="5" fill="var(--ilustrasi-primer)" opacity=".4" />
        </KerangkaSvg>
    );
}
