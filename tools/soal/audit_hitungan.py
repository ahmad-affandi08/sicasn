import json
import math
import re
from pathlib import Path

BERKAS = Path(__file__).resolve().parents[2] / "database" / "seeders" / "data" / "soal" / "tiu.json"
soal = json.loads(BERKAS.read_text(encoding="utf-8"))


def n(teks):
    return int(re.sub(r"[^\d-]", "", teks.replace("Rp", "")))


def kunci(butir):
    return next(o["teks"] for o in butir["opsi"] if o["skor"] == 5)


HARI = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"]
cek = {"ok": 0, "salah": 0, "tidak_dikenali": 0}


def hasil(butir, benar):
    cek["ok" if benar else "salah"] += 1
    if not benar:
        print("SALAH:", butir["pertanyaan"], kunci(butir))


for b in soal:
    q, k = b["pertanyaan"], kunci(b)
    if m := re.match(r"Hasil dari (\d+)% × (\d+) \+ 1/(\d+) × (\d+) adalah", q):
        p, a, d, c = map(int, m.groups())
        hasil(b, n(k) == a * p // 100 + c // d)
    elif m := re.match(r"Hasil dari (\d+) × (\d+) \+ (\d+) - (\d+) × 2 adalah", q):
        a, bb, d, c = map(int, m.groups())
        hasil(b, n(k) == a * bb + d - c * 2)
    elif m := re.match(r"Jika (\d+)x \+ (\d+) = (\d+), maka", q):
        a, bb, c = map(int, m.groups())
        hasil(b, n(k) * a + bb == c)
    elif m := re.match(r"Jika (\d+)\(x - (\d+)\) = (\d+), maka", q):
        a, bb, c = map(int, m.groups())
        hasil(b, a * (n(k) - bb) == c)
    elif m := re.match(r"Jika hari ini hari (\w+), maka (\d+) hari lagi adalah hari", q):
        h, j = m.group(1), int(m.group(2))
        hasil(b, HARI[(HARI.index(h) + j) % 7] == k)
    elif m := re.match(r"Seorang pembeli mendapat diskon (\d+)% untuk barang seharga Rp([\d.]+)\. Berapa yang harus dibayar", q):
        d, h = int(m.group(1)), n(m.group(2))
        hasil(b, n(k) == h - h * d // 100)
    elif m := re.match(r"Sebuah toko memberi diskon (\d+)% untuk barang seharga Rp([\d.]+)\. Berapa rupiah potongan", q):
        d, h = int(m.group(1)), n(m.group(2))
        hasil(b, n(k) == h * d // 100)
    elif m := re.match(r"Sebuah barang dibeli seharga Rp([\d.]+) dan dijual dengan (untung|rugi) (\d+)%", q):
        beli, jenis, p = n(m.group(1)), m.group(2), int(m.group(3))
        hasil(b, n(k) == (beli + beli * p // 100 if jenis == "untung" else beli - beli * p // 100))
    elif m := re.match(r"Uang sebesar Rp([\d.]+) dibagikan kepada dua orang dengan perbandingan (\d+) : (\d+)\. Bagian orang (pertama|kedua)", q):
        total, a, bb, o = n(m.group(1)), int(m.group(2)), int(m.group(3)), m.group(4)
        hasil(b, n(k) * (a + bb) == total * (a if o == "pertama" else bb))
    elif m := re.match(r"Nilai (\d+) ujian seorang siswa adalah ([\d, ]+)\. Agar rata-rata (\d+) ujian menjadi (\d+)", q):
        nilai = [int(x) for x in m.group(2).split(", ")]
        banyak, target = int(m.group(3)), int(m.group(4))
        hasil(b, (sum(nilai) + n(k)) == target * banyak)
    elif m := re.match(r"Umur ayah (\d+) kali umur anaknya\. (\d+) tahun lagi jumlah umur mereka (\d+) tahun", q):
        kali, t, jumlah = map(int, m.groups())
        anak = n(k)
        hasil(b, (kali * anak + t) + (anak + t) == jumlah)
    elif m := re.match(r"Andi dapat .* dalam (\d+) hari, sedangkan Budi dapat menyelesaikannya dalam (\d+) hari", q):
        x, y = int(m.group(1)), int(m.group(2))
        hasil(b, abs(1 / x + 1 / y - 1 / n(k)) < 1e-12)
    elif m := re.match(r"Lampu A menyala setiap (\d+) menit dan lampu B setiap (\d+) menit", q):
        x, y = int(m.group(1)), int(m.group(2))
        hasil(b, n(k) == x * y // math.gcd(x, y))
    elif m := re.match(r"Seorang pedagang memiliki (\d+) apel dan (\d+) jeruk", q):
        x, y = int(m.group(1)), int(m.group(2))
        hasil(b, n(k) == math.gcd(x, y))
    elif m := re.match(r"Dari (\d+) siswa, (\d+) siswa gemar matematika, (\d+) siswa gemar fisika, dan (\d+) siswa gemar keduanya", q):
        t, a, bb, i = map(int, m.groups())
        hasil(b, n(k) == t - (a + bb - i))
    elif m := re.match(r"Sebuah mobil melaju dengan kecepatan tetap (\d+) km/jam selama (\d+) jam", q):
        v, t = int(m.group(1)), int(m.group(2))
        hasil(b, n(k) == v * t)
    elif m := re.match(r"Jarak dua kota (\d+) km\. Waktu tempuh dengan kecepatan tetap (\d+) km/jam", q):
        jarak, v = int(m.group(1)), int(m.group(2))
        hasil(b, n(k) * v == jarak)
    elif m := re.match(r"Dua kendaraan berangkat bersamaan dari kota yang berjarak (\d+) km dan saling mendekat dengan kecepatan (\d+) km/jam dan (\d+) km/jam", q):
        jarak, v1, v2 = map(int, m.groups())
        hasil(b, n(k) * (v1 + v2) == jarak)
    elif m := re.match(r"Seorang pengendara pergi dari kota A ke kota B dengan kecepatan (\d+) km/jam dan kembali .* kecepatan (\d+) km/jam", q):
        v1, v2 = int(m.group(1)), int(m.group(2))
        hasil(b, abs(2 * v1 * v2 / (v1 + v2) - n(k)) < 1e-9)
    elif b["subkategori"] not in ("Deret angka", "Penalaran logis", "Penalaran analitis") and not b["subkategori"].startswith("Verbal"):
        cek["tidak_dikenali"] += 1
        print("TIDAK DIKENALI:", b["subkategori"], q[:90])

print(cek)
