import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiLangkahChat(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 120 120" {...props}>
            <circle cx="60" cy="60" r="56" fill="var(--ilustrasi-primer-lembut)" />
            <path d="M32 40 a14 14 0 0 1 14 -14 h28 a14 14 0 0 1 14 14 v22 a14 14 0 0 1 -14 14 H58 L44 90 V76 h-2 a14 14 0 0 1 -10 -14 Z" fill="var(--ilustrasi-primer)" />
            <circle cx="48" cy="51" r="4" fill="var(--ilustrasi-glif)" />
            <circle cx="60" cy="51" r="4" fill="var(--ilustrasi-glif)" />
            <circle cx="72" cy="51" r="4" fill="var(--ilustrasi-glif)" />
            <circle cx="92" cy="30" r="6" fill="var(--ilustrasi-aksen)" />
        </KerangkaSvg>
    );
}
