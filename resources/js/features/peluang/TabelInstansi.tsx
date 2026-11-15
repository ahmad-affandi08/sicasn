import { Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { teksPeluang } from '@/lang/peluang';
import { formatHari, formatRasio } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { InstansiPeluangItem } from '@/types';

import { catatanFormasi, tampilFormasi, tampilPelamar } from './AngkaInstansi';
import { KepalaUrut } from './KepalaUrut';
import { LencanaPersaingan } from './LencanaPersaingan';

interface PropsTabelInstansi {
    instansi: InstansiPeluangItem[];
    urut: string;
    onUrut: (urut: string) => void;
    onBuka: (instansi: InstansiPeluangItem) => void;
}

interface KolomUrut {
    label: string;
    urut: string;
}

const kolomUrut: KolomUrut[] = [
    { label: teksPeluang.kolom.instansi, urut: 'nama' },
    { label: teksPeluang.kolom.formasi, urut: 'formasi_terbanyak' },
    { label: teksPeluang.kolom.pelamar, urut: 'pelamar_tersedikit' },
    { label: teksPeluang.kolom.rasio, urut: 'rasio_terendah' },
];

export function TabelInstansi({ instansi, urut, onUrut, onBuka }: PropsTabelInstansi) {
    return (
        <Card>
            <Table>
                <TableHeader>
                    <TableRow className="hover:bg-transparent">
                        {kolomUrut.map((kolom) => (
                            <KepalaUrut key={kolom.urut} label={kolom.label} aktif={urut === kolom.urut} onUrut={() => onUrut(kolom.urut)} />
                        ))}
                        <TableHead>{teksPeluang.kolom.persaingan}</TableHead>
                        <TableHead>{teksPeluang.kolom.data}</TableHead>
                        <TableHead>
                            <span className="sr-only">{teksPeluang.kolom.rincian}</span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {instansi.map((butir) => (
                        <TableRow key={butir.id}>
                            <TableCell className="max-w-72 font-medium">
                                <span className="line-clamp-2 break-words" title={butir.nama}>
                                    {butir.nama}
                                </span>
                            </TableCell>
                            <TableCell>
                                {tampilFormasi(butir)}
                                {catatanFormasi(butir) && <span className="block text-xs text-redup">{catatanFormasi(butir)}</span>}
                            </TableCell>
                            <TableCell>{tampilPelamar(butir)}</TableCell>
                            <TableCell>{formatRasio(butir.rasio, butir.batasBawah)}</TableCell>
                            <TableCell>
                                <LencanaPersaingan tingkat={butir.tingkat} />
                            </TableCell>
                            <TableCell className="text-xs text-redup">
                                {butir.terbaru.sumberNama}
                                <span className="block">{formatHari(butir.terbaru.tanggal)}</span>
                            </TableCell>
                            <TableCell>
                                <div className="flex flex-col items-start gap-1.5">
                                    <Button variant="outline" size="sm" onClick={() => onBuka(butir)}>
                                        {teksPeluang.kartu.rincian}
                                    </Button>
                                    {butir.formasiAktif > 0 && (
                                        <Link href={`${rute.formasi}?instansi=${butir.id}`} className="text-xs font-medium text-primer underline">
                                            {teksPeluang.kartu.formasiAktif(butir.formasiAktif)}
                                        </Link>
                                    )}
                                </div>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </Card>
    );
}
