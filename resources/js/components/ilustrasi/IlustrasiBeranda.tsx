import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

const baris = [
    { y: 138, lebar: 120, warna: 'var(--sukses)' },
    { y: 188, lebar: 96, warna: 'var(--peringatan)' },
    { y: 238, lebar: 132, warna: 'var(--bahaya)' },
];

export function IlustrasiBeranda(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 480 380" {...props}>
            <circle cx="240" cy="196" r="164" fill="var(--ilustrasi-primer-lembut)" />
            <circle cx="66" cy="92" r="10" fill="var(--ilustrasi-aksen)" />
            <circle cx="430" cy="322" r="14" fill="var(--ilustrasi-primer)" opacity=".25" />
            <circle cx="52" cy="290" r="7" fill="var(--ilustrasi-primer)" opacity=".4" />
            <rect x="86" y="72" width="308" height="236" rx="22" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <circle cx="112" cy="98" r="6" fill="var(--ilustrasi-aksen)" />
            <circle cx="132" cy="98" r="6" fill="var(--ilustrasi-primer)" opacity=".5" />
            <rect x="156" y="92" width="130" height="12" rx="6" fill="var(--ilustrasi-garis)" />
            {baris.map((item) => (
                <g key={item.y}>
                    <rect x="112" y={item.y} width="36" height="36" rx="11" fill="var(--ilustrasi-primer-lembut)" />
                    <rect x="162" y={item.y + 4} width={item.lebar} height="10" rx="5" fill="var(--ilustrasi-garis)" />
                    <rect x="162" y={item.y + 22} width={item.lebar - 36} height="8" rx="4" fill="var(--ilustrasi-garis)" opacity=".6" />
                    <rect x="318" y={item.y + 7} width="58" height="22" rx="11" fill={item.warna} opacity=".9" />
                </g>
            ))}
            <circle cx="352" cy="262" r="42" fill="var(--ilustrasi-kartu)" fillOpacity=".7" stroke="var(--ilustrasi-primer)" strokeWidth="10" />
            <path d="M382 292 L414 324" stroke="var(--ilustrasi-primer)" strokeWidth="14" strokeLinecap="round" />
            <rect x="326" y="40" width="116" height="64" rx="16" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <circle cx="356" cy="72" r="15" fill="var(--sukses)" />
            <path d="M349 72 L354 77 L363 67" stroke="var(--ilustrasi-glif)" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
            <rect x="380" y="62" width="48" height="8" rx="4" fill="var(--ilustrasi-garis)" />
            <rect x="380" y="78" width="32" height="8" rx="4" fill="var(--ilustrasi-garis)" opacity=".6" />
        </KerangkaSvg>
    );
}
