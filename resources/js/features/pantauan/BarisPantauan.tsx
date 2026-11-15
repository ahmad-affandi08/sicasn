import { Alert } from '@/components/ui/alert';
import { KartuFormasi } from '@/features/formasi/KartuFormasi';
import { teksPantauan } from '@/lang/pantauan';
import { formatHari } from '@/lib/format';
import type { PantauanItem } from '@/types';

import { EditorCatatan } from './EditorCatatan';

interface PropsBarisPantauan {
    pantauan: PantauanItem;
    ambang: { longgarMaks: number; ketatMin: number };
    maksCatatan: number;
    dipilih: boolean;
    pilihanPenuh: boolean;
    onPilih: (id: number) => void;
}

export function BarisPantauan({ pantauan, ambang, maksCatatan, dipilih, pilihanPenuh, onPilih }: PropsBarisPantauan) {
    const teks = teksPantauan;

    return (
        <div className="flex flex-col">
            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-garis bg-permukaan px-4 py-2 text-xs text-redup md:px-5">
                <label className="flex min-h-11 cursor-pointer items-center gap-2.5 text-sm font-medium text-teks md:min-h-9">
                    <input
                        type="checkbox"
                        className="h-5 w-5 accent-[var(--primer)]"
                        checked={dipilih}
                        disabled={!dipilih && pilihanPenuh}
                        aria-label={teks.pilih(pantauan.jabatan)}
                        onChange={() => onPilih(pantauan.id)}
                    />
                    {teks.pilihLabel}
                </label>
                <span>{teks.ditambahkan(formatHari(pantauan.ditambahkanAt))}</span>
            </div>
            {pantauan.status === 'ditarik' && (
                <div className="px-4 pt-4 md:px-5">
                    <Alert variant="bahaya">{teks.ditarik}</Alert>
                </div>
            )}
            <KartuFormasi formasi={pantauan} ambang={ambang} dipantau muatUlang={['item']} />
            <div className="border-t border-garis px-4 py-4 md:px-5">
                <EditorCatatan formasiId={pantauan.id} catatan={pantauan.catatan} maks={maksCatatan} />
            </div>
        </div>
    );
}
