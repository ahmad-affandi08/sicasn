import { Head, Link } from '@inertiajs/react';

import { Catatan } from '@/components/Catatan';
import { HeaderHalaman } from '@/components/HeaderHalaman';
import { DaftarMode } from '@/components/latihan/DaftarMode';
import { RiwayatLatihan } from '@/components/latihan/RiwayatLatihan';
import { Button } from '@/components/ui/button';
import type { AmbangKategori, InfoMode, ItemRiwayat, KategoriSoal } from '@/features/latihan/tipe';
import { teksLatihan } from '@/lang/latihan';
import { LayoutMember } from '@/layouts/LayoutMember';
import { rute } from '@/lib/rute';

interface PropsHalaman {
    ketersediaan: Record<KategoriSoal, number>;
    percobaanBerjalanId: number | null;
    ambang: Record<KategoriSoal, AmbangKategori>;
    mode: InfoMode[];
    riwayat: ItemRiwayat[];
}

const URUTAN: KategoriSoal[] = ['twk', 'tiu', 'tkp'];

export default function Index({ ketersediaan, percobaanBerjalanId, ambang, mode, riwayat }: PropsHalaman) {
    const teks = teksLatihan;

    return (
        <LayoutMember>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            {percobaanBerjalanId !== null && (
                <div className="mb-6 flex flex-col gap-3 border-l-2 border-primer pl-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="font-medium">{teks.lanjutkan.judul}</p>
                        <p className="text-sm text-redup">{teks.lanjutkan.pesan}</p>
                    </div>
                    <Button asChild className="self-start sm:self-center">
                        <Link href={rute.kerjakanLatihan(percobaanBerjalanId)}>{teks.lanjutkan.tombol}</Link>
                    </Button>
                </div>
            )}

            <DaftarMode mode={mode} ketersediaan={ketersediaan} terkunci={percobaanBerjalanId !== null} />

            <section aria-labelledby="judul-ambang" className="mt-10">
                <h2 id="judul-ambang" className="text-lg font-medium">
                    {teks.ambang.judul}
                </h2>
                <p className="mt-1 max-w-2xl text-sm text-redup">{teks.ambang.sub}</p>
                <dl className="mt-4 grid grid-cols-3 divide-x divide-garis overflow-hidden rounded-kartu border border-garis bg-kartu">
                    {URUTAN.map((kategori) => (
                        <div key={kategori} className="flex flex-col gap-1 p-4">
                            <dt className="text-sm text-redup">
                                {teks.kategori[kategori]} <span className="hidden sm:inline">· {teks.kategoriLengkap[kategori]}</span>
                            </dt>
                            <dd className="text-2xl leading-none font-medium tabular-nums">
                                {ambang[kategori].resmi}
                                <span className="text-sm font-normal text-redup"> / {ambang[kategori].maks}</span>
                            </dd>
                            <dd className="text-xs text-redup">
                                {teks.ambang.jumlah}: {ambang[kategori].jumlah} · {teks.bank}: {teks.tersedia(ketersediaan[kategori])}
                            </dd>
                        </div>
                    ))}
                </dl>
                <Catatan className="mt-4">{teks.catatan}</Catatan>
            </section>

            <section aria-labelledby="judul-riwayat" className="mt-10">
                <h2 id="judul-riwayat" className="mb-3 text-lg font-medium">
                    {teks.riwayat.judul}
                </h2>
                <RiwayatLatihan riwayat={riwayat} />
            </section>
        </LayoutMember>
    );
}
