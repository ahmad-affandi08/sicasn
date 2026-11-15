import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

import { useDialog } from '@/components/dialog/konteks';
import { teksLatihan } from '@/lang/latihan';
import { rute } from '@/lib/rute';

import { kirimJawaban, type HasilKirim } from './api';
import type { JawabanSoal, PercobaanBerjalan, PetaJawaban } from './tipe';

const JEDA_ULANG_MS = 1000;

function jawabanAwal(percobaan: PercobaanBerjalan): PetaJawaban {
    return Object.fromEntries(percobaan.soal.map((soal) => [soal.soalId, { opsiId: soal.opsiDipilihId, ragu: soal.ragu }]));
}

function jeda(milidetik: number): Promise<void> {
    return new Promise((selesai) => window.setTimeout(selesai, milidetik));
}

export function useSesiLatihan(percobaan: PercobaanBerjalan) {
    const dialog = useDialog();
    const [jawaban, setJawaban] = useState<PetaJawaban>(() => jawabanAwal(percobaan));
    const [indeks, setIndeks] = useState(0);
    const [sisaDetik, setSisaDetik] = useState(percobaan.sisaDetik);
    const [menyimpan, setMenyimpan] = useState(0);
    const batas = useRef(Date.now() + percobaan.sisaDetik * 1000);
    const rantai = useRef<Promise<void>>(Promise.resolve());
    const sudahMenyelesaikan = useRef(false);
    const sudahMelaporGagal = useRef(false);

    const menyelesaikan = useCallback(
        async (otomatis: boolean) => {
            if (sudahMenyelesaikan.current) {
                return;
            }

            sudahMenyelesaikan.current = true;
            await rantai.current;

            if (otomatis) {
                await dialog.info({ judul: teksLatihan.kerjakan.waktuHabis.judul, pesan: teksLatihan.kerjakan.waktuHabis.pesan, teksTombol: teksLatihan.kerjakan.waktuHabis.tombol });
            }

            router.post(rute.selesaiLatihan(percobaan.id));
        },
        [dialog, percobaan.id],
    );

    useEffect(() => {
        const penanda = window.setInterval(() => {
            const sisa = Math.max(0, Math.ceil((batas.current - Date.now()) / 1000));
            setSisaDetik(sisa);

            if (sisa === 0) {
                void menyelesaikan(true);
            }
        }, 1000);

        return () => window.clearInterval(penanda);
    }, [menyelesaikan]);

    const antrekan = useCallback(
        (soalId: number, baru: JawabanSoal) => {
            setMenyimpan((jumlah) => jumlah + 1);

            rantai.current = rantai.current.then(async () => {
                let hasil: HasilKirim = await kirimJawaban(percobaan.id, { soalId, ...baru });

                if (hasil === 'gagal') {
                    await jeda(JEDA_ULANG_MS);
                    hasil = await kirimJawaban(percobaan.id, { soalId, ...baru });
                }

                if (hasil === 'waktuHabis') {
                    void menyelesaikan(true);
                }

                if (hasil === 'gagal' && !sudahMelaporGagal.current) {
                    sudahMelaporGagal.current = true;
                    await dialog.galat({ judul: teksLatihan.kerjakan.gagalSimpan.judul, pesan: teksLatihan.kerjakan.gagalSimpan.pesan, teksTombol: teksLatihan.kerjakan.gagalSimpan.tombol });
                    sudahMelaporGagal.current = false;
                }

                setMenyimpan((jumlah) => jumlah - 1);
            });
        },
        [dialog, menyelesaikan, percobaan.id],
    );

    const ubah = useCallback(
        (soalId: number, perubahan: Partial<JawabanSoal>) => {
            const baru = { opsiId: null, ragu: false, ...jawaban[soalId], ...perubahan };
            setJawaban((sebelumnya) => ({ ...sebelumnya, [soalId]: baru }));
            antrekan(soalId, baru);
        },
        [antrekan, jawaban],
    );

    const ringkasan = useMemo(() => {
        const dijawab = percobaan.soal.filter((soal) => jawaban[soal.soalId]?.opsiId != null).length;
        const ragu = percobaan.soal.filter((soal) => jawaban[soal.soalId]?.ragu === true).length;

        return { dijawab, ragu, kosong: percobaan.soal.length - dijawab };
    }, [jawaban, percobaan.soal]);

    const konfirmasiSelesai = useCallback(async () => {
        const setuju = await dialog.konfirmasi({
            judul: teksLatihan.kerjakan.konfirmasi.judul,
            pesan: teksLatihan.kerjakan.konfirmasi.pesan(ringkasan.kosong, ringkasan.ragu),
            teksTombol: teksLatihan.kerjakan.konfirmasi.tombol,
            teksBatal: teksLatihan.kerjakan.konfirmasi.batal,
        });

        if (setuju) {
            await menyelesaikan(false);
        }
    }, [dialog, menyelesaikan, ringkasan.kosong, ringkasan.ragu]);

    return { jawaban, indeks, setIndeks, sisaDetik, menyimpan, ringkasan, ubah, konfirmasiSelesai };
}
