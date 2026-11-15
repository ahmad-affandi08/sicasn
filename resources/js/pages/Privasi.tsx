import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { teksSistem } from '@/lang/sistem';
import { LayoutPublik } from '@/layouts/LayoutPublik';
import { rute } from '@/lib/rute';

export default function Privasi() {
    const teks = teksSistem.privasi;

    return (
        <LayoutPublik>
            <Head title={teks.judul} />
            <article className="mx-auto flex max-w-2xl flex-col gap-8 px-4 py-12 sm:px-6">
                <header className="flex flex-col gap-2">
                    <h1 className="text-3xl font-medium tracking-tight">{teks.judul}</h1>
                    <p className="text-redup">{teks.sub}</p>
                    <p className="text-sm text-redup">{teks.diperbarui}</p>
                </header>
                {teks.bagian.map((bagian) => (
                    <section key={bagian.judul} className="flex flex-col gap-2 border-t border-garis pt-6">
                        <h2 className="text-lg font-medium">{bagian.judul}</h2>
                        {bagian.isi.map((paragraf) => (
                            <p key={paragraf} className="leading-relaxed text-redup">
                                {paragraf}
                            </p>
                        ))}
                    </section>
                ))}
                <Button asChild variant="outline" className="self-start">
                    <Link href={rute.beranda}>{teks.kembali}</Link>
                </Button>
            </article>
        </LayoutPublik>
    );
}
