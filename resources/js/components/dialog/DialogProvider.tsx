import { useCallback, useMemo, useRef, useState, type ReactNode } from 'react';

import { KotakDialog } from './KotakDialog';
import type { JenisDialogPesan, OpsiKonfirmasi, OpsiKonfirmasiKetik, OpsiPesan, PerintahDialog, TampilanDialog } from './jenis';
import { KonteksDialog } from './konteks';

interface ItemAntrean {
    id: number;
    tampilan: TampilanDialog;
    selesai: (hasil: boolean) => void;
}

export function DialogProvider({ children }: { children: ReactNode }) {
    const [antrean, setAntrean] = useState<ItemAntrean[]>([]);
    const penghitung = useRef(0);

    const buka = useCallback((tampilan: TampilanDialog) => {
        return new Promise<boolean>((selesai) => {
            penghitung.current += 1;
            const id = penghitung.current;
            setAntrean((sebelumnya) => [...sebelumnya, { id, tampilan, selesai }]);
        });
    }, []);

    const perintah = useMemo<PerintahDialog>(() => {
        const pesan = (jenis: JenisDialogPesan) => async (opsi: OpsiPesan) => {
            await buka({ jenis, ...opsi });
        };

        return {
            sukses: pesan('sukses'),
            gagal: pesan('gagal'),
            galat: pesan('galat'),
            peringatan: pesan('peringatan'),
            info: pesan('info'),
            konfirmasi: (opsi: OpsiKonfirmasi) => buka({ jenis: 'konfirmasi', ...opsi }),
            konfirmasiKetik: (opsi: OpsiKonfirmasiKetik) => buka({ jenis: 'konfirmasi', berisiko: true, ...opsi }),
        };
    }, [buka]);

    const aktif = antrean[0];

    return (
        <KonteksDialog.Provider value={perintah}>
            {children}
            {aktif && (
                <KotakDialog
                    key={aktif.id}
                    tampilan={aktif.tampilan}
                    onTutup={(hasil) => {
                        aktif.selesai(hasil);
                        setAntrean((sebelumnya) => sebelumnya.slice(1));
                    }}
                />
            )}
        </KonteksDialog.Provider>
    );
}
