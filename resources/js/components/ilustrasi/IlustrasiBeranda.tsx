import { KerangkaSvg } from './KerangkaSvg';
import type { PropsIlustrasi } from './tipe';

const baris = [
    { y: 86, nama: 128, posisi: 46, warna: 'var(--sukses)', lencana: 52 },
    { y: 132, nama: 104, posisi: 118, warna: 'var(--peringatan)', lencana: 60 },
    { y: 178, nama: 144, posisi: 150, warna: 'var(--peringatan)', lencana: 60 },
    { y: 224, nama: 116, posisi: 196, warna: 'var(--bahaya)', lencana: 48 },
    { y: 270, nama: 136, posisi: 232, warna: 'var(--bahaya)', lencana: 48 },
];

export function IlustrasiBeranda(props: PropsIlustrasi) {
    return (
        <KerangkaSvg viewBox="0 0 480 340" {...props}>
            <rect x="8" y="8" width="464" height="324" rx="12" fill="var(--ilustrasi-kartu)" stroke="var(--ilustrasi-garis)" strokeWidth="2" />
            <rect x="8" y="8" width="464" height="44" rx="12" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="8" y="40" width="464" height="12" fill="var(--ilustrasi-primer-lembut)" />
            <rect x="28" y="24" width="86" height="10" rx="5" fill="var(--ilustrasi-primer)" opacity=".55" />
            <rect x="196" y="24" width="52" height="10" rx="5" fill="var(--ilustrasi-primer)" opacity=".3" />
            <rect x="296" y="24" width="60" height="10" rx="5" fill="var(--ilustrasi-primer)" opacity=".3" />
            <rect x="404" y="24" width="48" height="10" rx="5" fill="var(--ilustrasi-primer)" opacity=".3" />
            {baris.map((item) => (
                <g key={item.y}>
                    <line x1="8" x2="472" y1={item.y - 16} y2={item.y - 16} stroke="var(--ilustrasi-garis)" strokeWidth="1.5" opacity=".7" />
                    <rect x="28" y={item.y} width={item.nama} height="10" rx="5" fill="var(--ilustrasi-teks)" opacity=".18" />
                    <rect x="28" y={item.y + 16} width={item.nama * 0.6} height="8" rx="4" fill="var(--ilustrasi-teks)" opacity=".08" />
                    <rect x="196" y={item.y + 6} width="52" height="10" rx="5" fill="var(--ilustrasi-teks)" opacity=".1" />
                    <rect x="296" y={item.y + 9} width="21" height="6" rx="3" fill="var(--sukses)" opacity=".22" />
                    <rect x="318" y={item.y + 9} width="21" height="6" fill="var(--peringatan)" opacity=".22" />
                    <rect x="340" y={item.y + 9} width="21" height="6" rx="3" fill="var(--bahaya)" opacity=".22" />
                    <rect x={296 + item.posisi * 0.32} y={item.y + 4} width="4" height="16" rx="2" fill={item.warna} />
                    <rect x="404" y={item.y + 3} width={item.lencana} height="18" rx="6" fill={item.warna} opacity=".16" />
                    <rect x="412" y={item.y + 9} width="28" height="6" rx="3" fill={item.warna} />
                </g>
            ))}
        </KerangkaSvg>
    );
}
