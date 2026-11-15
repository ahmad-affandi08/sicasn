import random


def rp(angka):
    return "Rp" + format(angka, ",").replace(",", ".")


def angka(nilai):
    if isinstance(nilai, float) and nilai == int(nilai):
        nilai = int(nilai)
    if isinstance(nilai, int):
        return format(nilai, ",").replace(",", ".")
    return str(nilai).replace(".", ",")


def bentuk_opsi(rng, benar, kandidat_salah, tampil=str):
    unik = []
    for nilai in kandidat_salah:
        if nilai != benar and nilai not in unik:
            unik.append(nilai)
    if len(unik) < 4:
        raise ValueError("pengecoh kurang")
    terpilih = unik[:4]
    semua = [benar] + terpilih
    rng.shuffle(semua)
    return [tampil(nilai) for nilai in semua], semua.index(benar)


def soal(subkategori, pertanyaan, benar, salah, pembahasan, tampil=None):
    ubah = tampil or angka
    return {
        "subkategori": subkategori,
        "pertanyaan": pertanyaan,
        "benar": ubah(benar),
        "salah": [ubah(nilai) for nilai in salah],
        "pembahasan": pembahasan,
    }


def pengecoh_numerik(rng, benar, tambahan=()):
    dasar = [benar + 1, benar - 1, benar + 2, benar - 2, benar + 5, benar - 5, benar + 10, benar - 10, benar * 2, benar + 3, benar - 3]
    kandidat = list(tambahan) + dasar
    rng.shuffle(kandidat)
    hasil = []
    for nilai in list(tambahan) + kandidat:
        if nilai != benar and nilai not in hasil and nilai > -1000:
            hasil.append(nilai)
    return hasil
