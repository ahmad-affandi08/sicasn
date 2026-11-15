import type { FilterFormasiItem } from '@/types';

export const URUTAN_AWAL = 'rasio_terendah';

export const FILTER_KOSONG: FilterFormasiItem = {
    cari: '',
    jenjang: [],
    jurusan: [],
    provinsi: null,
    wilayah: null,
    instansi: null,
    jenis: '',
    tingkat: '',
    urut: URUTAN_AWAL,
    sertakan: false,
};

export function hitungFilterAktif(filter: FilterFormasiItem): number {
    return [
        filter.jenjang.length > 0,
        filter.jurusan.length > 0,
        filter.provinsi !== null,
        filter.wilayah !== null,
        filter.instansi !== null,
        filter.jenis !== '',
        filter.tingkat !== '',
        filter.urut !== URUTAN_AWAL,
        filter.sertakan,
    ].filter(Boolean).length;
}

export function parameterFilter(filter: FilterFormasiItem, halaman?: number): Record<string, string | number | string[] | number[]> {
    const parameter: Record<string, string | number | string[] | number[]> = {};

    if (filter.cari !== '') {
        parameter.cari = filter.cari;
    }

    if (filter.jenjang.length > 0) {
        parameter.jenjang = filter.jenjang;
    }

    if (filter.jurusan.length > 0) {
        parameter.jurusan = filter.jurusan;
    }

    if (filter.provinsi !== null) {
        parameter.provinsi = filter.provinsi;
    }

    if (filter.wilayah !== null) {
        parameter.wilayah = filter.wilayah;
    }

    if (filter.instansi !== null) {
        parameter.instansi = filter.instansi;
    }

    if (filter.jenis !== '') {
        parameter.jenis = filter.jenis;
    }

    if (filter.tingkat !== '') {
        parameter.tingkat = filter.tingkat;
    }

    if (filter.urut !== URUTAN_AWAL) {
        parameter.urut = filter.urut;
    }

    if (filter.sertakan) {
        parameter.sertakan = '1';
    }

    if (halaman !== undefined && halaman > 1) {
        parameter.halaman = halaman;
    }

    return parameter;
}
