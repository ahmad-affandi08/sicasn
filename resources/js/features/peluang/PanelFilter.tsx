import { useState, type FormEvent } from 'react';
import { LuSearch, LuSlidersHorizontal } from 'react-icons/lu';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { teksPeluang } from '@/lang/peluang';
import { cn } from '@/lib/utils';
import type { FilterPeluangItem } from '@/types';

import { hitungFilterAktif, URUTAN_AWAL } from './hitungFilter';

interface PropsPanelFilter {
    filter: FilterPeluangItem;
    tingkat: { kode: string; label: string }[];
    onTerapkan: (filter: FilterPeluangItem) => void;
}

const urutan = Object.entries(teksPeluang.filter.urutOpsi);
const tipe = Object.entries(teksPeluang.filter.tipeOpsi);

export function PanelFilter({ filter, tingkat, onTerapkan }: PropsPanelFilter) {
    const teks = teksPeluang.filter;
    const [nilai, setNilai] = useState(filter);
    const [terbuka, setTerbuka] = useState(false);
    const aktif = hitungFilterAktif(filter);

    function ubah(bagian: Partial<FilterPeluangItem>, langsung = false) {
        const baru = { ...nilai, ...bagian };
        setNilai(baru);

        if (langsung) {
            onTerapkan(baru);
        }
    }

    function kirim(kejadian: FormEvent) {
        kejadian.preventDefault();
        onTerapkan(nilai);
    }

    function hapus() {
        const kosong = { cari: '', tipe: '', tingkat: '', urut: URUTAN_AWAL };
        setNilai(kosong);
        onTerapkan(kosong);
    }

    return (
        <form onSubmit={kirim} role="search" className="flex flex-col gap-2">
            <div className="flex gap-2">
                <div className="relative flex-1">
                    <LuSearch aria-hidden className="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-redup" />
                    <Input type="search" aria-label={teks.cari} placeholder={teks.cari} className="pl-12" value={nilai.cari} onChange={(kejadian) => ubah({ cari: kejadian.target.value })} />
                </div>
                <Button type="submit">{teks.terapkan}</Button>
                <Button type="button" variant="outline" className="md:hidden" aria-expanded={terbuka} aria-controls="opsi-filter" onClick={() => setTerbuka((sekarang) => !sekarang)}>
                    <LuSlidersHorizontal aria-hidden className="h-4 w-4" />
                    {aktif > 0 ? teks.tombolAktif(aktif) : teks.tombol}
                </Button>
            </div>
            <div id="opsi-filter" className={cn('grid-cols-1 gap-2 md:flex md:flex-wrap md:items-center', terbuka ? 'grid' : 'hidden')}>
                <Select className="md:w-48" aria-label={teks.tipe} value={nilai.tipe} onChange={(kejadian) => ubah({ tipe: kejadian.target.value }, true)}>
                    <option value="">{teks.semuaTipe}</option>
                    {tipe.map(([kode, label]) => (
                        <option key={kode} value={kode}>
                            {label}
                        </option>
                    ))}
                </Select>
                <Select className="md:w-48" aria-label={teks.tingkat} value={nilai.tingkat} onChange={(kejadian) => ubah({ tingkat: kejadian.target.value }, true)}>
                    <option value="">{teks.semuaTingkat}</option>
                    {tingkat.map((opsi) => (
                        <option key={opsi.kode} value={opsi.kode}>
                            {opsi.label}
                        </option>
                    ))}
                </Select>
                <Select className="md:w-56" aria-label={teks.urut} value={nilai.urut} onChange={(kejadian) => ubah({ urut: kejadian.target.value }, true)}>
                    {urutan.map(([kode, label]) => (
                        <option key={kode} value={kode}>
                            {label}
                        </option>
                    ))}
                </Select>
                {(aktif > 0 || nilai.cari !== '') && (
                    <Button type="button" variant="ghost" onClick={hapus}>
                        {teks.reset}
                    </Button>
                )}
            </div>
        </form>
    );
}
