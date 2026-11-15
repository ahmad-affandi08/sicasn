import { Head } from '@inertiajs/react';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ButirRiwayat } from '@/features/statistik/ButirRiwayat';
import { UnggahCsv } from '@/features/statistik/UnggahCsv';
import { teksAdmin } from '@/lang/admin';
import { LayoutAdmin } from '@/layouts/LayoutAdmin';
import type { RiwayatStatistikItem } from '@/types';

interface PropsHalaman {
    riwayat: RiwayatStatistikItem[];
    totalBaris: number;
}

export default function Index({ riwayat, totalBaris }: PropsHalaman) {
    const teks = teksAdmin.statistik;

    return (
        <LayoutAdmin>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.judul} sub={teks.sub} />

            <div className="flex flex-col gap-6">
                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <UnggahCsv />
                    </div>
                    <Card>
                        <CardContent className="flex h-full flex-col justify-center gap-1 pt-6">
                            <p className="text-4xl font-medium">{totalBaris.toLocaleString('id-ID')}</p>
                            <p className="text-sm text-redup">{teks.totalBaris}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.riwayat.judul}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {riwayat.length === 0 ? (
                            <p className="text-sm text-redup">{teks.riwayat.kosong}</p>
                        ) : (
                            <ul className="flex flex-col gap-4">
                                {riwayat.map((impor) => (
                                    <ButirRiwayat key={impor.id} impor={impor} />
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </LayoutAdmin>
    );
}
