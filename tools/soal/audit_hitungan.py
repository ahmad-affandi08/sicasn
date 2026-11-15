import json
import math
import re
from fractions import Fraction
from pathlib import Path

BERKAS = Path(__file__).resolve().parents[2] / "database" / "seeders" / "data" / "soal" / "tiu.json"
soal = json.loads(BERKAS.read_text(encoding="utf-8"))


def n(teks):
    return int(re.sub(r"[^\d-]", "", teks.replace("Rp", "")))


def kunci(butir):
    return next(o["teks"] for o in butir["opsi"] if o["skor"] == 5)


def prima(x):
    return x > 1 and all(x % k for k in range(2, int(x**0.5) + 1))


def audit_baru(b, q, k):
    if m := re.match(r"Sisa pembagian (\d+) oleh (\d+) adalah", q):
        return n(k) == int(m.group(1)) % int(m.group(2))
    if m := re.match(r"Banyak bilangan prima dari (\d+) sampai (\d+)", q):
        return n(k) == sum(1 for x in range(int(m.group(1)), int(m.group(2)) + 1) if prima(x))
    if m := re.match(r"Hasil dari √(\d+) \+ (\d+)² - (\d+)³ adalah", q):
        a, bb, c = map(int, m.groups())
        return n(k) == round(a**0.5) + bb * bb - c * c * c
    if m := re.match(r"Hasil dari √(\d+) × (\d+) - (\d+)³ adalah", q):
        a, bb, c = map(int, m.groups())
        return n(k) == round(a**0.5) * bb - c * c * c
    if m := re.match(r"Manakah pecahan (terbesar|terkecil) di antara ([\d/, ]+)\?", q):
        pecahan = [Fraction(int(x.split("/")[0]), int(x.split("/")[1])) for x in m.group(2).split(", ")]
        tujuan = max(pecahan) if m.group(1) == "terbesar" else min(pecahan)
        return Fraction(int(k.split("/")[0]), int(k.split("/")[1])) == tujuan
    if m := re.match(r"(\d+) pekerja dapat menyelesaikan suatu proyek dalam (\d+) hari\. Jika proyek yang sama dikerjakan (\d+) pekerja", q):
        a, d, c = map(int, m.groups())
        return n(k) * c == a * d
    if m := re.match(r"Ani menabung Rp([\d.]+) dengan bunga tunggal (\d+)% per tahun selama (\d+) tahun\. (Jumlah|Besar)", q):
        pokok, persen, tahun = n(m.group(1)), int(m.group(2)), int(m.group(3))
        bunga = pokok * persen * tahun // 100
        return n(k) == (pokok + bunga if m.group(4) == "Jumlah" else bunga)
    if m := re.match(r"Sebuah persegi panjang berukuran panjang (\d+) cm dan lebar (\d+) cm\. (Luasnya|Kelilingnya)", q):
        p, l = int(m.group(1)), int(m.group(2))
        return n(k) == (p * l if m.group(3) == "Luasnya" else 2 * p + 2 * l)
    if m := re.match(r"Sebuah segitiga memiliki alas (\d+) cm dan tinggi (\d+) cm", q):
        return n(k) * 2 == int(m.group(1)) * int(m.group(2))
    if m := re.match(r"Sebuah lingkaran berjari-jari (\d+) cm\. Dengan π = 22/7, (luas|kelilingnya)", q):
        r = int(m.group(1))
        return n(k) == (Fraction(22, 7) * r * r if m.group(2) == "luas" else Fraction(44, 7) * r)
    if m := re.match(r"Sebuah balok berukuran panjang (\d+) cm, lebar (\d+) cm, dan tinggi (\d+) cm", q):
        return n(k) == math.prod(map(int, m.groups()))
    if m := re.match(r"Sebuah kubus memiliki panjang rusuk (\d+) cm", q):
        return n(k) == int(m.group(1)) ** 3
    if m := re.match(r"Sebuah trapesium memiliki sisi sejajar (\d+) cm dan (\d+) cm serta tinggi (\d+) cm", q):
        a, bb, t = map(int, m.groups())
        return n(k) * 2 == (a + bb) * t
    if q.startswith("Data penjualan sebuah toko:"):
        nilai = {mm.group(1): int(mm.group(2)) for mm in re.finditer(r"(\w+): (\d+) unit", q)}
        if "Selisih" in q:
            return n(k) == max(nilai.values()) - min(nilai.values())
        if q.rstrip().endswith("tertinggi adalah ..."):
            return k == max(nilai, key=nilai.get)
        if q.rstrip().endswith("terendah adalah ..."):
            return k == min(nilai, key=nilai.get)
        return n(k) * len(nilai) == sum(nilai.values())
    if m := re.match(r"Besar sudut terkecil .* pukul (\d\d)\.(\d\d) adalah", q):
        h, mnt = int(m.group(1)) % 12, int(m.group(2))
        selisih = abs(0.5 * (60 * h + mnt) - 6 * mnt)
        return abs(min(selisih, 360 - selisih) - n(k)) < 1e-9
    if m := re.match(r"Tentukan huruf berikutnya dari deret: ([A-Z, ]+), \.\.\.", q):
        urut = [ord(x) - 64 for x in m.group(1).split(", ")]
        langkah = urut[1] - urut[0]
        return all(urut[i + 1] - urut[i] == langkah for i in range(len(urut) - 1)) and ord(k) - 64 == urut[-1] + langkah
    if m := re.match(r"Dari (\d+) orang akan dipilih (\d+) orang untuk menjadi anggota tim", q):
        a, c = int(m.group(1)), int(m.group(2))
        return n(k) == math.factorial(a) // (math.factorial(c) * math.factorial(a - c))
    if m := re.match(r"Dari (\d+) orang akan dipilih (\d+) orang untuk menduduki jabatan", q):
        a, c = int(m.group(1)), int(m.group(2))
        return n(k) == math.factorial(a) // math.factorial(a - c)
    if m := re.match(r"Sebuah kantong berisi (\d+) kelereng merah, (\d+) kelereng biru, dan (\d+) kelereng hijau\. Satu kelereng diambil secara acak\. Peluang terambil kelereng (\w+) adalah", q):
        merah, biru, hijau = map(int, m.groups()[:3])
        pilih = {"merah": merah, "biru": biru, "hijau": hijau}[m.group(4)]
        hasil_pecahan = Fraction(int(k.split("/")[0]), int(k.split("/")[1])) if "/" in k else Fraction(int(k))
        return hasil_pecahan == Fraction(pilih, merah + biru + hijau)
    if m := re.match(r"Rata-rata nilai ujian (\d+) siswa kelas A adalah (\d+) dan rata-rata nilai (\d+) siswa kelas B adalah (\d+)", q):
        n1, r1, n2, r2 = map(int, m.groups())
        return n(k) * (n1 + n2) == n1 * r1 + n2 * r2
    if m := re.match(r"Harga sebuah barang Rp([\d.]+) dinaikkan (\d+)% lalu diturunkan (\d+)% dari harga setelah kenaikan", q):
        harga, naik, turun = n(m.group(1)), int(m.group(2)), int(m.group(3))
        return Fraction(n(k)) == Fraction(harga) * (1 + Fraction(naik, 100)) * (1 - Fraction(turun, 100))
    if m := re.match(r"Kecepatan (\d+) km/jam sama dengan", q):
        return Fraction(n(k)) == Fraction(int(m.group(1)) * 1000, 3600)
    if m := re.match(r"Kecepatan (\d+) m/detik sama dengan", q):
        return Fraction(n(k)) == Fraction(int(m.group(1)) * 3600, 1000)
    if m := re.match(r"(\d+) liter ditambah (\d+) mililiter", q):
        return n(k) == int(m.group(1)) * 1000 + int(m.group(2))
    if m := re.match(r"(\d+) jam (\d+) menit sama dengan ... menit", q):
        return n(k) == int(m.group(1)) * 60 + int(m.group(2))
    if m := re.match(r"Pada peta berskala 1 : ([\d.]+), jarak dua kota adalah (\d+) cm", q):
        return Fraction(n(k)) == Fraction(n(m.group(1)) * int(m.group(2)), 100000)
    if m := re.match(r"Sebuah kereta berangkat pukul (\d\d)\.(\d\d) dan menempuh perjalanan selama (\d+) jam (\d+) menit\. Kereta tiba pada pukul", q):
        mulai = int(m.group(1)) * 60 + int(m.group(2)) + int(m.group(3)) * 60 + int(m.group(4))
        return k == f"{mulai // 60:02d}.{mulai % 60:02d}"
    return None


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
        hasil_baru = audit_baru(b, q, k)
        if hasil_baru is None:
            cek["tidak_dikenali"] += 1
            print("TIDAK DIKENALI:", b["subkategori"], q[:90])
        else:
            hasil(b, bool(hasil_baru))

print(cek)
