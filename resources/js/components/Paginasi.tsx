import { LuChevronLeft, LuChevronRight } from 'react-icons/lu';

import { Button } from '@/components/ui/button';
import { teksPeluang } from '@/lang/peluang';

interface PropsPaginasi {
    halaman: number;
    terakhir: number;
    onPindah: (halaman: number) => void;
}

export function Paginasi({ halaman, terakhir, onPindah }: PropsPaginasi) {
    const teks = teksPeluang.halaman;

    if (terakhir <= 1) {
        return null;
    }

    return (
        <nav aria-label={teks.info(halaman, terakhir)} className="flex items-center justify-between gap-3">
            <Button variant="outline" disabled={halaman <= 1} onClick={() => onPindah(halaman - 1)}>
                <LuChevronLeft aria-hidden className="h-4 w-4" />
                {teks.sebelumnya}
            </Button>
            <p className="text-sm text-redup">{teks.info(halaman, terakhir)}</p>
            <Button variant="outline" disabled={halaman >= terakhir} onClick={() => onPindah(halaman + 1)}>
                {teks.berikutnya}
                <LuChevronRight aria-hidden className="h-4 w-4" />
            </Button>
        </nav>
    );
}
