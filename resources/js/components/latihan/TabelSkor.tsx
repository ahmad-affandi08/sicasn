import { LuCircleCheck, LuCircleMinus } from 'react-icons/lu';

import type { HasilKategori } from '@/features/latihan/tipe';
import { teksLatihan } from '@/lang/latihan';

export function TabelSkor({ kategori }: { kategori: HasilKategori[] }) {
    const teks = teksLatihan.hasil;

    return (
        <div role="region" aria-label={teks.judul} tabIndex={0} className="relative overflow-x-auto focus-visible:ring-2 focus-visible:ring-primer">
            <table className="w-full min-w-[34rem] text-left text-sm">
                <caption className="sr-only">{teks.judul}</caption>
                <thead>
                    <tr className="border-b border-garis text-redup">
                        <th scope="col" className="py-2 pr-4 font-medium">
                            {teks.tes}
                        </th>
                        <th scope="col" className="py-2 pr-4 text-right font-medium">
                            {teks.skor}
                        </th>
                        <th scope="col" className="py-2 pr-4 text-right font-medium">
                            {teks.ambang}
                        </th>
                        <th scope="col" className="py-2 pr-4 text-right font-medium">
                            {teks.benar}
                        </th>
                        <th scope="col" className="py-2 pr-4 text-right font-medium">
                            {teks.salah}
                        </th>
                        <th scope="col" className="py-2 pr-4 text-right font-medium">
                            {teks.kosong}
                        </th>
                        <th scope="col" className="py-2 font-medium">
                            <span className="sr-only">Status</span>
                        </th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-garis">
                    {kategori.map((butir) => (
                        <tr key={butir.kategori}>
                            <th scope="row" className="py-3 pr-4 font-medium">
                                {teksLatihan.kategori[butir.kategori]}
                                <span className="block text-xs font-normal text-redup">{teksLatihan.kategoriLengkap[butir.kategori]}</span>
                            </th>
                            <td className="py-3 pr-4 text-right tabular-nums">
                                <span className="font-medium">{butir.skor}</span> <span className="text-redup">/ {butir.skorMaks}</span>
                            </td>
                            <td className="py-3 pr-4 text-right tabular-nums">{butir.ambangSetara}</td>
                            <td className="py-3 pr-4 text-right tabular-nums" title={butir.kategori === 'tkp' ? teks.terbaik : teks.benar}>
                                {butir.benar}
                            </td>
                            <td className="py-3 pr-4 text-right tabular-nums" title={butir.kategori === 'tkp' ? teks.kurangTepat : teks.salah}>
                                {butir.salah}
                            </td>
                            <td className="py-3 pr-4 text-right tabular-nums">{butir.kosong}</td>
                            <td className="py-3">
                                <span className={butir.tercapai ? 'inline-flex items-center gap-1 font-medium text-sukses' : 'inline-flex items-center gap-1 text-redup'}>
                                    {butir.tercapai ? <LuCircleCheck aria-hidden className="h-4 w-4" /> : <LuCircleMinus aria-hidden className="h-4 w-4" />}
                                    {butir.tercapai ? teks.tercapai : teks.belumTercapai}
                                </span>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
