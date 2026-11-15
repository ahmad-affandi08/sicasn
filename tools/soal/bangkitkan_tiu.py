import json
import random
import sys
from pathlib import Path

import tiu_logika as logika
import tiu_numerik as numerik
import tiu_verbal as verbal

SUMBER = "Soal asli SICASN (dibangkitkan program; kunci dihitung otomatis)"
LISENSI = "Soal asli SICASN"
LABEL = ["A", "B", "C", "D", "E"]

RENCANA_ACAK = [
    (numerik.deret_aritmetika, 14),
    (numerik.deret_geometri, 12),
    (numerik.deret_selisih_bertingkat, 14),
    (numerik.deret_kuadrat, 10),
    (numerik.deret_ganda, 12),
    (numerik.deret_fibonacci, 10),
    (numerik.deret_kali_tambah, 10),
    (numerik.hitung_persen_pecahan, 14),
    (numerik.operasi_urutan, 12),
    (numerik.diskon_untung, 14),
    (numerik.untung_rugi, 12),
    (numerik.kecepatan_jarak, 14),
    (numerik.dua_kendaraan_bertemu, 10),
    (numerik.rata_rata_kecepatan, 3),
    (numerik.pekerjaan, 12),
    (numerik.umur, 12),
    (numerik.rata_rata, 12),
    (numerik.perbandingan, 12),
    (numerik.kpk_fpb, 12),
    (numerik.himpunan, 10),
    (numerik.aljabar, 14),
    (numerik.kalender, 10),
    (logika.modus_ponens, 4),
    (logika.modus_tollens, 4),
    (logika.silogisme_hipotetik, 4),
    (logika.menyangkal_anteseden, 4),
    (logika.menegaskan_konsekuen, 4),
    (logika.silogisme_semua, 4),
    (logika.silogisme_tidak_ada, 4),
    (logika.silogisme_sebagian, 4),
    (logika.entitas_anggota, 4),
    (logika.disjungsi, 4),
    (logika.urutan_posisi, 14),
]

RENCANA_VERBAL = [verbal.sinonim, verbal.antonim, verbal.analogi]
JUMLAH_VERBAL = {"sinonim": len(verbal.SINONIM), "antonim": len(verbal.ANTONIM), "analogi": len(verbal.ANALOGI)}


def susun(rng, butir):
    salah = []
    for teks in butir["salah"]:
        if teks != butir["benar"] and teks not in salah:
            salah.append(teks)
    if len(salah) < 4:
        return None
    semua = [butir["benar"]] + salah[:4]
    rng.shuffle(semua)
    posisi = semua.index(butir["benar"])
    pembahasan = f"{butir['pembahasan']} Jawaban yang benar: {LABEL[posisi]}."
    return {
        "kategori": "tiu",
        "subkategori": butir["subkategori"],
        "pertanyaan": butir["pertanyaan"],
        "pembahasan": pembahasan,
        "sumber_nama": SUMBER,
        "sumber_url": None,
        "lisensi": LISENSI,
        "opsi": [{"label": LABEL[i], "teks": teks, "skor": 5 if i == posisi else 0} for i, teks in enumerate(semua)],
    }


def bangun():
    rng = random.Random(20261115)
    hasil = []
    terpakai = set()

    for fungsi, jumlah in RENCANA_ACAK:
        dibuat = 0
        percobaan = 0
        while dibuat < jumlah and percobaan < jumlah * 60:
            percobaan += 1
            try:
                butir = fungsi(rng)
            except ValueError:
                continue
            kunci = " ".join(butir["pertanyaan"].split()).lower()
            if kunci in terpakai:
                continue
            baris = susun(rng, butir)
            if baris is None:
                continue
            terpakai.add(kunci)
            hasil.append(baris)
            dibuat += 1
        if dibuat < jumlah:
            print(f"kurang: {fungsi.__name__} {dibuat}/{jumlah}", file=sys.stderr)

    for fungsi in RENCANA_VERBAL:
        for indeks in range(JUMLAH_VERBAL[fungsi.__name__]):
            baris = susun(rng, fungsi(rng, indeks))
            if baris is not None and " ".join(baris["pertanyaan"].split()).lower() not in terpakai:
                terpakai.add(" ".join(baris["pertanyaan"].split()).lower())
                hasil.append(baris)

    rng.shuffle(hasil)
    return hasil


if __name__ == "__main__":
    tujuan = Path(__file__).resolve().parents[2] / "database" / "seeders" / "data" / "soal" / "tiu.json"
    soal = bangun()
    tujuan.write_text(json.dumps(soal, ensure_ascii=False, indent=1) + "\n", encoding="utf-8")
    print(f"{len(soal)} soal TIU ditulis ke {tujuan}")
