import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiKosong(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 240 200" {...props}>
            <ellipse cx="120" cy="104" rx="104" ry="84" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="58" y="64" width="54" height="22" rx="9" fill="var(--ilustrasi-primer)" opacity=".35" />
            <rect x="58" y="76" width="124" height="82" rx="14" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <rect x="78" y="100" width="64" height="8" rx="4" fill="var(--ilustrasi-garis)" />
            <rect x="78" y="118" width="44" height="8" rx="4" fill="var(--ilustrasi-garis)" opacity=".6" />
            <circle cx="164" cy="136" r="24" fill="var(--ilustrasi-kartu)" fillOpacity=".8" stroke="var(--ilustrasi-primer)" strokeWidth="7" />
            <path d="M181 154 L198 171" stroke="var(--ilustrasi-primer)" strokeWidth="9" strokeLinecap="round" />
            <circle cx="40" cy="48" r="6" fill="var(--ilustrasi-aksen)" />
            <circle cx="206" cy="52" r="4" fill="var(--ilustrasi-primer)" opacity=".5" />
        </KerangkaSvg>
    );
}
