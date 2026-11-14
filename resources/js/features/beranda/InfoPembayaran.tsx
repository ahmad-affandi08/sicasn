import { LuWallet } from 'react-icons/lu';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { teks } from '@/lang/id';
import type { InfoPembayaran as DataPembayaran } from '@/types';

export function InfoPembayaran({ info }: { info: DataPembayaran }) {
    return (
        <section aria-labelledby="judul-pembayaran" className="mx-auto w-full max-w-3xl px-4 pb-14 sm:px-6 md:pb-20">
            <Card>
                <CardHeader>
                    <div className="flex items-center gap-3">
                        <span className="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-primer-lembut text-primer">
                            <LuWallet aria-hidden className="h-5 w-5" />
                        </span>
                        <CardTitle id="judul-pembayaran">{teks.pembayaran.judul}</CardTitle>
                    </div>
                </CardHeader>
                <CardContent className="flex flex-col gap-4">
                    {info.hargaTeks && <p className="text-2xl font-bold text-primer">{info.hargaTeks}</p>}
                    {info.catatan && <p className="text-redup">{info.catatan}</p>}
                    {info.rekening.length > 0 && (
                        <ul className="flex flex-col gap-3 border-t border-garis pt-4">
                            {info.rekening.map((rekening) => (
                                <li key={`${rekening.bank}-${rekening.nomor}`} className="flex flex-col text-sm">
                                    <span className="font-medium">{rekening.bank}</span>
                                    <span className="text-lg tracking-wide">{rekening.nomor}</span>
                                    {rekening.atasNama && <span className="text-redup">{teks.pembayaran.atasNama(rekening.atasNama)}</span>}
                                </li>
                            ))}
                        </ul>
                    )}
                    <p className="text-sm text-redup">{teks.pembayaran.catatanKonfirmasi}</p>
                </CardContent>
            </Card>
        </section>
    );
}
