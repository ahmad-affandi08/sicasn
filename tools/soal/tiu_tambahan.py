import itertools
import math
from fractions import Fraction

from dasar import angka, pengecoh_numerik, rp, soal

HURUF = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"


def pecahan_teks(nilai):
    nilai = Fraction(nilai)
    return str(nilai.numerator) if nilai.denominator == 1 else f"{nilai.numerator}/{nilai.denominator}"


def sisa_pembagian(rng):
    n = rng.randint(50, 999)
    d = rng.randint(3, 13)
    benar = n % d
    kandidat = [x for x in range(d) if x != benar] + [n // d, d, benar + d]
    rng.shuffle(kandidat)
    return soal("Bilangan", f"Sisa pembagian {n} oleh {d} adalah ...", benar, kandidat, f"{n} = {d} × {n // d} + {benar}. Maka sisanya {benar}.")


def banyak_prima(rng):
    a = rng.randint(1, 40)
    b = a + rng.randint(15, 45)
    prima = [x for x in range(max(a, 2), b + 1) if all(x % k for k in range(2, int(x**0.5) + 1))]
    benar = len(prima)
    return soal("Bilangan", f"Banyak bilangan prima dari {a} sampai {b} (termasuk batas bila prima) adalah ...", benar, pengecoh_numerik(rng, benar), f"Bilangan prima pada rentang itu: {', '.join(map(str, prima))}. Jumlahnya {benar}.")


def pangkat_akar(rng):
    a = rng.choice([4, 9, 16, 25, 36, 49, 64, 81, 100, 121, 144, 169, 196, 225])
    b = rng.randint(2, 9)
    c = rng.randint(2, 4)
    jenis = rng.choice(["tambah", "kurang"])
    akar = int(math.isqrt(a))
    benar = akar + b**2 - c**3 if jenis == "tambah" else akar * b - c**3
    teks = f"√{a} + {b}² - {c}³" if jenis == "tambah" else f"√{a} × {b} - {c}³"
    return soal("Hitungan", f"Hasil dari {teks} adalah ...", benar, pengecoh_numerik(rng, benar, [benar + c, benar - b]), f"√{a} = {akar}, {b}² = {b**2}, {c}³ = {c**3}. Hasilnya {benar}.")


def pecahan_terbesar(rng):
    pecahan = set()
    while len(pecahan) < 5:
        pembilang = rng.randint(1, 11)
        penyebut = rng.randint(pembilang + 1, 14)
        pecahan.add(Fraction(pembilang, penyebut))
    daftar = sorted(pecahan)
    rng.shuffle(daftar)
    jenis = rng.choice(["terbesar", "terkecil"])
    benar = max(daftar) if jenis == "terbesar" else min(daftar)
    urut = ", ".join(f"{p.numerator}/{p.denominator}" for p in sorted(daftar))
    daftar_teks = ", ".join(f"{p.numerator}/{p.denominator}" for p in daftar)
    return soal("Hitungan", f"Manakah pecahan {jenis} di antara {daftar_teks}?", benar, [p for p in daftar if p != benar], f"Urutan dari terkecil: {urut}. Jadi yang {jenis} adalah {pecahan_teks(benar)}.", tampil=lambda nilai: nilai if isinstance(nilai, str) else f"{nilai.numerator}/{nilai.denominator}")


def pekerja_berbalik(rng):
    n = rng.choice([4, 5, 6, 8, 10, 12])
    d = rng.choice([6, 8, 10, 12, 15, 20])
    m = rng.choice([x for x in [2, 3, 4, 5, 6, 8, 10, 12, 15, 20, 24, 30] if (n * d) % x == 0 and x != n])
    benar = n * d // m
    return soal("Perbandingan", f"{n} pekerja dapat menyelesaikan suatu proyek dalam {d} hari. Jika proyek yang sama dikerjakan {m} pekerja dengan kemampuan sama, proyek selesai dalam ... hari.", benar, pengecoh_numerik(rng, benar, [d + (m - n), d * m // n if (d * m) % n == 0 else d + 2]), f"Banyak pekerja dan lama pengerjaan berbanding terbalik. Total pekerjaan = {n} × {d} = {n * d} hari-orang. Dengan {m} pekerja: {n * d} ÷ {m} = {benar} hari.")


def bunga_tunggal(rng):
    pokok = rng.choice([1000000, 2000000, 2500000, 4000000, 5000000, 8000000, 10000000])
    persen = rng.choice([4, 5, 6, 8, 10, 12])
    tahun = rng.choice([1, 2, 3, 4, 5])
    bunga = pokok * persen * tahun // 100
    jenis = rng.choice(["total", "bunga"])
    benar = pokok + bunga if jenis == "total" else bunga
    tanya = f"Ani menabung {rp(pokok)} dengan bunga tunggal {persen}% per tahun selama {tahun} tahun. " + ("Jumlah tabungan Ani pada akhir masa tersebut adalah ..." if jenis == "total" else "Besar bunga yang diperoleh adalah ...")
    return soal("Persentase", tanya, benar, [pokok + bunga if jenis == "bunga" else bunga, pokok + pokok * persen // 100, bunga // tahun, benar + pokok * persen // 100], f"Bunga = {rp(pokok)} × {persen}% × {tahun} = {rp(bunga)}." + (f" Jumlah akhir = {rp(pokok)} + {rp(bunga)} = {rp(pokok + bunga)}." if jenis == "total" else ""), tampil=rp)


def bangun_datar(rng):
    jenis = rng.choice(["luas_persegi_panjang", "keliling_persegi_panjang", "luas_segitiga", "luas_lingkaran", "keliling_lingkaran", "volume_balok", "volume_kubus", "luas_trapesium"])
    if jenis == "luas_persegi_panjang":
        p, l = rng.randint(6, 30), rng.randint(3, 20)
        return soal("Geometri", f"Sebuah persegi panjang berukuran panjang {p} cm dan lebar {l} cm. Luasnya adalah ... cm².", p * l, pengecoh_numerik(rng, p * l, [2 * (p + l), p + l]), f"Luas = panjang × lebar = {p} × {l} = {p * l} cm².")
    if jenis == "keliling_persegi_panjang":
        p, l = rng.randint(6, 30), rng.randint(3, 20)
        return soal("Geometri", f"Sebuah persegi panjang berukuran panjang {p} cm dan lebar {l} cm. Kelilingnya adalah ... cm.", 2 * (p + l), pengecoh_numerik(rng, 2 * (p + l), [p * l, p + l]), f"Keliling = 2 × (panjang + lebar) = 2 × ({p} + {l}) = {2 * (p + l)} cm.")
    if jenis == "luas_segitiga":
        a, t = rng.randint(4, 30) * 2, rng.randint(3, 20)
        return soal("Geometri", f"Sebuah segitiga memiliki alas {a} cm dan tinggi {t} cm. Luasnya adalah ... cm².", a * t // 2, pengecoh_numerik(rng, a * t // 2, [a * t, a + t]), f"Luas = ½ × alas × tinggi = ½ × {a} × {t} = {a * t // 2} cm².")
    if jenis == "luas_lingkaran":
        r = rng.choice([7, 14, 21, 28, 35])
        benar = 22 * r * r // 7
        return soal("Geometri", f"Sebuah lingkaran berjari-jari {r} cm. Dengan π = 22/7, luas lingkaran adalah ... cm².", benar, pengecoh_numerik(rng, benar, [2 * 22 * r // 7, 22 * r * r // 14]), f"Luas = π × r² = 22/7 × {r} × {r} = {benar} cm².")
    if jenis == "keliling_lingkaran":
        r = rng.choice([7, 14, 21, 28, 35])
        benar = 2 * 22 * r // 7
        return soal("Geometri", f"Sebuah lingkaran berjari-jari {r} cm. Dengan π = 22/7, kelilingnya adalah ... cm.", benar, pengecoh_numerik(rng, benar, [22 * r * r // 7, 22 * r // 7]), f"Keliling = 2 × π × r = 2 × 22/7 × {r} = {benar} cm.")
    if jenis == "volume_balok":
        p, l, t = rng.randint(3, 15), rng.randint(3, 12), rng.randint(2, 10)
        return soal("Geometri", f"Sebuah balok berukuran panjang {p} cm, lebar {l} cm, dan tinggi {t} cm. Volumenya adalah ... cm³.", p * l * t, pengecoh_numerik(rng, p * l * t, [p * l + t, 2 * (p * l + p * t + l * t)]), f"Volume = p × l × t = {p} × {l} × {t} = {p * l * t} cm³.")
    if jenis == "volume_kubus":
        s = rng.randint(3, 14)
        return soal("Geometri", f"Sebuah kubus memiliki panjang rusuk {s} cm. Volumenya adalah ... cm³.", s**3, pengecoh_numerik(rng, s**3, [s * s, 6 * s * s]), f"Volume = rusuk³ = {s}³ = {s**3} cm³.")
    a, b, t = rng.randint(4, 14), rng.randint(15, 30), rng.randint(2, 12) * 2
    benar = (a + b) * t // 2
    return soal("Geometri", f"Sebuah trapesium memiliki sisi sejajar {a} cm dan {b} cm serta tinggi {t} cm. Luasnya adalah ... cm².", benar, pengecoh_numerik(rng, benar, [(a + b) * t, a * b]), f"Luas = ½ × (jumlah sisi sejajar) × tinggi = ½ × ({a} + {b}) × {t} = {benar} cm².")


def tabel_data(rng):
    bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni"]
    n = rng.choice([4, 5, 6])
    nilai = rng.sample(range(40, 200, 5), n)
    jenis = rng.choice(["tertinggi", "terendah", "selisih", "rata"])
    baris = "\n".join(f"{bulan[i]}: {nilai[i]} unit" for i in range(n))
    if jenis == "tertinggi":
        benar = bulan[nilai.index(max(nilai))]
        return soal("Interpretasi data", f"Data penjualan sebuah toko:\n{baris}\nBulan dengan penjualan tertinggi adalah ...", benar, [b for b in bulan[:n] if b != benar] + ["Juli", "Agustus"], f"Nilai tertinggi {max(nilai)} unit pada bulan {benar}.", tampil=lambda x: x)
    if jenis == "terendah":
        benar = bulan[nilai.index(min(nilai))]
        return soal("Interpretasi data", f"Data penjualan sebuah toko:\n{baris}\nBulan dengan penjualan terendah adalah ...", benar, [b for b in bulan[:n] if b != benar] + ["Juli", "Agustus"], f"Nilai terendah {min(nilai)} unit pada bulan {benar}.", tampil=lambda x: x)
    if jenis == "selisih":
        benar = max(nilai) - min(nilai)
        return soal("Interpretasi data", f"Data penjualan sebuah toko:\n{baris}\nSelisih penjualan tertinggi dan terendah adalah ... unit.", benar, pengecoh_numerik(rng, benar, [max(nilai), min(nilai)]), f"Selisih = {max(nilai)} - {min(nilai)} = {benar} unit.")
    total = sum(nilai)
    if total % n:
        raise ValueError("rata tidak bulat")
    benar = total // n
    return soal("Interpretasi data", f"Data penjualan sebuah toko:\n{baris}\nRata-rata penjualan per bulan adalah ... unit.", benar, pengecoh_numerik(rng, benar, [max(nilai), (max(nilai) + min(nilai)) // 2]), f"Total = {total} unit dibagi {n} bulan = {benar} unit.")


def sudut_jam(rng):
    jam = rng.randint(1, 12)
    menit = rng.choice([0, 10, 20, 30, 40, 50])
    sudut = abs(30 * jam - 5.5 * menit)
    if sudut > 180:
        sudut = 360 - sudut
    benar = int(sudut)
    if benar != sudut:
        raise ValueError("sudut tidak bulat")
    return soal("Hitungan", f"Besar sudut terkecil yang dibentuk jarum jam dan jarum menit pada pukul {jam:02d}.{menit:02d} adalah ... derajat.", benar, pengecoh_numerik(rng, benar, [30 * jam, 6 * menit, abs(30 * jam - 6 * menit)]), f"Sudut = |30 × {jam} - 5,5 × {menit}| = {abs(30 * jam - 5.5 * menit):g}°; sudut terkecil {benar}°.")


def deret_huruf(rng):
    langkah = rng.randint(1, 4)
    awal = rng.randint(0, 25 - langkah * 6)
    deret = [HURUF[awal + langkah * i] for i in range(6)]
    benar = HURUF[awal + langkah * 6] if awal + langkah * 6 < 26 else None
    if benar is None:
        raise ValueError("lewat Z")
    kandidat = [HURUF[(awal + langkah * 6 + k) % 26] for k in (1, -1, 2, -2, langkah)]
    return soal("Deret huruf", f"Tentukan huruf berikutnya dari deret: {', '.join(deret)}, ...", benar, kandidat, f"Setiap huruf bergeser {langkah} urutan alfabet. Setelah {deret[-1]} adalah {benar}.", tampil=lambda x: x)


def kombinatorika(rng):
    jenis = rng.choice(["kombinasi", "permutasi"])
    n = rng.randint(5, 10)
    k = rng.randint(2, 3)
    if jenis == "kombinasi":
        benar = math.comb(n, k)
        return soal("Kombinatorika", f"Dari {n} orang akan dipilih {k} orang untuk menjadi anggota tim. Banyak cara memilihnya adalah ...", benar, pengecoh_numerik(rng, benar, [math.perm(n, k), n * k, benar + n]), f"Urutan tidak penting, maka kombinasi C({n},{k}) = {benar}.")
    benar = math.perm(n, k)
    jabatan = "ketua, sekretaris" + (", dan bendahara" if k == 3 else "")
    return soal("Kombinatorika", f"Dari {n} orang akan dipilih {k} orang untuk menduduki jabatan {jabatan} (urutan jabatan berbeda). Banyak susunan yang mungkin adalah ...", benar, pengecoh_numerik(rng, benar, [math.comb(n, k), n * k, benar + n]), f"Urutan penting, maka permutasi P({n},{k}) = {benar}.")


def peluang(rng):
    a, b, c = rng.randint(2, 9), rng.randint(2, 9), rng.randint(2, 9)
    total = a + b + c
    warna = rng.choice([("merah", a), ("biru", b), ("hijau", c)])
    benar = Fraction(warna[1], total)
    salah = [Fraction(warna[1], total + 1), Fraction(total - warna[1], total), Fraction(warna[1], max(a, b, c)), Fraction(1, total), Fraction(warna[1] + 1, total)]
    return soal("Peluang", f"Sebuah kantong berisi {a} kelereng merah, {b} kelereng biru, dan {c} kelereng hijau. Satu kelereng diambil secara acak. Peluang terambil kelereng {warna[0]} adalah ...", benar, salah, f"Peluang = banyak kelereng {warna[0]} ÷ seluruh kelereng = {warna[1]}/{total}" + (f" = {pecahan_teks(benar)}." if benar.denominator != total else "."), tampil=pecahan_teks)


def rata_rata_gabungan(rng):
    for _ in range(200):
        n1, n2 = rng.randint(10, 40), rng.randint(10, 40)
        r1, r2 = rng.randint(60, 90), rng.randint(60, 90)
        if (n1 * r1 + n2 * r2) % (n1 + n2) == 0 and r1 != r2:
            break
    else:
        raise ValueError("tidak ketemu")
    benar = (n1 * r1 + n2 * r2) // (n1 + n2)
    return soal("Rata-rata", f"Rata-rata nilai ujian {n1} siswa kelas A adalah {r1} dan rata-rata nilai {n2} siswa kelas B adalah {r2}. Rata-rata nilai gabungan kedua kelas adalah ...", benar, pengecoh_numerik(rng, benar, [(r1 + r2) // 2, (r1 + r2) // 2 + 1]), f"Total nilai = {n1} × {r1} + {n2} × {r2} = {n1 * r1 + n2 * r2}. Banyak siswa {n1 + n2}. Rata-rata = {benar}.")


def naik_turun_harga(rng):
    for _ in range(200):
        harga = rng.choice([100000, 200000, 250000, 400000, 500000, 800000, 1000000])
        p = rng.choice([10, 20, 25, 30, 40, 50])
        q = rng.choice([10, 20, 25, 30, 40, 50])
        akhir = harga * (100 + p) * (100 - q)
        if akhir % 10000 == 0:
            break
    else:
        raise ValueError("tidak ketemu")
    benar = akhir // 10000
    return soal("Persentase", f"Harga sebuah barang {rp(harga)} dinaikkan {p}% lalu diturunkan {q}% dari harga setelah kenaikan. Harga akhirnya adalah ...", benar, [harga, harga + harga * (p - q) // 100, benar + harga // 10, benar - harga // 20, harga * (100 + p - q) // 100 + 5000], f"Setelah naik {p}%: {rp(harga * (100 + p) // 100)}. Setelah turun {q}%: {rp(benar)}.", tampil=rp)


def konversi_satuan(rng):
    jenis = rng.choice(["kmjam_ms", "ms_kmjam", "liter_ml", "jam_menit"])
    if jenis == "kmjam_ms":
        v = rng.choice([18, 36, 54, 72, 90, 108, 126])
        return soal("Hitungan", f"Kecepatan {v} km/jam sama dengan ... m/detik.", v * 5 // 18, pengecoh_numerik(rng, v * 5 // 18, [v * 18 // 5, v // 3]), f"1 km/jam = 5/18 m/detik, maka {v} × 5/18 = {v * 5 // 18} m/detik.")
    if jenis == "ms_kmjam":
        v = rng.choice([5, 10, 15, 20, 25, 30])
        return soal("Hitungan", f"Kecepatan {v} m/detik sama dengan ... km/jam.", v * 18 // 5, pengecoh_numerik(rng, v * 18 // 5, [v * 5 // 18 + 1, v * 3]), f"1 m/detik = 18/5 km/jam, maka {v} × 18/5 = {v * 18 // 5} km/jam.")
    if jenis == "liter_ml":
        l = rng.choice([2, 3, 5, 8, 12])
        ml = rng.choice([250, 500, 750])
        return soal("Hitungan", f"{l} liter ditambah {ml} mililiter sama dengan ... mililiter.", l * 1000 + ml, [l * 100 + ml, l * 10 + ml, l * 1000, (l + ml) * 10], f"{l} liter = {l * 1000} mL. Ditambah {ml} mL menjadi {l * 1000 + ml} mL.")
    jam, menit = rng.randint(1, 5), rng.choice([15, 20, 30, 45])
    return soal("Hitungan", f"{jam} jam {menit} menit sama dengan ... menit.", jam * 60 + menit, pengecoh_numerik(rng, jam * 60 + menit, [jam * 100 + menit, jam * 10 + menit]), f"{jam} jam = {jam * 60} menit. Ditambah {menit} menit menjadi {jam * 60 + menit} menit.")


def skala_peta(rng):
    skala = rng.choice([100000, 200000, 250000, 500000, 1000000])
    cm = rng.choice([2, 4, 5, 6, 8, 10, 12])
    km = skala * cm / 100000
    if km != int(km):
        raise ValueError("tidak bulat")
    benar = int(km)
    return soal("Perbandingan", f"Pada peta berskala 1 : {angka(skala)}, jarak dua kota adalah {cm} cm. Jarak sebenarnya adalah ... km.", benar, pengecoh_numerik(rng, benar, [benar * 10, max(benar // 10, 1), benar + cm]), f"Jarak sebenarnya = {cm} × {angka(skala)} cm = {angka(skala * cm)} cm = {benar} km.")


def durasi_waktu(rng):
    jam1, menit1 = rng.randint(5, 10), rng.choice([0, 15, 30, 45])
    durasi = rng.choice([95, 130, 185, 215, 260, 305])
    total = jam1 * 60 + menit1 + durasi
    jam2, menit2 = divmod(total, 60)
    if jam2 >= 24:
        raise ValueError("lewat hari")
    benar = f"{jam2:02d}.{menit2:02d}"
    kandidat = [f"{(jam2 + k) % 24:02d}.{menit2:02d}" for k in (1, -1, 2)] + [f"{jam2:02d}.{(menit2 + 15) % 60:02d}", f"{jam2:02d}.{(menit2 + 30) % 60:02d}"]
    return soal("Hitungan", f"Sebuah kereta berangkat pukul {jam1:02d}.{menit1:02d} dan menempuh perjalanan selama {durasi // 60} jam {durasi % 60} menit. Kereta tiba pada pukul ...", benar, kandidat, f"{jam1:02d}.{menit1:02d} ditambah {durasi} menit menjadi {benar}.", tampil=lambda x: x)
