import { useEffect, useId, useRef, useState, type KeyboardEvent } from 'react';
import { LuChevronsUpDown, LuX } from 'react-icons/lu';

import { teksFormasi } from '@/lang/formasi';
import { cn } from '@/lib/utils';
import type { OpsiPilihan } from '@/types';

interface PropsCombobox {
    id: string;
    label: string;
    placeholder: string;
    terpilih: OpsiPilihan[];
    banyak?: boolean;
    muatOpsi: (kataKunci: string) => Promise<OpsiPilihan[]>;
    onUbah: (terpilih: OpsiPilihan[]) => void;
    jeda?: number;
}

export function Combobox({ id, label, placeholder, terpilih, banyak = false, muatOpsi, onUbah, jeda = 250 }: PropsCombobox) {
    const idDaftar = useId();
    const [terbuka, setTerbuka] = useState(false);
    const [kataKunci, setKataKunci] = useState('');
    const [opsi, setOpsi] = useState<OpsiPilihan[]>([]);
    const [memuat, setMemuat] = useState(false);
    const [galat, setGalat] = useState(false);
    const [aktif, setAktif] = useState(0);
    const wadah = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!terbuka) {
            return;
        }

        let batal = false;
        const pewaktu = window.setTimeout(() => {
            setMemuat(true);
            setGalat(false);
            muatOpsi(kataKunci)
                .then((hasil) => {
                    if (!batal) {
                        setOpsi(hasil);
                        setAktif(0);
                    }
                })
                .catch(() => {
                    if (!batal) {
                        setGalat(true);
                    }
                })
                .finally(() => {
                    if (!batal) {
                        setMemuat(false);
                    }
                });
        }, jeda);

        return () => {
            batal = true;
            window.clearTimeout(pewaktu);
        };
    }, [terbuka, kataKunci, muatOpsi, jeda]);

    useEffect(() => {
        function tutupBilaLuar(kejadian: MouseEvent) {
            if (wadah.current && !wadah.current.contains(kejadian.target as Node)) {
                setTerbuka(false);
            }
        }

        document.addEventListener('mousedown', tutupBilaLuar);

        return () => document.removeEventListener('mousedown', tutupBilaLuar);
    }, []);

    const terakhirDipilih = terpilih.at(-1);
    const tersedia = opsi.filter((butir) => !terpilih.some((sudah) => sudah.nilai === butir.nilai));

    const pilihanAktif = tersedia[aktif];

    function pilih(butir: OpsiPilihan) {
        onUbah(banyak ? [...terpilih, butir] : [butir]);
        setKataKunci('');
        setTerbuka(banyak);
    }

    function hapus(nilai: string) {
        onUbah(terpilih.filter((butir) => butir.nilai !== nilai));
    }

    function tekan(kejadian: KeyboardEvent<HTMLInputElement>) {
        if (kejadian.key === 'ArrowDown') {
            kejadian.preventDefault();
            setTerbuka(true);
            setAktif((sekarang) => Math.min(sekarang + 1, Math.max(tersedia.length - 1, 0)));
        } else if (kejadian.key === 'ArrowUp') {
            kejadian.preventDefault();
            setAktif((sekarang) => Math.max(sekarang - 1, 0));
        } else if (kejadian.key === 'Enter' && terbuka && pilihanAktif) {
            kejadian.preventDefault();
            pilih(pilihanAktif);
        } else if (kejadian.key === 'Escape') {
            setTerbuka(false);
        } else if (kejadian.key === 'Backspace' && kataKunci === '' && terakhirDipilih) {
            hapus(terakhirDipilih.nilai);
        }
    }

    const disembunyikan = !banyak && terpilih.length > 0;

    return (
        <div ref={wadah} className="relative flex flex-col gap-2">
            <label htmlFor={id} className="text-sm font-medium text-teks">
                {label}
            </label>
            <div className={cn('flex min-h-11 flex-wrap items-center gap-1.5 rounded-lg border border-garis-input bg-kartu px-2.5 py-1 md:min-h-10', terbuka && 'ring-2 ring-primer/40')}>
                {terpilih.map((butir) => (
                    <span key={butir.nilai} className="inline-flex max-w-full items-center gap-1 rounded-md bg-primer-lembut py-0.5 pr-0.5 pl-2 text-sm text-primer">
                        <span className="truncate">{butir.label}</span>
                        <button type="button" aria-label={teksFormasi.combobox.hapus(butir.label)} onClick={() => hapus(butir.nilai)} className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded hover:bg-primer/10">
                            <LuX aria-hidden className="h-4 w-4" />
                        </button>
                    </span>
                ))}
                {!disembunyikan && (
                    <input
                        id={id}
                        role="combobox"
                        aria-expanded={terbuka}
                        aria-controls={idDaftar}
                        aria-autocomplete="list"
                        aria-activedescendant={terbuka && pilihanAktif ? `${idDaftar}-${aktif}` : undefined}
                        autoComplete="off"
                        placeholder={terpilih.length === 0 ? placeholder : ''}
                        value={kataKunci}
                        onChange={(kejadian) => {
                            setKataKunci(kejadian.target.value);
                            setTerbuka(true);
                        }}
                        onFocus={() => setTerbuka(true)}
                        onKeyDown={tekan}
                        className="min-h-8 min-w-24 flex-1 bg-transparent px-1 text-base text-teks outline-none placeholder:text-redup md:text-sm"
                    />
                )}
                {disembunyikan && <span id={id} className="sr-only">{terakhirDipilih?.label}</span>}
                {!disembunyikan && <LuChevronsUpDown aria-hidden className="ml-auto h-4 w-4 shrink-0 text-redup" />}
            </div>
            {terbuka && (
                <ul id={idDaftar} role="listbox" aria-label={label} className="absolute top-full right-0 left-0 z-30 mt-1 max-h-60 overflow-y-auto rounded-lg border border-garis bg-kartu p-1 shadow-lg">
                    {memuat && <li className="px-3 py-2 text-sm text-redup">{teksFormasi.combobox.memuat}</li>}
                    {!memuat && galat && <li className="px-3 py-2 text-sm text-bahaya">{teksFormasi.combobox.galat}</li>}
                    {!memuat && !galat && tersedia.length === 0 && <li className="px-3 py-2 text-sm text-redup">{teksFormasi.combobox.kosong}</li>}
                    {!memuat &&
                        !galat &&
                        tersedia.map((butir, indeks) => (
                            <li
                                key={butir.nilai}
                                id={`${idDaftar}-${indeks}`}
                                role="option"
                                aria-selected={false}
                                onMouseDown={(kejadian) => kejadian.preventDefault()}
                                onClick={() => pilih(butir)}
                                className={cn('min-h-11 cursor-pointer rounded-lg px-3 py-2.5 text-sm', indeks === aktif ? 'bg-primer-lembut text-primer' : 'hover:bg-primer-lembut/60')}
                            >
                                {butir.label}
                            </li>
                        ))}
                </ul>
            )}
        </div>
    );
}
