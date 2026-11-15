import { teks } from '@/lang/id';
import type { InfoPembayaran as DataPembayaran } from '@/types';

export function InfoPembayaran({ info }: { info: DataPembayaran }) {
    return (
        <section aria-labelledby="judul-pembayaran" className="border-t border-garis bg-permukaan">
            <div className="mx-auto grid w-full max-w-6xl gap-8 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-[1fr_2fr] lg:gap-16">
                <div className="flex flex-col gap-2">
                    <h2 id="judul-pembayaran" className="text-2xl font-medium sm:text-3xl">
                        {teks.pembayaran.judul}
                    </h2>
                    {info.hargaTeks && <p className="text-3xl font-medium text-primer">{info.hargaTeks}</p>}
                    {info.catatan && <p className="text-redup">{info.catatan}</p>}
                </div>
                <div className="flex flex-col gap-5">
                    {info.rekening.length > 0 && (
                        <ul className="grid gap-4 sm:grid-cols-2">
                            {info.rekening.map((rekening) => (
                                <li key={`${rekening.bank}-${rekening.nomor}`} className="flex flex-col rounded-kartu border border-garis bg-kartu p-4 text-sm">
                                    <span className="text-redup">{rekening.bank}</span>
                                    <span className="text-lg font-medium tracking-wide">{rekening.nomor}</span>
                                    {rekening.atasNama && <span className="text-redup">{teks.pembayaran.atasNama(rekening.atasNama)}</span>}
                                </li>
                            ))}
                        </ul>
                    )}
                    <p className="text-sm text-redup">{teks.pembayaran.catatanKonfirmasi}</p>
                </div>
            </div>
        </section>
    );
}
