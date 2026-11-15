const pemformatTanggal = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'Asia/Jakarta',
});

export function formatWaktu(iso: string | null): string {
    if (!iso) {
        return '-';
    }

    const tanggal = new Date(iso);

    return Number.isNaN(tanggal.getTime()) ? '-' : pemformatTanggal.format(tanggal);
}

const pemformatHari = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' });

export function formatHari(tanggal: string | null): string {
    if (!tanggal) {
        return '-';
    }

    const nilai = new Date(`${tanggal.slice(0, 10)}T00:00:00+07:00`);

    return Number.isNaN(nilai.getTime()) ? '-' : pemformatHari.format(nilai);
}

export function formatAngka(nilai: number | null, batasBawah = false): string {
    if (nilai === null) {
        return '-';
    }

    return `${batasBawah ? '≥ ' : ''}${nilai.toLocaleString('id-ID')}`;
}

export function formatRasio(nilai: number | null, batasBawah = false): string {
    if (nilai === null) {
        return '-';
    }

    return `${batasBawah ? '≥ ' : ''}${nilai.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })}`;
}
