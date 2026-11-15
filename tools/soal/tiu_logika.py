import itertools

from dasar import soal

NAMA = ["Andi", "Budi", "Citra", "Dewi", "Eko", "Fajar", "Gita", "Hadi", "Indah", "Joko", "Kirana", "Lukman"]

KLAUSA = [
    ("hujan turun", "hujan tidak turun"),
    ("harga bahan baku naik", "harga bahan baku tidak naik"),
    ("pegawai itu disiplin", "pegawai itu tidak disiplin"),
    ("laporan dikumpulkan tepat waktu", "laporan tidak dikumpulkan tepat waktu"),
    ("mesin dirawat rutin", "mesin tidak dirawat rutin"),
    ("peserta lulus ujian", "peserta tidak lulus ujian"),
    ("anggaran disetujui", "anggaran tidak disetujui"),
    ("rapat dimulai", "rapat tidak dimulai"),
    ("jalan raya diperbaiki", "jalan raya tidak diperbaiki"),
    ("sistem diperbarui", "sistem tidak diperbarui"),
]

KATEGORI = [
    ("pegawai honorer", "pekerja keras", "dihargai atasan"),
    ("mahasiswa", "peserta seminar", "pemegang sertifikat"),
    ("petani", "warga desa", "anggota koperasi"),
    ("atlet", "orang yang berlatih rutin", "orang yang bugar"),
    ("guru", "lulusan perguruan tinggi", "pemegang ijazah"),
    ("pedagang pasar", "wajib pajak", "pencatat omzet"),
    ("relawan", "warga peduli", "peserta kerja bakti"),
    ("perawat", "tenaga kesehatan", "pekerja shift"),
]


def kesimpulan(rng, sub, premis, benar, salah, penjelasan):
    return soal(sub, premis + "\nKesimpulan yang pasti benar adalah ...", benar, salah, penjelasan, tampil=lambda teks: teks)


def modus_ponens(rng):
    p, q = rng.sample(KLAUSA, 2)
    premis = f"Premis 1: Jika {p[0]}, maka {q[0]}.\nPremis 2: {p[0].capitalize()}."
    return kesimpulan(rng, "Penalaran logis", premis, q[0].capitalize() + ".", [q[1].capitalize() + ".", p[1].capitalize() + ".", f"{q[0].capitalize()} tidak bergantung pada {p[0]}.", f"{p[0].capitalize()} hanya terjadi bila {q[0]}."], f"Ini modus ponens: jika P maka Q, dan P benar, maka Q pasti benar. Jadi {q[0]}.")


def modus_tollens(rng):
    p, q = rng.sample(KLAUSA, 2)
    premis = f"Premis 1: Jika {p[0]}, maka {q[0]}.\nPremis 2: {q[1].capitalize()}."
    return kesimpulan(rng, "Penalaran logis", premis, p[1].capitalize() + ".", [p[0].capitalize() + ".", q[0].capitalize() + ".", f"Tidak dapat disimpulkan apakah {p[0]} atau tidak.", f"{q[1].capitalize()} karena {p[1]}."], f"Ini modus tollens: jika P maka Q, dan Q tidak terjadi, maka P pasti tidak terjadi. Jadi {p[1]}.")


def silogisme_hipotetik(rng):
    p, q, r = rng.sample(KLAUSA, 3)
    premis = f"Premis 1: Jika {p[0]}, maka {q[0]}.\nPremis 2: Jika {q[0]}, maka {r[0]}."
    return kesimpulan(rng, "Penalaran logis", premis, f"Jika {p[0]}, maka {r[0]}.", [f"Jika {r[0]}, maka {p[0]}.", f"Jika {p[1]}, maka {r[1]}.", f"{p[0].capitalize()} dan {r[1]}.", f"Jika {r[1]}, maka {q[0]}."], "Silogisme hipotetik: jika P maka Q dan jika Q maka R, maka jika P maka R. Kebalikan atau invers dari pernyataan tidak otomatis benar.")


def menyangkal_anteseden(rng):
    p, q = rng.sample(KLAUSA, 2)
    premis = f"Premis 1: Jika {p[0]}, maka {q[0]}.\nPremis 2: {p[1].capitalize()}."
    return kesimpulan(rng, "Penalaran logis", premis, f"Tidak dapat disimpulkan apakah {q[0]} atau tidak.", [f"{q[0].capitalize()}.", f"{q[1].capitalize()}.", f"{p[0].capitalize()}.", f"{q[1].capitalize()} dan {p[1]}."], f"Premis hanya menjamin Q bila P terjadi. Karena P tidak terjadi, Q bisa terjadi atau tidak karena sebab lain. Menyimpulkan 'tidak {q[0]}' adalah kekeliruan menyangkal anteseden.")


def menegaskan_konsekuen(rng):
    p, q = rng.sample(KLAUSA, 2)
    premis = f"Premis 1: Jika {p[0]}, maka {q[0]}.\nPremis 2: {q[0].capitalize()}."
    return kesimpulan(rng, "Penalaran logis", premis, f"Tidak dapat disimpulkan apakah {p[0]} atau tidak.", [f"{p[0].capitalize()}.", f"{p[1].capitalize()}.", f"{p[0].capitalize()} dan {q[1]}.", f"{q[1].capitalize()}."], f"Q bisa terjadi karena sebab lain selain P. Menyimpulkan '{p[0]}' adalah kekeliruan menegaskan konsekuen.")


