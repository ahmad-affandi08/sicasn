import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiNotFound(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 320 220" {...props}>
            <ellipse cx="160" cy="116" rx="144" ry="96" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="52" y="64" width="216" height="104" rx="20" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <text x="160" y="140" textAnchor="middle" fontSize="60" fontWeight="700" fill="var(--ilustrasi-primer)" fontFamily="Roboto, sans-serif">
                404
            </text>
            <circle cx="62" cy="52" r="8" fill="var(--ilustrasi-aksen)" />
            <circle cx="262" cy="184" r="6" fill="var(--ilustrasi-primer)" opacity=".4" />
        </KerangkaSvg>
    );
}
