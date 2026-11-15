import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiPantau(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 240 200" {...props}>
            <ellipse cx="120" cy="104" rx="104" ry="84" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="46" y="58" width="68" height="92" rx="12" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <rect x="126" y="58" width="68" height="92" rx="12" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <rect x="58" y="96" width="44" height="6" rx="3" fill="var(--ilustrasi-garis)" />
            <rect x="58" y="110" width="30" height="6" rx="3" fill="var(--ilustrasi-garis)" opacity=".7" />
            <rect x="138" y="96" width="44" height="6" rx="3" fill="var(--ilustrasi-garis)" />
            <rect x="138" y="110" width="36" height="6" rx="3" fill="var(--ilustrasi-garis)" opacity=".7" />
            <rect x="58" y="128" width="44" height="8" rx="4" fill="var(--ilustrasi-primer)" opacity=".4" />
            <rect x="138" y="128" width="44" height="8" rx="4" fill="var(--ilustrasi-aksen)" opacity=".7" />
            <path d="M58 58 V82 L68 74 L78 82 V58 Z" fill="var(--ilustrasi-aksen)" />
            <path d="M138 58 V82 L148 74 L158 82 V58 Z" fill="var(--ilustrasi-primer)" />
            <circle cx="206" cy="50" r="5" fill="var(--ilustrasi-aksen)" />
        </KerangkaSvg>
    );
}
