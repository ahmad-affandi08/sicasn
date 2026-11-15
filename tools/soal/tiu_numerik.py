import math

from dasar import angka, pengecoh_numerik, rp, soal


def deret_aritmetika(rng):
    a = rng.randint(1, 40)
    d = rng.choice([2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 15, -2, -3, -4, -5])
    if d < 0:
        a += 60
    deret = [a + d * i for i in range(6)]
    benar = a + d * 6
    teks = ", ".join(str(x) for x in deret)
    return soal("Deret angka", f"Tentukan angka berikutnya dari deret: {teks}, ...", benar, pengecoh_numerik(rng, benar, [benar + d, benar - d]), f"Selisih tiap suku tetap {d}. Suku berikutnya = {deret[-1]} {'+' if d > 0 else '-'} {abs(d)} = {benar}.")


def deret_geometri(rng):
    a = rng.choice([1, 2, 3, 4, 5, 6])
    r = rng.choice([2, 3, 4, 5])
    deret = [a * r**i for i in range(5)]
    benar = a * r**5
    teks = ", ".join(str(x) for x in deret)
    return soal("Deret angka", f"Tentukan angka berikutnya dari deret: {teks}, ...", benar, pengecoh_numerik(rng, benar, [deret[-1] + r, deret[-1] * (r + 1), deret[-1] + deret[-2]]), f"Setiap suku dikalikan {r}. Suku berikutnya = {deret[-1]} × {r} = {benar}.")


def deret_selisih_bertingkat(rng):
    a = rng.randint(1, 20)
    d1 = rng.randint(1, 6)
    k = rng.randint(1, 4)
    deret = [a]
    selisih = d1
    for _ in range(6):
        deret.append(deret[-1] + selisih)
        selisih += k
    benar = deret[-1]
    tampil = deret[:-1]
    teks = ", ".join(str(x) for x in tampil)
    selisih_urut = [tampil[i + 1] - tampil[i] for i in range(len(tampil) - 1)]
    return soal("Deret angka", f"Tentukan angka berikutnya dari deret: {teks}, ...", benar, pengecoh_numerik(rng, benar, [benar - k, benar + k, tampil[-1] + selisih_urut[-1]]), f"Selisih antar suku berturut-turut: {', '.join(map(str, selisih_urut))}, bertambah {k} tiap langkah. Selisih berikutnya {selisih_urut[-1] + k}, sehingga suku berikutnya {tampil[-1]} + {selisih_urut[-1] + k} = {benar}.")


def deret_kuadrat(rng):
    c = rng.randint(-3, 9)
    awal = rng.randint(1, 4)
    deret = [(awal + i) ** 2 + c for i in range(6)]
    benar = (awal + 6) ** 2 + c
    teks = ", ".join(str(x) for x in deret)
    ket = f"n² {'+' if c >= 0 else '-'} {abs(c)}" if c != 0 else "n²"
    return soal("Deret angka", f"Tentukan angka berikutnya dari deret: {teks}, ...", benar, pengecoh_numerik(rng, benar, [benar - 2, benar + 2, deret[-1] + 2 * (awal + 5) + 3]), f"Suku-sukunya berpola {ket} dengan n = {awal}, {awal + 1}, dan seterusnya. Untuk n = {awal + 6}: {(awal + 6) ** 2}{(' ' + ('+' if c >= 0 else '-') + ' ' + str(abs(c))) if c != 0 else ''} = {benar}.")


