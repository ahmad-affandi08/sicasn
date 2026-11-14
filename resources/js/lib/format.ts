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
