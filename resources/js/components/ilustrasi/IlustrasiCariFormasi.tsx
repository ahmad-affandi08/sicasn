import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiCariFormasi(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 240 200" {...props}>
            <ellipse cx="120" cy="104" rx="104" ry="84" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="50" y="48" width="140" height="104" rx="14" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <rect x="66" y="66" width="52" height="8" rx="4" fill="var(--ilustrasi-primer)" opacity=".6" />
            <rect x="66" y="84" width="108" height="6" rx="3" fill="var(--ilustrasi-garis)" />
            <rect x="66" y="98" width="86" height="6" rx="3" fill="var(--ilustrasi-garis)" opacity=".7" />
            <rect x="66" y="116" width="40" height="14" rx="7" fill="var(--ilustrasi-aksen)" opacity=".85" />
            <rect x="112" y="116" width="40" height="14" rx="7" fill="var(--ilustrasi-primer)" opacity=".35" />
            <circle cx="168" cy="132" r="22" fill="var(--ilustrasi-kartu)" fillOpacity=".85" stroke="var(--ilustrasi-primer)" strokeWidth="7" />
            <path d="M184 148 L200 164" stroke="var(--ilustrasi-primer)" strokeWidth="9" strokeLinecap="round" />
            <circle cx="40" cy="52" r="6" fill="var(--ilustrasi-aksen)" />
        </KerangkaSvg>
    );
}