def deret_ganda(rng):
    a = rng.randint(2, 20)
    d = rng.randint(2, 6)
    b = rng.randint(30, 60)
    e = rng.randint(2, 7)
    panjang = 8
    deret = []
    for i in range(panjang):
        deret.append(a + d * (i // 2) if i % 2 == 0 else b - e * (i // 2))
    benar = a + d * (panjang // 2)
    tampil = deret[:panjang]
    teks = ", ".join(str(x) for x in tampil)
    return soal("Deret angka", f"Tentukan angka berikutnya dari deret: {teks}, ...", benar, pengecoh_numerik(rng, benar, [b - e * (panjang // 2), benar + e, benar - d]), f"Deret ini gabungan dua pola. Suku ganjil: {', '.join(str(x) for x in tampil[::2])} naik {d} tiap langkah. Suku genap: {', '.join(str(x) for x in tampil[1::2])} turun {e}. Suku ke-9 termasuk pola pertama: {tampil[-2]} + {d} = {benar}.")


def deret_fibonacci(rng):
    a = rng.randint(1, 6)
    b = rng.randint(2, 9)
    deret = [a, b]
    for _ in range(5):
        deret.append(deret[-1] + deret[-2])
    benar = deret[-1] + deret[-2]
    teks = ", ".join(str(x) for x in deret)
    return soal("Deret angka", f"Tentukan angka berikutnya dari deret: {teks}, ...", benar, pengecoh_numerik(rng, benar, [deret[-1] * 2, deret[-1] + deret[-3], benar + 1]), f"Setiap suku adalah jumlah dua suku sebelumnya. Suku berikutnya = {deret[-2]} + {deret[-1]} = {benar}.")


def deret_kali_tambah(rng):
    kali = rng.choice([2, 3])
    tambah = rng.choice([-3, -2, -1, 1, 2, 3, 4, 5])
    a = rng.randint(1, 6)
    deret = [a]
    for _ in range(5):
        deret.append(deret[-1] * kali + tambah)
    benar = deret[-1] * kali + tambah
    teks = ", ".join(str(x) for x in deret)
    operasi = f"{'+' if tambah > 0 else '-'} {abs(tambah)}"
    return soal("Deret angka", f"Tentukan angka berikutnya dari deret: {teks}, ...", benar, pengecoh_numerik(rng, benar, [deret[-1] * kali, deret[-1] * kali - tambah, deret[-1] + (deret[-1] - deret[-2])]), f"Setiap suku dikalikan {kali} lalu {operasi}. Suku berikutnya = {deret[-1]} × {kali} {operasi} = {benar}.")


def hitung_persen_pecahan(rng):
    p = rng.choice([10, 20, 25, 30, 40, 50, 60, 75])
    n = rng.choice([80, 120, 160, 200, 240, 320, 400, 480, 600])
    penyebut = rng.choice([2, 3, 4, 5, 6])
    m = penyebut * rng.randint(10, 40)
    benar = n * p // 100 + m // penyebut
    return soal("Hitungan", f"Hasil dari {p}% × {n} + 1/{penyebut} × {m} adalah ...", benar, pengecoh_numerik(rng, benar, [n * p // 100 - m // penyebut, n * p // 100 + m, n * p // 10 + m // penyebut]), f"{p}% × {n} = {n * p // 100}. 1/{penyebut} × {m} = {m // penyebut}. Jumlahnya {n * p // 100} + {m // penyebut} = {benar}.")


def operasi_urutan(rng):
    a, b, c, d = rng.randint(2, 9), rng.randint(2, 9), rng.randint(2, 9), rng.randint(10, 40)
    benar = a * b + d - c * 2
    return soal("Hitungan", f"Hasil dari {a} × {b} + {d} - {c} × 2 adalah ...", benar, pengecoh_numerik(rng, benar, [(a * b + d - c) * 2, a * (b + d) - c * 2, a * b + (d - c) * 2]), f"Kerjakan perkalian lebih dulu: {a} × {b} = {a * b} dan {c} × 2 = {c * 2}. Lalu {a * b} + {d} - {c * 2} = {benar}.")


def diskon_untung(rng):
    harga = rng.choice([200000, 250000, 300000, 400000, 500000, 600000, 800000, 1200000])
    diskon = rng.choice([10, 15, 20, 25, 30, 40])
    jenis = rng.choice(["bayar", "hemat"])
    hemat = harga * diskon // 100
    benar = harga - hemat if jenis == "bayar" else hemat
    kandidat = [harga + hemat, harga - hemat * 2, hemat, harga - hemat, harga * (100 - diskon) // 10]
    judul = "Seorang pembeli mendapat diskon" if jenis == "bayar" else "Sebuah toko memberi diskon"
    tanya = f"{judul} {diskon}% untuk barang seharga {rp(harga)}. " + ("Berapa yang harus dibayar?" if jenis == "bayar" else "Berapa rupiah potongan harganya?")
    return soal("Persentase", tanya, benar, kandidat, f"Potongan = {diskon}% × {rp(harga)} = {rp(hemat)}. " + (f"Harga yang dibayar = {rp(harga)} - {rp(hemat)} = {rp(benar)}." if jenis == "bayar" else f"Jadi potongan harganya {rp(benar)}."), tampil=rp)


def untung_rugi(rng):
    beli = rng.choice([40000, 50000, 80000, 100000, 120000, 200000, 250000])
    persen = rng.choice([10, 12, 15, 20, 25, 30])
    untung = rng.random() < 0.6
    jual = beli + beli * persen // 100 if untung else beli - beli * persen // 100
    kata = "untung" if untung else "rugi"
    return soal("Persentase", f"Sebuah barang dibeli seharga {rp(beli)} dan dijual dengan {kata} {persen}%. Harga jualnya adalah ...", jual, [beli + beli * persen // 100 if not untung else beli - beli * persen // 100, beli * persen // 100, jual + beli * persen // 100, beli + persen * 1000], f"{kata.capitalize()} = {persen}% × {rp(beli)} = {rp(beli * persen // 100)}. Harga jual = {rp(beli)} {'+' if untung else '-'} {rp(beli * persen // 100)} = {rp(jual)}.", tampil=rp)


def kecepatan_jarak(rng):
    v = rng.choice([40, 45, 50, 60, 72, 80])
    t = rng.choice([2, 3, 4, 5])
    jenis = rng.choice(["jarak", "waktu"])
    jarak = v * t
    if jenis == "jarak":
        return soal("Soal cerita", f"Sebuah mobil melaju dengan kecepatan tetap {v} km/jam selama {t} jam. Jarak yang ditempuh adalah ... km.", jarak, pengecoh_numerik(rng, jarak, [v + t, v * (t + 1), v * (t - 1)]), f"Jarak = kecepatan × waktu = {v} × {t} = {jarak} km.")
    return soal("Soal cerita", f"Jarak dua kota {jarak} km. Waktu tempuh dengan kecepatan tetap {v} km/jam adalah ... jam.", t, pengecoh_numerik(rng, t, [t + 1, t - 1, t + 2]), f"Waktu = jarak ÷ kecepatan = {jarak} ÷ {v} = {t} jam.")


def dua_kendaraan_bertemu(rng):
    v1 = rng.choice([40, 50, 60, 70])
    v2 = rng.choice([30, 40, 50, 60])
    t = rng.choice([2, 3, 4])
    jarak = (v1 + v2) * t
    return soal("Soal cerita", f"Dua kendaraan berangkat bersamaan dari kota yang berjarak {jarak} km dan saling mendekat dengan kecepatan {v1} km/jam dan {v2} km/jam. Keduanya bertemu setelah ... jam.", t, pengecoh_numerik(rng, t, [t + 1, t - 1]), f"Kecepatan mendekat = {v1} + {v2} = {v1 + v2} km/jam. Waktu bertemu = {jarak} ÷ {v1 + v2} = {t} jam.")


def rata_rata_kecepatan(rng):
    v1 = rng.choice([40, 60, 30, 20])
    v2 = rng.choice([60, 120, 90, 30])
    if v1 == v2:
        v2 += 30
    benar_f = 2 * v1 * v2 / (v1 + v2)
    if abs(benar_f - round(benar_f)) > 1e-9:
        v1, v2 = 40, 60
        benar_f = 48
    benar = int(round(benar_f))
    return soal("Soal cerita", f"Seorang pengendara pergi dari kota A ke kota B dengan kecepatan {v1} km/jam dan kembali lewat jalan yang sama dengan kecepatan {v2} km/jam. Kecepatan rata-rata seluruh perjalanan adalah ... km/jam.", benar, pengecoh_numerik(rng, benar, [(v1 + v2) // 2, (v1 + v2) // 2 + 2, abs(v1 - v2)]), f"Kecepatan rata-rata = jarak total ÷ waktu total = 2 × {v1} × {v2} ÷ ({v1} + {v2}) = {benar} km/jam. Bukan rata-rata hitung biasa.")


def pekerjaan(rng):
    pasangan = [(6, 12, 4), (10, 15, 6), (12, 24, 8), (20, 30, 12), (15, 30, 10), (30, 60, 20), (9, 18, 6), (8, 24, 6), (12, 4, 3), (18, 9, 6), (10, 40, 8), (14, 35, 10)]
    x, y, h = rng.choice(pasangan)
    pekerjaan_nama = rng.choice(["mengecat sebuah ruangan", "membangun pagar", "menyelesaikan laporan", "membersihkan kebun"])
    return soal("Soal cerita", f"Andi dapat {pekerjaan_nama} dalam {x} hari, sedangkan Budi dapat menyelesaikannya dalam {y} hari. Jika keduanya bekerja bersama, pekerjaan itu selesai dalam ... hari.", h, pengecoh_numerik(rng, h, [(x + y) // 2, x + y, abs(x - y)]), f"Kerja Andi per hari = 1/{x}, Budi = 1/{y}. Bersama = 1/{x} + 1/{y} = {x + y}/{x * y} pekerjaan per hari. Waktu bersama = {x * y}/{x + y} = {h} hari.")


def umur(rng):
    anak = rng.randint(5, 14)
    k = rng.choice([3, 4, 5])
    t = rng.choice([2, 3, 4, 5, 6])
    ayah = anak * k
    jumlah = ayah + anak + 2 * t
    return soal("Soal cerita", f"Umur ayah {k} kali umur anaknya. {t} tahun lagi jumlah umur mereka {jumlah} tahun. Umur anak sekarang adalah ... tahun.", anak, pengecoh_numerik(rng, anak, [anak + t, anak - t, ayah // 2]), f"Misal umur anak x, ayah {k}x. Setelah {t} tahun: ({k}x + {t}) + (x + {t}) = {jumlah}, maka {k + 1}x = {jumlah - 2 * t}, x = {anak}.")


def rata_rata(rng):
    n = rng.choice([4, 5, 6])
    nilai = [rng.randint(60, 95) for _ in range(n - 1)]
    target = rng.randint(74, 88)
    kurang = target * n - sum(nilai)
    if not 40 <= kurang <= 100:
        nilai = [70] * (n - 1)
        kurang = target * n - 70 * (n - 1)
    teks = ", ".join(map(str, nilai))
    return soal("Rata-rata", f"Nilai {n - 1} ujian seorang siswa adalah {teks}. Agar rata-rata {n} ujian menjadi {target}, nilai ujian terakhir harus ...", kurang, pengecoh_numerik(rng, kurang, [target, kurang - n, kurang + n]), f"Total yang diperlukan = {target} × {n} = {target * n}. Total {n - 1} nilai pertama = {sum(nilai)}. Nilai terakhir = {target * n} - {sum(nilai)} = {kurang}.")


def perbandingan(rng):
    a, b = rng.choice([(2, 3), (3, 5), (1, 4), (3, 7), (4, 5), (2, 5)])
    kelipatan = rng.choice([20000, 30000, 40000, 50000])
    total = (a + b) * kelipatan
    bagian = rng.choice(["pertama", "kedua"])
    benar = a * kelipatan if bagian == "pertama" else b * kelipatan
    return soal("Perbandingan", f"Uang sebesar {rp(total)} dibagikan kepada dua orang dengan perbandingan {a} : {b}. Bagian orang {bagian} adalah ...", benar, [(b if bagian == "pertama" else a) * kelipatan, total // 2, benar + kelipatan, benar - kelipatan, benar + 2 * kelipatan], f"Jumlah perbandingan {a} + {b} = {a + b}. Satu bagian = {rp(total)} ÷ {a + b} = {rp(kelipatan)}. Bagian orang {bagian} = {a if bagian == 'pertama' else b} × {rp(kelipatan)} = {rp(benar)}.", tampil=rp)


def kpk_fpb(rng):
    a, b = rng.choice([(6, 8), (12, 18), (10, 15), (9, 12), (8, 12), (15, 20), (14, 21), (12, 16)])
    jenis = rng.choice(["kpk", "fpb"])
    kpk = a * b // math.gcd(a, b)
    if jenis == "kpk":
        return soal("Bilangan", f"Lampu A menyala setiap {a} menit dan lampu B setiap {b} menit. Keduanya menyala bersamaan pada pukul 08.00. Keduanya akan menyala bersamaan lagi setelah ... menit.", kpk, pengecoh_numerik(rng, kpk, [a * b, a + b, math.gcd(a, b)]), f"Waktu menyala bersamaan berikutnya adalah KPK dari {a} dan {b}, yaitu {kpk} menit.")
    fpb = math.gcd(a, b)
    jml_a, jml_b = a * 3, b * 3
    return soal("Bilangan", f"Seorang pedagang memiliki {jml_a} apel dan {jml_b} jeruk. Buah itu dibagi ke dalam beberapa keranjang sama banyak tanpa sisa, dengan isi tiap jenis sama. Jumlah keranjang terbanyak adalah ...", fpb * 3, pengecoh_numerik(rng, fpb * 3, [fpb, jml_a, kpk]), f"Jumlah keranjang terbanyak adalah FPB dari {jml_a} dan {jml_b}, yaitu {fpb * 3}.")


def himpunan(rng):
    total = rng.choice([40, 45, 50, 60])
    a = rng.randint(20, total - 8)
    b = rng.randint(15, total - 8)
    irisan = rng.randint(max(0, a + b - total + 2), min(a, b) - 2)
    tidak = total - (a + b - irisan)
    return soal("Himpunan", f"Dari {total} siswa, {a} siswa gemar matematika, {b} siswa gemar fisika, dan {irisan} siswa gemar keduanya. Banyak siswa yang tidak gemar keduanya adalah ...", tidak, pengecoh_numerik(rng, tidak, [total - a - b, total - (a + b + irisan), irisan]), f"Yang gemar paling sedikit satu pelajaran = {a} + {b} - {irisan} = {a + b - irisan}. Yang tidak gemar keduanya = {total} - {a + b - irisan} = {tidak}.")


def aljabar(rng):
    x = rng.randint(2, 15)
    a = rng.randint(2, 9)
    b = rng.randint(1, 20)
    jenis = rng.choice(["a", "b"])
    if jenis == "a":
        c = a * x + b
        return soal("Aljabar", f"Jika {a}x + {b} = {c}, maka nilai x adalah ...", x, pengecoh_numerik(rng, x, [x + 1, x - 1, c // a]), f"{a}x = {c} - {b} = {c - b}, sehingga x = {c - b} ÷ {a} = {x}.")
    c = a * (x - b % 5)
    return soal("Aljabar", f"Jika {a}(x - {b % 5}) = {c}, maka nilai x adalah ...", x, pengecoh_numerik(rng, x, [x - 1, x + 1, c // a - b % 5]), f"Bagi kedua ruas dengan {a}: x - {b % 5} = {c // a}. Maka x = {c // a} + {b % 5} = {x}.")


def kalender(rng):
    hari = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"]
    mulai = rng.randrange(7)
    lompat = rng.choice([30, 45, 60, 100, 150, 200, 365])
    benar = hari[(mulai + lompat) % 7]
    kandidat = [hari[(mulai + lompat + k) % 7] for k in range(1, 7)]
    return soal("Kalender", f"Jika hari ini hari {hari[mulai]}, maka {lompat} hari lagi adalah hari ...", benar, kandidat, f"{lompat} = {lompat // 7} × 7 + {lompat % 7}. Maju {lompat % 7} hari dari {hari[mulai]} adalah {benar}.")
