from dasar import soal

SINONIM = [
    ("Gigih", "Tekun", ["Malas", "Lemah", "Angkuh", "Ragu"]),
    ("Lugas", "Apa adanya", ["Berbelit", "Sopan", "Pelan", "Rumit"]),
    ("Akurat", "Cermat", ["Samar", "Cepat", "Kasar", "Longgar"]),
    ("Transparan", "Terbuka", ["Tertutup", "Rumit", "Gelap", "Samar"]),
    ("Kredibel", "Dapat dipercaya", ["Meragukan", "Populer", "Bersahaja", "Mahal"]),
    ("Mutakhir", "Terbaru", ["Kuno", "Terkenal", "Terbaik", "Termurah"]),
    ("Fleksibel", "Luwes", ["Kaku", "Keras", "Mahal", "Tebal"]),
    ("Vital", "Sangat penting", ["Tidak berguna", "Sementara", "Tersembunyi", "Mudah"]),
    ("Amandemen", "Perubahan", ["Penghapusan", "Pengesahan", "Penolakan", "Pengumuman"]),
    ("Prosedur", "Tata cara", ["Tujuan", "Hasil", "Peralatan", "Hukuman"]),
    ("Inovasi", "Pembaruan", ["Peniruan", "Kebiasaan", "Pengulangan", "Pembatalan"]),
    ("Solusi", "Pemecahan", ["Pertanyaan", "Kesulitan", "Penundaan", "Perdebatan"]),
    ("Eksplisit", "Gamblang", ["Tersirat", "Samar", "Tertutup", "Rahasia"]),
    ("Implisit", "Tersirat", ["Tersurat", "Jelas", "Resmi", "Terbuka"]),
    ("Konsisten", "Taat asas", ["Berubah-ubah", "Asal-asalan", "Terburu-buru", "Berlebihan"]),
    ("Integritas", "Keutuhan", ["Perpecahan", "Kelemahan", "Kebimbangan", "Keraguan"]),
    ("Efektif", "Berhasil guna", ["Sia-sia", "Boros", "Terlambat", "Lambat"]),
    ("Esensial", "Pokok", ["Tambahan", "Hiasan", "Sementara", "Ringan"]),
]

ANTONIM = [
    ("Optimis", "Pesimis", ["Realistis", "Ambisius", "Antusias", "Percaya diri"]),
    ("Sentralisasi", "Desentralisasi", ["Modernisasi", "Globalisasi", "Birokrasi", "Reformasi"]),
    ("Hemat", "Boros", ["Murah", "Rajin", "Cermat", "Sederhana"]),
    ("Ekspor", "Impor", ["Produksi", "Distribusi", "Konsumsi", "Investasi"]),
    ("Abstrak", "Konkret", ["Rumit", "Umum", "Singkat", "Samar"]),
    ("Mayoritas", "Minoritas", ["Populasi", "Kelompok", "Oposisi", "Dominasi"]),
    ("Progresif", "Konservatif", ["Radikal", "Dinamis", "Kreatif", "Modern"]),
    ("Defisit", "Surplus", ["Anggaran", "Utang", "Inflasi", "Pajak"]),
    ("Inflasi", "Deflasi", ["Resesi", "Stagnasi", "Devaluasi", "Investasi"]),
    ("Hulu", "Hilir", ["Muara", "Tepi", "Dasar", "Aliran"]),
    ("Rasional", "Irasional", ["Emosional", "Realistis", "Logis", "Praktis"]),
    ("Vertikal", "Horizontal", ["Diagonal", "Miring", "Melingkar", "Lurus"]),
    ("Fiktif", "Nyata", ["Lama", "Umum", "Sulit", "Baru"]),
    ("Transparan", "Tertutup", ["Jelas", "Murah", "Terang", "Formal"]),
]

ANALOGI = [
    ("Dokter : Rumah sakit", "Guru", "Sekolah", ["Murid", "Buku", "Kapur", "Ujian"]),
    ("Pisau : Memotong", "Pena", "Menulis", ["Kertas", "Tinta", "Membaca", "Meja"]),
    ("Kaki : Sepatu", "Kepala", "Topi", ["Rambut", "Leher", "Wajah", "Telinga"]),
    ("Panas : Dingin", "Terang", "Gelap", ["Lampu", "Siang", "Cahaya", "Hangat"]),
    ("Padi : Beras", "Gandum", "Tepung", ["Roti", "Sawah", "Petani", "Biji"]),
    ("Mata : Melihat", "Telinga", "Mendengar", ["Suara", "Kepala", "Anting", "Bicara"]),
    ("Petani : Sawah", "Nelayan", "Laut", ["Ikan", "Perahu", "Jala", "Pantai"]),
    ("Kapal : Pelabuhan", "Pesawat", "Bandara", ["Pilot", "Langit", "Tiket", "Awan"]),
    ("Halus : Kasar", "Cair", "Padat", ["Air", "Beku", "Licin", "Panas"]),
    ("Termometer : Suhu", "Barometer", "Tekanan udara", ["Angin", "Hujan", "Cuaca", "Awan"]),
    ("Jam : Waktu", "Meteran", "Panjang", ["Berat", "Tukang", "Kain", "Lebar"]),
    ("Lapar : Makan", "Haus", "Minum", ["Air", "Gelas", "Tidur", "Lelah"]),
    ("Hakim : Pengadilan", "Dokter", "Rumah sakit", ["Obat", "Perawat", "Pasien", "Suntikan"]),
    ("Sepeda : Mengayuh", "Perahu", "Mendayung", ["Berlayar", "Sungai", "Layar", "Berenang"]),
    ("Sendok : Makan", "Gunting", "Memotong", ["Kain", "Tajam", "Tangan", "Lipat"]),
    ("Pagi : Siang", "Senin", "Selasa", ["Minggu", "Jumat", "Hari", "Sabtu"]),
]


def sinonim(rng, indeks):
    kata, benar, salah = SINONIM[indeks % len(SINONIM)]
    return soal("Verbal: sinonim", f"Sinonim kata \"{kata}\" adalah ...", benar, salah, f"Dalam penggunaan baku, \"{kata}\" bermakna sama dengan \"{benar}\".", tampil=lambda teks: teks)


def antonim(rng, indeks):
    kata, benar, salah = ANTONIM[indeks % len(ANTONIM)]
    return soal("Verbal: antonim", f"Lawan kata (antonim) dari \"{kata}\" adalah ...", benar, salah, f"\"{kata}\" berlawanan makna dengan \"{benar}\".", tampil=lambda teks: teks)


def analogi(rng, indeks):
    pasangan, c, benar, salah = ANALOGI[indeks % len(ANALOGI)]
    kiri, kanan = pasangan.split(" : ")
    return soal("Verbal: analogi", f"{kiri} : {kanan} = {c} : ...", benar, salah, f"Hubungan {kiri} dengan {kanan} sama dengan hubungan {c} dengan {benar}.", tampil=lambda teks: teks)
