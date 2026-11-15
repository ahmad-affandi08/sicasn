import json
import random
from pathlib import Path

import data_tkp
import data_tkp_tambahan
import data_twk
import twk_fakta

LABEL = ["A", "B", "C", "D", "E"]
LISENSI = "Soal asli SICASN"
TUJUAN = Path(__file__).resolve().parents[2] / "database" / "seeders" / "data" / "soal"


def bangun_twk():
    rng = random.Random(1945)
    hasil = []
    ditulis = {pertanyaan for _, pertanyaan, *_ in data_twk.DATA}
    tambahan = [butir for butir in twk_fakta.bangun_semua() if butir[1] not in ditulis]
    for sub, pertanyaan, benar, salah, pembahasan, sumber in [*data_twk.DATA, *tambahan]:
        semua = [benar] + list(salah)
        rng.shuffle(semua)
        posisi = semua.index(benar)
        hasil.append({
            "kategori": "twk",
            "subkategori": sub,
            "pertanyaan": pertanyaan,
            "pembahasan": f"{pembahasan} Jawaban yang benar: {LABEL[posisi]}.",
            "sumber_nama": sumber,
            "sumber_url": None,
            "lisensi": LISENSI,
            "opsi": [{"label": LABEL[i], "teks": teks, "skor": 5 if i == posisi else 0} for i, teks in enumerate(semua)],
        })
    rng.shuffle(hasil)
    return hasil


def bangun_tkp():
    rng = random.Random(2024)
    hasil = []
    for sub, pertanyaan, opsi_urut, pembahasan in [*data_tkp.DATA, *data_tkp_tambahan.DATA]:
        pasangan = list(zip(opsi_urut, [5, 4, 3, 2, 1]))
        rng.shuffle(pasangan)
        terbaik = next(i for i, (_, skor) in enumerate(pasangan) if skor == 5)
        hasil.append({
            "kategori": "tkp",
            "subkategori": sub,
            "pertanyaan": pertanyaan,
            "pembahasan": f"{pembahasan} Jawaban terbaik (skor 5): {LABEL[terbaik]}.",
            "sumber_nama": "Soal asli SICASN; penilaian mengacu pada nilai dasar ASN (BerAKHLAK) dan prinsip pelayanan publik",
            "sumber_url": None,
            "lisensi": LISENSI,
            "opsi": [{"label": LABEL[i], "teks": teks, "skor": skor} for i, (teks, skor) in enumerate(pasangan)],
        })
    rng.shuffle(hasil)
    return hasil


if __name__ == "__main__":
    for nama, isi in (("twk", bangun_twk()), ("tkp", bangun_tkp())):
        (TUJUAN / f"{nama}.json").write_text(json.dumps(isi, ensure_ascii=False, indent=1) + "\n", encoding="utf-8")
        print(f"{len(isi)} soal {nama.upper()} ditulis")
