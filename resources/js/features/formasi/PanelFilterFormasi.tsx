import { useCallback, useState } from 'react';

import { Combobox } from '@/components/form/Combobox';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { teksFormasi } from '@/lang/formasi';
import { muatOpsi } from '@/lib/opsiRemote';
import { rute } from '@/lib/rute';
import { cn } from '@/lib/utils';
import type { FilterFormasiItem, OpsiFormasiItem, OpsiPilihan } from '@/types';

import { FILTER_KOSONG, hitungFilterAktif } from './filter';

interface PropsPanelFilterFormasi {
    filter: FilterFormasiItem;
    opsi: OpsiFormasiItem;
    tingkat: { kode: string; label: string }[];
    onTerapkan: (filter: FilterFormasiItem) => void;
}

export function PanelFilterFormasi({ filter, opsi, tingkat, onTerapkan }: PropsPanelFilterFormasi) {
    const teks = teksFormasi.filter;
    const [nilai, setNilai] = useState(filter);
    const [wilayah, setWilayah] = useState<OpsiPilihan[]>(opsi.kabupatenKota);
    const [jurusan, setJurusan] = useState<OpsiPilihan[]>(opsi.jurusanTerpilih);
    const [instansi, setInstansi] = useState<OpsiPilihan[]>(opsi.instansiTerpilih ? [opsi.instansiTerpilih] : []);
    const muatJurusan = useCallback((kataKunci: string) => muatOpsi(rute.opsiJurusan, kataKunci), []);
    const muatInstansi = useCallback((kataKunci: string) => muatOpsi(rute.opsiInstansi, kataKunci), []);

    function ubah(bagian: Partial<FilterFormasiItem>) {
        setNilai((sekarang) => ({ ...sekarang, ...bagian }));
    }

    function pilihJenjang(kode: string) {
        ubah({ jenjang: nilai.jenjang.includes(kode) ? nilai.jenjang.filter((butir) => butir !== kode) : [...nilai.jenjang, kode] });
    }

    async function pilihProvinsi(teksProvinsi: string) {
        const id = teksProvinsi === '' ? null : Number(teksProvinsi);
        ubah({ provinsi: id, wilayah: null });
        setWilayah([]);

        if (id !== null) {
            setWilayah(await muatOpsi(rute.opsiWilayah(id)).catch(() => []));
        }
    }

    function hapus() {
        setNilai({ ...FILTER_KOSONG, cari: filter.cari });
        setWilayah([]);
        setJurusan([]);
        setInstansi([]);
        onTerapkan({ ...FILTER_KOSONG, cari: filter.cari });
    }

    const aktif = hitungFilterAktif(nilai);

    return (
        <form
            className="flex flex-col gap-4"
            onSubmit={(kejadian) => {
                kejadian.preventDefault();
                onTerapkan(nilai);
            }}
        >
            <fieldset className="flex flex-col gap-2">
                <legend className="text-sm font-medium">{teks.jenjang}</legend>
                <p className="text-xs text-redup">{teks.jenjangPetunjuk}</p>
                <div className="flex flex-wrap gap-2">
                    {opsi.jenjang.map((kode) => {
                        const dipilih = nilai.jenjang.includes(kode);

                        return (
                            <button
                                key={kode}
                                type="button"
                                aria-pressed={dipilih}
                                onClick={() => pilihJenjang(kode)}
                                className={cn('min-h-11 min-w-12 rounded-lg border px-3 text-sm font-medium transition-colors md:min-h-9', dipilih ? 'border-primer bg-primer text-primer-teks' : 'border-garis-input bg-kartu hover:bg-permukaan')}
                            >
                                {kode}
                            </button>
                        );
                    })}
                </div>
            </fieldset>

            <Combobox
                id="filter-jurusan"
                label={teks.jurusan}
                placeholder={teks.jurusanCari}
                banyak
                terpilih={jurusan}
                muatOpsi={muatJurusan}
                onUbah={(baru) => {
                    setJurusan(baru);
                    ubah({ jurusan: baru.map((butir) => Number(butir.nilai)) });
                }}
            />

            <div className="flex flex-col gap-2">
                <label htmlFor="filter-provinsi" className="text-sm font-medium">
                    {teks.provinsi}
                </label>
                <Select id="filter-provinsi" value={nilai.provinsi === null ? '' : String(nilai.provinsi)} onChange={(kejadian) => void pilihProvinsi(kejadian.target.value)}>
                    <option value="">{teks.semuaProvinsi}</option>
                    {opsi.provinsi.map((butir) => (
                        <option key={butir.nilai} value={butir.nilai}>
                            {butir.label} ({butir.jumlah.toLocaleString('id-ID')})
                        </option>
                    ))}
                </Select>
            </div>

            <div className="flex flex-col gap-2">
                <label htmlFor="filter-wilayah" className="text-sm font-medium">
                    {teks.wilayah}
                </label>
                <Select id="filter-wilayah" disabled={nilai.provinsi === null} value={nilai.wilayah === null ? '' : String(nilai.wilayah)} onChange={(kejadian) => ubah({ wilayah: kejadian.target.value === '' ? null : Number(kejadian.target.value) })}>
                    <option value="">{nilai.provinsi === null ? teks.pilihProvinsiDulu : teks.semuaWilayah}</option>
                    {wilayah.map((butir) => (
                        <option key={butir.nilai} value={butir.nilai}>
                            {butir.label} ({butir.jumlah.toLocaleString('id-ID')})
                        </option>
                    ))}
                </Select>
            </div>

            <Combobox
                id="filter-instansi"
                label={teks.instansi}
                placeholder={teks.instansiCari}
                terpilih={instansi}
                muatOpsi={muatInstansi}
                onUbah={(baru) => {
                    setInstansi(baru);
                    ubah({ instansi: baru[0] ? Number(baru[0].nilai) : null });
                }}
            />

            {opsi.jenisFormasi.length > 1 && (
                <div className="flex flex-col gap-2">
                    <label htmlFor="filter-jenis" className="text-sm font-medium">
                        {teks.jenisFormasi}
                    </label>
                    <Select id="filter-jenis" value={nilai.jenis} onChange={(kejadian) => ubah({ jenis: kejadian.target.value })}>
                        <option value="">{teks.semuaJenis}</option>
                        {opsi.jenisFormasi.map((butir) => (
                            <option key={butir.nilai} value={butir.nilai}>
                                {butir.label} ({butir.jumlah.toLocaleString('id-ID')})
                            </option>
                        ))}
                    </Select>
                </div>
            )}

            <div className="flex flex-col gap-2">
                <label htmlFor="filter-tingkat" className="text-sm font-medium">
                    {teks.tingkat}
                </label>
                <Select id="filter-tingkat" value={nilai.tingkat} onChange={(kejadian) => ubah({ tingkat: kejadian.target.value })}>
                    <option value="">{teks.semuaTingkat}</option>
                    {tingkat.map((butir) => (
                        <option key={butir.kode} value={butir.kode}>
                            {butir.label}
                        </option>
                    ))}
                </Select>
            </div>

            <div className="flex items-start gap-3">
                <Switch id="filter-sertakan" checked={nilai.sertakan} onCheckedChange={(dipilih) => ubah({ sertakan: dipilih })} aria-describedby="filter-sertakan-petunjuk" />
                <div>
                    <label htmlFor="filter-sertakan" className="text-sm font-medium">
                        {teks.sertakan}
                    </label>
                    <p id="filter-sertakan-petunjuk" className="text-xs text-redup">
                        {teks.sertakanPetunjuk}
                    </p>
                </div>
            </div>

            <div className="flex flex-col gap-2 border-t border-garis pt-4">
                <Button type="submit">{aktif > 0 ? `${teks.terapkan} (${aktif})` : teks.terapkan}</Button>
                <Button type="button" variant="ghost" onClick={hapus}>
                    {teks.hapus}
                </Button>
            </div>
        </form>
    );
}