def silogisme_semua(rng):
    a, b, c = rng.choice(KATEGORI)
    premis = f"Premis 1: Semua {a} adalah {b}.\nPremis 2: Semua {b} adalah {c}."
    return kesimpulan(rng, "Penalaran logis", premis, f"Semua {a} adalah {c}.", [f"Semua {c} adalah {a}.", f"Tidak ada {a} yang {c}.", f"Semua {c} adalah {b}.", f"Sebagian {b} bukan {a}."], "Silogisme kategorik: semua A adalah B dan semua B adalah C, maka semua A adalah C. Arah sebaliknya tidak berlaku.")


def silogisme_tidak_ada(rng):
    a, b, c = rng.choice(KATEGORI)
    premis = f"Premis 1: Semua {a} adalah {b}.\nPremis 2: Tidak ada {b} yang {c}."
    return kesimpulan(rng, "Penalaran logis", premis, f"Tidak ada {a} yang {c}.", [f"Semua {a} adalah {c}.", f"Sebagian {a} adalah {c}.", f"Semua {c} adalah {a}.", f"Sebagian {c} adalah {a}."], f"Semua A termasuk B, dan tidak satu pun B termasuk C. Maka tidak ada A yang C.")


def silogisme_sebagian(rng):
    a, b, c = rng.choice(KATEGORI)
    premis = f"Premis 1: Sebagian {a} adalah {b}.\nPremis 2: Semua {b} adalah {c}."
    return kesimpulan(rng, "Penalaran logis", premis, f"Sebagian {a} adalah {c}.", [f"Semua {a} adalah {c}.", f"Tidak ada {a} yang {c}.", f"Semua {c} adalah {a}.", f"Sebagian {c} bukan {b}."], "Sebagian A adalah B dan semua B adalah C, maka sebagian A adalah C. Kata 'semua' pada kesimpulan terlalu kuat.")


def entitas_anggota(rng):
    a, b, c = rng.choice(KATEGORI)
    nama = rng.choice(NAMA)
    premis = f"Premis 1: Semua {a} adalah {b}.\nPremis 2: {nama} adalah {a}."
    return kesimpulan(rng, "Penalaran logis", premis, f"{nama} adalah {b}.", [f"{nama} bukan {b}.", f"Semua {b} adalah {nama}.", f"{nama} mungkin bukan {a}.", f"Tidak ada {a} yang {b}."], f"{nama} termasuk {a}, dan semua {a} adalah {b}. Maka {nama} adalah {b}.")


def disjungsi(rng):
    p, q = rng.sample(KLAUSA, 2)
    premis = f"Premis 1: {p[0].capitalize()} atau {q[0]}.\nPremis 2: {p[1].capitalize()}."
    return kesimpulan(rng, "Penalaran logis", premis, q[0].capitalize() + ".", [q[1].capitalize() + ".", p[0].capitalize() + ".", f"{p[1].capitalize()} dan {q[1]}.", f"Tidak dapat disimpulkan apa pun."], f"Silogisme disjungtif: P atau Q, dan P tidak benar, maka Q benar.")


def urutan_posisi(rng):
    sifat = rng.choice([("tinggi", "lebih tinggi dari", "lebih pendek dari", "paling tinggi", "paling pendek"), ("tua", "lebih tua dari", "lebih muda dari", "paling tua", "paling muda"), ("berat", "lebih berat dari", "lebih ringan dari", "paling berat", "paling ringan")])
    jumlah = rng.choice([4, 5])
    orang = rng.sample(NAMA, jumlah)
    pasangan = [(orang[i], orang[i + 1]) for i in range(jumlah - 1)]
    kalimat = []
    for atas, bawah in pasangan:
        if rng.random() < 0.5:
            kalimat.append(f"{atas} {sifat[1]} {bawah}.")
        else:
            kalimat.append(f"{bawah} {sifat[2]} {atas}.")
    rng.shuffle(kalimat)
    pertanyaan_tipe = rng.choice(["atas", "bawah", "kedua"])
    if pertanyaan_tipe == "atas":
        tanya, benar = f"Siapakah yang {sifat[3]}?", orang[0]
    elif pertanyaan_tipe == "bawah":
        tanya, benar = f"Siapakah yang {sifat[4]}?", orang[-1]
    else:
        tanya, benar = f"Siapakah yang berada di urutan kedua dari yang {sifat[3]}?", orang[1]
    daftar = "\n".join(kalimat)
    benar_pasti = benar
    semua = [p for p in itertools.permutations(orang) if all(p.index(a) < p.index(b) for a, b in pasangan)]
    if len(semua) != 1:
        raise ValueError("tidak unik")
    pembahasan = f"Urutan dari yang {sifat[3]} sampai {sifat[4]}: {', '.join(orang)}. Jadi jawabannya {benar_pasti}."
    lain = [n for n in orang if n != benar_pasti]
    salah = lain[:4]
    sisa = [n for n in NAMA if n not in orang]
    while len(salah) < 4:
        salah.append(sisa.pop())
    return soal("Penalaran analitis", f"Perhatikan pernyataan berikut:\n{daftar}\n{tanya}", benar_pasti, salah, pembahasan, tampil=lambda teks: teks)
