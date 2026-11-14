import { Head, Link } from '@inertiajs/react';

import { HeaderHalaman } from '@/components/HeaderHalaman';
import { IlustrasiKosong } from '@/components/ilustrasi';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { teksAuth } from '@/lang/auth';
import { LayoutMember } from '@/layouts/LayoutMember';
import { rute } from '@/lib/rute';

interface PropsHalaman {
    profil: { nama: string; email: string; noWhatsapp: string | null };
}

export default function Akun({ profil }: PropsHalaman) {
    const teks = teksAuth.akun;

    return (
        <LayoutMember>
            <Head title={teks.judul} />
            <HeaderHalaman judul={teks.sapaan(profil.nama)} sub={teks.judul} aksi={<Badge variant="sukses">{teks.status}</Badge>} />

            <div className="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{teks.profil}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl className="flex flex-col gap-4 text-sm">
                            <div>
                                <dt className="text-redup">{teks.nama}</dt>
                                <dd className="font-medium">{profil.nama}</dd>
                            </div>
                            <div>
                                <dt className="text-redup">{teks.email}</dt>
                                <dd className="font-medium break-all">{profil.email}</dd>
                            </div>
                            <div>
                                <dt className="text-redup">{teks.whatsapp}</dt>
                                <dd className="font-medium">{profil.noWhatsapp ?? teks.belumDiisi}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{teks.keamanan}</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col items-start gap-4">
                        <p className="text-sm text-redup">{teksAuth.ganti.subBiasa}</p>
                        <Button asChild variant="outline">
                            <Link href={rute.gantiPassword}>{teks.gantiPassword}</Link>
                        </Button>
                    </CardContent>
                </Card>

                <Card className="md:col-span-2">
                    <CardContent className="flex flex-col items-center gap-4 pt-6 text-center sm:flex-row sm:text-left">
                        <IlustrasiKosong className="h-32 w-40 shrink-0" judul={teks.ilustrasi} />
                        <div className="flex flex-col gap-1">
                            <h2 className="text-lg font-medium">{teks.infoJudul}</h2>
                            <p className="text-redup">{teks.info}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </LayoutMember>
    );
}
