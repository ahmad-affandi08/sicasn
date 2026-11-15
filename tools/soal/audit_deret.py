import json
import re
from fractions import Fraction
from pathlib import Path

BERKAS = Path(__file__).resolve().parents[2] / "database" / "seeders" / "data" / "soal" / "tiu.json"


def angka(teks):
    return int(teks.replace(".", ""))


def prediksi(a):
    hasil = {}
    d = a[1] - a[0]
    if all(a[i + 1] - a[i] == d for i in range(len(a) - 1)):
        hasil["aritmetika"] = a[-1] + d
    if a[0] != 0 and all(x != 0 for x in a):
        r = Fraction(a[1], a[0])
        if all(Fraction(a[i + 1], a[i]) == r for i in range(len(a) - 1)):
            hasil["geometri"] = int(a[-1] * r) if (a[-1] * r).denominator == 1 else None
    s = [a[i + 1] - a[i] for i in range(len(a) - 1)]
    if len(s) >= 3:
        d2 = s[1] - s[0]
        if all(s[i + 1] - s[i] == d2 for i in range(len(s) - 1)):
            hasil["selisih-bertingkat"] = a[-1] + s[-1] + d2
    if len(a) >= 4 and all(a[i] == a[i - 1] + a[i - 2] for i in range(2, len(a))):
        hasil["fibonacci"] = a[-1] + a[-2]
    if a[1] != a[0] and len(a) >= 4:
        k = Fraction(a[2] - a[1], a[1] - a[0])
        if k.denominator == 1:
            c = a[1] - k * a[0]
            if all(a[i + 1] == k * a[i] + c for i in range(len(a) - 1)):
                hasil["kali-tambah"] = int(k * a[-1] + c)
    if len(a) >= 6:
        ganjil, genap = a[0::2], a[1::2]
        dg = ganjil[1] - ganjil[0]
        de = genap[1] - genap[0]
        if all(ganjil[i + 1] - ganjil[i] == dg for i in range(len(ganjil) - 1)) and all(genap[i + 1] - genap[i] == de for i in range(len(genap) - 1)):
            if len(a) % 2 == 0:
                hasil["dua-pola"] = ganjil[-1] + dg
            else:
                hasil["dua-pola"] = genap[-1] + de
    return hasil


soal = json.loads(BERKAS.read_text(encoding="utf-8"))
salah = 0
ambigu = 0
total = 0
for butir in soal:
    if butir["subkategori"] != "Deret angka":
        continue
    cocok = re.search(r"deret: (.+), \.\.\.", butir["pertanyaan"])
    deret = [int(x) for x in cocok.group(1).split(", ")]
    benar = angka(next(o["teks"] for o in butir["opsi"] if o["skor"] == 5))
    p = prediksi(deret)
    total += 1
    nilai = {v for v in p.values() if v is not None}
    if benar not in nilai:
        salah += 1
        print("SALAH", deret, benar, p)
    elif len(nilai) > 1:
        ambigu += 1
        print("AMBIGU", deret, benar, p)
print(f"deret diaudit: {total}, salah: {salah}, ambigu: {ambigu}")
