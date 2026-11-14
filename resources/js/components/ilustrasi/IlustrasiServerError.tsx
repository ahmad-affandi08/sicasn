import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiServerError(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 320 220" {...props}>
            <ellipse cx="160" cy="116" rx="144" ry="96" fill="var(--bahaya-lembut)" />
            <rect x="96" y="48" width="128" height="40" rx="12" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <rect x="96" y="98" width="128" height="40" rx="12" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <rect x="96" y="148" width="128" height="40" rx="12" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <circle cx="116" cy="68" r="6" fill="var(--sukses)" />
            <circle cx="116" cy="118" r="6" fill="var(--bahaya)" />
            <circle cx="116" cy="168" r="6" fill="var(--sukses)" />
            <rect x="136" y="64" width="62" height="8" rx="4" fill="var(--ilustrasi-garis)" />
            <rect x="136" y="114" width="62" height="8" rx="4" fill="var(--ilustrasi-garis)" />
            <rect x="136" y="164" width="62" height="8" rx="4" fill="var(--ilustrasi-garis)" />
            <circle cx="238" cy="62" r="8" fill="var(--ilustrasi-aksen)" />
        </KerangkaSvg>
    );
}
