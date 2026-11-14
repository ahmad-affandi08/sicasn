import { Head, Link, router } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { LuChevronLeft, LuChevronRight, LuSearch, LuUserPlus } from 'react-icons/lu';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { IlustrasiKosong } from '@/components/ilustrasi';
import { LencanaAkun, LencanaEmail } from '@/components/StatusAkun';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import { formatWaktu } from '@/lib/format';
import { rute } from '@/lib/rute';
import type { PenggunaItem } from '@/types';

interface PropsHalaman {
    pengguna: PenggunaItem[];
    paginasi: { total: number; halaman: number; terakhir: number };
    filter: { cari: string; status: string };
}

export default function Index({ pengguna, paginasi, filter }: PropsHalaman) {
    const teks = teksAdmin.pengguna;
    const [cari, setCari] = useState(filter.cari);
    const [status, setStatus] = useState(filter.status);

    function terapkan(kejadian?: FormEvent) {
        kejadian?.preventDefault();
        router.get(rute.admin.pengguna, { cari: cari || undefined, status: status === 'semua' ? undefined : status }, { preserveState: true, replace: true });
    }

    function keHalaman(halaman: number) {
        router.get(rute.admin.pengguna, { cari: filter.cari || undefined, status: filter.status === 'semua' ? undefined : filter.status, halaman }, { preserveState: true });
    }

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman
                judul={teks.judul}
                sub={teks.sub}
                aksi={
                    <Button asChild>
                        <Link href={rute.admin.buatPengguna}>
                            <LuUserPlus aria-hidden className="h-4 w-4" />
                            {teks.buat}
                        </Link>
                    </Button>
                }
            />

            <form onSubmit={terapkan} className="mb-4 flex flex-col gap-3 sm:flex-row" role="search">
                <div className="relative flex-1">
                    <LuSearch aria-hidden className="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-redup" />
                    <Input
                        type="search"
                        aria-label={teks.cari}
                        placeholder={teks.cari}
                        className="pl-12"
                        value={cari}
                        onChange={(kejadian) => setCari(kejadian.target.value)}
                    />
                </div>
                <Select
                    aria-label={teks.status.semua}
                    className="sm:w-48"
                    value={status}
                    onChange={(kejadian) => {
                        setStatus(kejadian.target.value);
                        router.get(rute.admin.pengguna, { cari: cari || undefined, status: kejadian.target.value === 'semua' ? undefined : kejadian.target.value }, { preserveState: true, replace: true });
                    }}
                >
                    <option value="semua">{teks.status.semua}</option>
                    <option value="aktif">{teks.status.aktif}</option>
                    <option value="nonaktif">{teks.status.nonaktif}</option>
                </Select>
                <Button type="submit" variant="outline">
                    {teks.saring}
                </Button>
            </form>

            {pengguna.length === 0 ? (
                <Card className="flex flex-col items-center gap-3 p-10 text-center">
                    <IlustrasiKosong className="h-36 w-44" />
                    <h2 className="text-lg font-medium">{teks.kosongJudul}</h2>
                    <p className="text-redup">{teks.kosong}</p>
                </Card>
            ) : (
                <>
                    <Card className="hidden md:block">
                        <Table>
                            <TableHeader>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead>{teks.kolom.nama}</TableHead>
                                    <TableHead>{teks.kolom.status}</TableHead>
                                    <TableHead>{teks.kolom.email}</TableHead>
                                    <TableHead>{teks.kolom.masuk}</TableHead>
                                    <TableHead className="text-right">{teks.kolom.aksi}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pengguna.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            <p className="font-medium">{item.nama}</p>
                                            <p className="text-redup">{item.email}</p>
                                        </TableCell>
                                        <TableCell>
                                            <LencanaAkun pengguna={item} />
                                        </TableCell>
                                        <TableCell>{item.peran === 'member' ? <LencanaEmail status={item.statusEmail} /> : null}</TableCell>
                                        <TableCell className="text-redup">{item.lastLoginAt ? formatWaktu(item.lastLoginAt) : teks.belumPernahMasuk}</TableCell>
                                        <TableCell className="text-right">
                                            <Button asChild size="sm" variant="outline">
                                                <Link href={rute.admin.detailPengguna(item.id)}>{teks.lihat}</Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>

                    <ul className="flex flex-col gap-3 md:hidden">
                        {pengguna.map((item) => (
                            <li key={item.id}>
                                <Link href={rute.admin.detailPengguna(item.id)} className="block rounded-kartu border border-garis bg-kartu p-4 shadow-sm">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="truncate font-medium">{item.nama}</p>
                                            <p className="truncate text-sm text-redup">{item.email}</p>
                                        </div>
                                        <LuChevronRight aria-hidden className="mt-1 h-5 w-5 shrink-0 text-redup" />
                                    </div>
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        <LencanaAkun pengguna={item} />
                                        {item.peran === 'member' && <LencanaEmail status={item.statusEmail} />}
                                    </div>
                                    <p className="mt-2 text-xs text-redup">
                                        {teks.kolom.masuk}: {item.lastLoginAt ? formatWaktu(item.lastLoginAt) : teks.belumPernahMasuk}
                                    </p>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </>
            )}

            <div className="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                <p className="text-sm text-redup">
                    {teks.total(paginasi.total)} · {teks.halaman(paginasi.halaman, paginasi.terakhir)}
                </p>
                <div className="flex gap-2">
                    <Button variant="outline" size="sm" disabled={paginasi.halaman <= 1} onClick={() => keHalaman(paginasi.halaman - 1)}>
                        <LuChevronLeft aria-hidden className="h-4 w-4" />
                        {teks.sebelumnya}
                    </Button>
                    <Button variant="outline" size="sm" disabled={paginasi.halaman >= paginasi.terakhir} onClick={() => keHalaman(paginasi.halaman + 1)}>
                        {teks.berikutnya}
                        <LuChevronRight aria-hidden className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </LayoutAdmin>
    );
}
