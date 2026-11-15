import random

PASAL = [
    ("Pasal 1 ayat (1)", "negara kesatuan yang berbentuk republik"),
    ("Pasal 1 ayat (2)", "kedaulatan berada di tangan rakyat dan dilaksanakan menurut Undang-Undang Dasar"),
    ("Pasal 1 ayat (3)", "Indonesia sebagai negara hukum"),
    ("Pasal 2 ayat (1)", "susunan MPR yang terdiri atas anggota DPR dan anggota DPD"),
    ("Pasal 3 ayat (2)", "wewenang MPR melantik Presiden dan/atau Wakil Presiden"),
    ("Pasal 4 ayat (1)", "Presiden memegang kekuasaan pemerintahan menurut Undang-Undang Dasar"),
    ("Pasal 5 ayat (1)", "hak Presiden mengajukan rancangan undang-undang kepada DPR"),
    ("Pasal 6A ayat (1)", "pemilihan Presiden dan Wakil Presiden dalam satu pasangan secara langsung oleh rakyat"),
    ("Pasal 7", "masa jabatan Presiden dan Wakil Presiden lima tahun dan hanya dapat dipilih kembali untuk satu kali masa jabatan"),
    ("Pasal 10", "Presiden memegang kekuasaan tertinggi atas Angkatan Darat, Angkatan Laut, dan Angkatan Udara"),
    ("Pasal 11 ayat (1)", "Presiden dengan persetujuan DPR menyatakan perang, membuat perdamaian, dan perjanjian dengan negara lain"),
    ("Pasal 18 ayat (1)", "pembagian wilayah negara atas daerah provinsi yang dibagi atas kabupaten dan kota"),
    ("Pasal 20 ayat (1)", "DPR memegang kekuasaan membentuk undang-undang"),
    ("Pasal 22C ayat (1)", "anggota DPD dipilih dari setiap provinsi melalui pemilihan umum"),
    ("Pasal 22E ayat (1)", "pemilihan umum dilaksanakan langsung, umum, bebas, rahasia, jujur, dan adil setiap lima tahun sekali"),
    ("Pasal 23E ayat (1)", "pemeriksaan pengelolaan dan tanggung jawab keuangan negara oleh Badan Pemeriksa Keuangan"),
    ("Pasal 24 ayat (1)", "kekuasaan kehakiman sebagai kekuasaan yang merdeka"),
    ("Pasal 24A ayat (1)", "wewenang Mahkamah Agung menguji peraturan di bawah undang-undang terhadap undang-undang"),
    ("Pasal 24C ayat (1)", "wewenang Mahkamah Konstitusi menguji undang-undang terhadap Undang-Undang Dasar"),
    ("Pasal 25A", "Indonesia sebagai negara kepulauan yang berciri nusantara"),
    ("Pasal 27 ayat (1)", "kedudukan warga negara yang sama di dalam hukum dan pemerintahan"),
    ("Pasal 27 ayat (3)", "hak dan kewajiban warga negara ikut serta dalam upaya pembelaan negara"),
    ("Pasal 28", "kemerdekaan berserikat, berkumpul, dan mengeluarkan pikiran dengan lisan dan tulisan yang ditetapkan dengan undang-undang"),
    ("Pasal 28D ayat (3)", "hak warga negara memperoleh kesempatan yang sama dalam pemerintahan"),
    ("Pasal 28H ayat (1)", "hak hidup sejahtera lahir dan batin, bertempat tinggal, serta mendapatkan lingkungan hidup yang baik dan sehat"),
    ("Pasal 28I ayat (1)", "hak untuk hidup yang tidak dapat dikurangi dalam keadaan apa pun"),
    ("Pasal 29 ayat (2)", "jaminan negara atas kemerdekaan tiap penduduk memeluk agamanya dan beribadat menurut agamanya"),
    ("Pasal 30 ayat (1)", "hak dan kewajiban warga negara ikut serta dalam usaha pertahanan dan keamanan negara"),
    ("Pasal 31 ayat (1)", "hak setiap warga negara untuk mendapat pendidikan"),
    ("Pasal 31 ayat (4)", "prioritas anggaran pendidikan sekurang-kurangnya dua puluh persen dari APBN dan APBD"),
    ("Pasal 32 ayat (1)", "kewajiban negara memajukan kebudayaan nasional di tengah peradaban dunia"),
    ("Pasal 33 ayat (1)", "perekonomian disusun sebagai usaha bersama berdasar atas asas kekeluargaan"),
    ("Pasal 33 ayat (3)", "bumi, air, dan kekayaan alam dikuasai negara untuk sebesar-besar kemakmuran rakyat"),
    ("Pasal 34 ayat (1)", "fakir miskin dan anak-anak terlantar dipelihara oleh negara"),
    ("Pasal 35", "bendera negara Sang Merah Putih"),
    ("Pasal 36", "bahasa negara adalah bahasa Indonesia"),
    ("Pasal 36A", "lambang negara Garuda Pancasila dengan semboyan Bhinneka Tunggal Ika"),
    ("Pasal 36B", "lagu kebangsaan Indonesia Raya"),
    ("Pasal 37 ayat (5)", "bentuk Negara Kesatuan Republik Indonesia yang tidak dapat dilakukan perubahan"),
]

PERISTIWA = [
    ("Sumpah Pemuda diikrarkan", "28 Oktober 1928", "Sumpah Pemuda"),
    ("Organisasi Budi Utomo berdiri", "20 Mei 1908", "hari berdirinya Budi Utomo yang diperingati sebagai Hari Kebangkitan Nasional"),
    ("Proklamasi kemerdekaan Indonesia dibacakan", "17 Agustus 1945", "Proklamasi Kemerdekaan Indonesia"),
    ("Pertempuran Surabaya yang diperingati sebagai Hari Pahlawan terjadi", "10 November 1945", "Hari Pahlawan"),
    ("Sidang PPKI yang mengesahkan UUD 1945 dilaksanakan", "18 Agustus 1945", "pengesahan UUD 1945 dan pemilihan Presiden serta Wakil Presiden pertama oleh PPKI"),
    ("Peristiwa Rengasdengklok terjadi", "16 Agustus 1945", "peristiwa Rengasdengklok"),
    ("Piagam Jakarta ditandatangani", "22 Juni 1945", "penandatanganan Piagam Jakarta oleh Panitia Sembilan"),
    ("Hari Lahir Pancasila diperingati", "1 Juni", "Hari Lahir Pancasila"),
    ("Belanda mengakui kedaulatan Republik Indonesia", "27 Desember 1949", "pengakuan kedaulatan Republik Indonesia oleh Belanda"),
    ("Dekrit Presiden yang menyatakan kembali ke UUD 1945 dikeluarkan", "5 Juli 1959", "Dekrit Presiden untuk kembali ke UUD 1945"),
    ("Surat Perintah Sebelas Maret (Supersemar) dikeluarkan", "11 Maret 1966", "Surat Perintah Sebelas Maret (Supersemar)"),
    ("Konferensi Asia-Afrika di Bandung dibuka", "18 April 1955", "pembukaan Konferensi Asia-Afrika di Bandung"),
    ("Deklarasi Djuanda dikeluarkan", "13 Desember 1957", "Deklarasi Djuanda tentang wilayah perairan Indonesia"),
    ("ASEAN didirikan melalui Deklarasi Bangkok", "8 Agustus 1967", "berdirinya ASEAN melalui Deklarasi Bangkok"),
    ("Hari Pendidikan Nasional diperingati", "2 Mei", "Hari Pendidikan Nasional"),
    ("Hari Kartini diperingati", "21 April", "Hari Kartini"),
    ("Hari Kesaktian Pancasila diperingati", "1 Oktober", "Hari Kesaktian Pancasila"),
    ("Hari Bela Negara diperingati", "19 Desember", "Hari Bela Negara"),
    ("Hari Ibu diperingati", "22 Desember", "Hari Ibu"),
    ("Hari Santri diperingati", "22 Oktober", "Hari Santri"),
    ("Hari Anti Korupsi Sedunia diperingati", "9 Desember", "Hari Anti Korupsi Sedunia"),
    ("Hari Pramuka diperingati", "14 Agustus", "Hari Pramuka"),
    ("Hari Konstitusi diperingati", "18 Agustus", "Hari Konstitusi"),
    ("Peristiwa Bandung Lautan Api terjadi", "24 Maret 1946", "peristiwa Bandung Lautan Api"),
    ("Serangan Umum di Yogyakarta dilancarkan", "1 Maret 1949", "Serangan Umum 1 Maret di Yogyakarta"),
]

TOKOH = [
    ("Soekarno", "Proklamator dan Presiden pertama Republik Indonesia"),
    ("Mohammad Hatta", "Proklamator dan Wakil Presiden pertama Republik Indonesia"),
    ("Ki Hajar Dewantara", "Bapak Pendidikan Nasional dan pendiri Taman Siswa"),
    ("R.A. Kartini", "Pelopor emansipasi perempuan Indonesia"),
    ("dr. Sutomo", "Pendiri Budi Utomo"),
    ("H.O.S. Tjokroaminoto", "Pemimpin Sarekat Islam"),
    ("K.H. Ahmad Dahlan", "Pendiri Muhammadiyah"),
    ("K.H. Hasyim Asy'ari", "Pendiri Nahdlatul Ulama"),
    ("W.R. Supratman", "Pencipta lagu Indonesia Raya"),
    ("Jenderal Soedirman", "Panglima Besar Angkatan Perang Republik Indonesia"),
    ("Bung Tomo", "Penggerak semangat arek-arek Surabaya melalui siaran radio"),
    ("Pangeran Diponegoro", "Pemimpin Perang Jawa (1825-1830)"),
    ("Sultan Hasanuddin", "Raja Gowa yang melawan VOC dan dijuluki Ayam Jantan dari Timur"),
    ("Pattimura", "Pemimpin perlawanan rakyat Maluku tahun 1817"),
    ("Tuanku Imam Bonjol", "Pemimpin Perang Padri"),
    ("Cut Nyak Dien", "Pejuang perempuan Aceh dalam perang melawan Belanda"),
    ("Dewi Sartika", "Pendiri sekolah untuk perempuan di Bandung"),
    ("Moh. Yamin", "Perumus teks Sumpah Pemuda"),
    ("Sutan Sjahrir", "Perdana Menteri pertama Republik Indonesia"),
    ("I Gusti Ngurah Rai", "Pemimpin Pertempuran Puputan Margarana di Bali"),
    ("Sultan Agung", "Raja Mataram yang menyerang VOC di Batavia"),
    ("Pangeran Antasari", "Pemimpin Perang Banjar di Kalimantan Selatan"),
    ("Fatmawati", "Penjahit bendera pusaka Sang Saka Merah Putih"),
]

SILA = [
    ("Ketuhanan Yang Maha Esa", "bintang", "pertama"),
    ("Kemanusiaan yang adil dan beradab", "rantai", "kedua"),
    ("Persatuan Indonesia", "pohon beringin", "ketiga"),
    ("Kerakyatan yang dipimpin oleh hikmat kebijaksanaan dalam permusyawaratan/perwakilan", "kepala banteng", "keempat"),
    ("Keadilan sosial bagi seluruh rakyat Indonesia", "padi dan kapas", "kelima"),
]

LEMBAGA = [
    ("Majelis Permusyawaratan Rakyat", "mengubah dan menetapkan Undang-Undang Dasar", "UUD 1945 Pasal 3 ayat (1)"),
    ("Dewan Perwakilan Rakyat", "memegang kekuasaan membentuk undang-undang", "UUD 1945 Pasal 20 ayat (1)"),
    ("Dewan Perwakilan Daerah", "mengajukan rancangan undang-undang yang berkaitan dengan otonomi daerah", "UUD 1945 Pasal 22D ayat (1)"),
    ("Presiden", "memegang kekuasaan pemerintahan menurut Undang-Undang Dasar", "UUD 1945 Pasal 4 ayat (1)"),
    ("Mahkamah Agung", "mengadili pada tingkat kasasi dan menguji peraturan di bawah undang-undang terhadap undang-undang", "UUD 1945 Pasal 24A ayat (1)"),
    ("Mahkamah Konstitusi", "menguji undang-undang terhadap Undang-Undang Dasar dan memutus sengketa hasil pemilihan umum", "UUD 1945 Pasal 24C ayat (1)"),
    ("Komisi Yudisial", "mengusulkan pengangkatan hakim agung serta menjaga kehormatan dan keluhuran martabat hakim", "UUD 1945 Pasal 24B ayat (1)"),
    ("Badan Pemeriksa Keuangan", "memeriksa pengelolaan dan tanggung jawab keuangan negara", "UUD 1945 Pasal 23E ayat (1)"),
    ("Komisi Pemilihan Umum", "menyelenggarakan pemilihan umum yang bersifat nasional, tetap, dan mandiri", "UUD 1945 Pasal 22E ayat (5)"),
    ("Ombudsman Republik Indonesia", "mengawasi penyelenggaraan pelayanan publik dan menerima laporan maladministrasi", "UU 37/2008 tentang Ombudsman Republik Indonesia"),
    ("Komisi Pemberantasan Korupsi", "mencegah dan memberantas tindak pidana korupsi", "UU 30/2002 tentang KPK"),
    ("Bank Indonesia", "menjaga kestabilan nilai rupiah", "UU tentang Bank Indonesia"),
    ("Tentara Nasional Indonesia", "mempertahankan, melindungi, dan memelihara keutuhan dan kedaulatan negara", "UUD 1945 Pasal 30 ayat (3)"),
    ("Kepolisian Negara Republik Indonesia", "menjaga keamanan dan ketertiban masyarakat serta menegakkan hukum", "UUD 1945 Pasal 30 ayat (4)"),
]

BAKU = [
    ("Analisis", ["Analisa", "Analysis", "Analise", "Analisi"]),
    ("Apotek", ["Apotik", "Apoteek", "Apothek", "Apotèk"]),
    ("Risiko", ["Resiko", "Risico", "Riziko", "Resico"]),
    ("Praktik", ["Praktek", "Practik", "Praktikk", "Prakték"]),
    ("Izin", ["Ijin", "Ijzin", "Idzin", "Isin"]),
    ("Nasihat", ["Nasehat", "Nasiat", "Nasihad", "Nasehad"]),
    ("Silakan", ["Silahkan", "Sylakan", "Silaken", "Silahken"]),
    ("Hakikat", ["Hakekat", "Hakikad", "Hakeket", "Hakikt"]),
    ("Kuitansi", ["Kwitansi", "Kuitanzi", "Quitansi", "Kuitansy"]),
    ("Aktivitas", ["Aktifitas", "Aktivitass", "Aktipitas", "Aktivites"]),
    ("Kualitas", ["Kwalitas", "Kuwalitas", "Kualites", "Kwalites"]),
    ("Objek", ["Obyek", "Objeck", "Obejk", "Obyeck"]),
    ("Subjek", ["Subyek", "Subjeck", "Subejk", "Subyeck"]),
    ("Konsekuen", ["Konsekwen", "Konsekuent", "Konsekwent", "Konsekuan"]),
    ("Teknik", ["Tehnik", "Tekhnik", "Tecknik", "Teknick"]),
    ("Metode", ["Metoda", "Methode", "Metodhe", "Metodde"]),
    ("Jadwal", ["Jadual", "Jadwall", "Jadwhal", "Jadwol"]),
    ("Standar", ["Standard", "Standhar", "Setandar", "Standare"]),
    ("Sistematis", ["Sistimatis", "Sistemetis", "Systematis", "Sistematik"]),
    ("Merek", ["Merk", "Mereck", "Mherek", "Mereg"]),
    ("Atlet", ["Atlit", "Atlett", "Atleth", "Athlet"]),
    ("Telepon", ["Telpon", "Telefon", "Telepone", "Teleponn"]),
    ("Frekuensi", ["Frekwensi", "Frekuensy", "Frekuenzi", "Frequensi"]),
    ("Ijazah", ["Ijasah", "Ijazaah", "Ijasa", "Izasah"]),
    ("Asas", ["Azas", "Asaz", "Assas", "Ashas"]),
    ("Lembap", ["Lembab", "Lembaf", "Lembapp", "Lembhap"]),
    ("Terampil", ["Trampil", "Terampill", "Therampil", "Terampel"]),
    ("Kategori", ["Katagori", "Kategorie", "Katergori", "Kategoryi"]),
    ("Hierarki", ["Hirarki", "Hierarkhi", "Hierarkie", "Hierakri"]),
    ("Teknologi", ["Tehnologi", "Teknologie", "Tecnologi", "Teknolgi"]),
    ("Ekstrem", ["Ekstrim", "Extrem", "Ekstrem-", "Eksterm"]),
    ("Andal", ["Handal", "Andall", "Hendal", "Andhal"]),
    ("Napas", ["Nafas", "Napass", "Nappas", "Nahpas"]),
    ("Provinsi", ["Propinsi", "Provensi", "Provincy", "Provinci"]),
    ("Karismatik", ["Kharismatik", "Karizmatik", "Karismatic", "Karismatis"]),
    ("Cenderamata", ["Cinderamata", "Cenderamota", "Cenderamatha", "Cinderamota"]),
    ("Sinkron", ["Singkron", "Sinkhron", "Sinkronn", "Sincron"]),
    ("Komplet", ["Komplit", "Komplett", "Kompleet", "Komplith"]),
]


def hari_bulan(tanggal):
    bagian = tanggal.split(" ")
    return " ".join(bagian[:2])


def _lainnya(rng, semua, kecuali, jumlah=4):
    pilihan = [x for x in semua if x != kecuali]
    rng.shuffle(pilihan)
    return pilihan[:jumlah]


def bangun_semua():
    rng = random.Random(1717)
    hasil = []
    isi_semua = [isi for _, isi in PASAL]
    pasal_semua = [pasal for pasal, _ in PASAL]

    for pasal, isi in PASAL:
        hasil.append(("Pilar negara", f"Ketentuan mengenai {isi} diatur dalam UUD 1945 ...", pasal, _lainnya(rng, pasal_semua, pasal), f"Ketentuan tersebut terdapat pada {pasal} UUD 1945.", f"UUD 1945 {pasal}"))
        hasil.append(("Pilar negara", f"{pasal} UUD 1945 mengatur tentang ...", isi[0].upper() + isi[1:], [x[0].upper() + x[1:] for x in _lainnya(rng, isi_semua, isi)], f"{pasal} UUD 1945 mengatur {isi}.", f"UUD 1945 {pasal}"))

    tanggal_semua = [t for _, t, _ in PERISTIWA]
    for maju, tanggal, mundur in PERISTIWA:
        if sum(1 for _, t, _ in PERISTIWA if t == tanggal) != 1:
            raise ValueError(f"tanggal kembar: {tanggal}")
        tanggal_lain = [x for x in tanggal_semua if hari_bulan(x) != hari_bulan(tanggal)]
        hasil.append(("Nasionalisme", f"{maju} pada tanggal ...", tanggal, _lainnya(rng, tanggal_lain, tanggal), f"{maju} pada {tanggal}.", "Sejarah nasional Indonesia"))
        label_lain = [m for _, tgl, m in PERISTIWA if hari_bulan(tgl) != hari_bulan(tanggal)]
        hasil.append(("Nasionalisme", f"Tanggal {tanggal} dikenang sebagai ...", mundur[0].upper() + mundur[1:], [x[0].upper() + x[1:] for x in _lainnya(rng, label_lain, mundur)], f"Tanggal {tanggal} dikenang sebagai {mundur}.", "Sejarah nasional Indonesia"))

    peran_semua = [peran for _, peran in TOKOH]
    nama_semua = [nama for nama, _ in TOKOH]
    for nama, peran in TOKOH:
        hasil.append(("Nasionalisme", f"{nama} dikenal dalam sejarah Indonesia sebagai ...", peran, _lainnya(rng, peran_semua, peran), f"{nama} dikenal sebagai {peran[0].lower() + peran[1:]}.", "Sejarah nasional Indonesia"))
        hasil.append(("Nasionalisme", f"Tokoh yang dikenal sebagai {peran[0].lower() + peran[1:]} adalah ...", nama, _lainnya(rng, nama_semua, nama), f"{nama} adalah {peran[0].lower() + peran[1:]}.", "Sejarah nasional Indonesia"))

    bunyi_semua = [b for b, _, _ in SILA]
    lambang_semua = [l for _, l, _ in SILA]
    urutan_semua = [u for _, _, u in SILA]
    for bunyi, lambang, urut in SILA:
        hasil.append(("Pilar negara", f"Lambang {lambang} pada perisai Garuda Pancasila menggambarkan sila ...", bunyi, _lainnya(rng, bunyi_semua, bunyi), f"{lambang.capitalize()} melambangkan sila {urut}: {bunyi}.", "UU 24/2009 tentang Bendera, Bahasa, dan Lambang Negara, serta Lagu Kebangsaan"))
        hasil.append(("Pilar negara", f"Sila \"{bunyi}\" dilambangkan dengan ...", lambang, _lainnya(rng, lambang_semua, lambang), f"Sila {urut} dilambangkan dengan {lambang}.", "UU 24/2009 tentang Bendera, Bahasa, dan Lambang Negara, serta Lagu Kebangsaan"))
        hasil.append(("Pilar negara", f"Sila \"{bunyi}\" adalah sila ...", urut.capitalize(), [u.capitalize() for u in _lainnya(rng, urutan_semua, urut, 3)] + ["Keenam"], f"Pancasila terdiri atas lima sila; \"{bunyi}\" adalah sila {urut}.", "Pembukaan UUD 1945 alinea keempat"))

    tugas_semua = [t for _, t, _ in LEMBAGA]
    nama_lembaga = [n for n, _, _ in LEMBAGA]
    for nama, tugas, sumber in LEMBAGA:
        hasil.append(("Pilar negara", f"Lembaga negara yang bertugas {tugas} adalah ...", nama, _lainnya(rng, nama_lembaga, nama), f"{nama} bertugas {tugas}.", sumber))
        hasil.append(("Pilar negara", f"Salah satu tugas dan wewenang {nama} adalah ...", tugas[0].upper() + tugas[1:], [x[0].upper() + x[1:] for x in _lainnya(rng, tugas_semua, tugas)], f"{nama} bertugas {tugas}.", sumber))

    for baku, variasi in BAKU:
        hasil.append(("Bahasa Indonesia", f"Bentuk baku dari kata \"{variasi[0]}\" adalah ...", baku, variasi[1:] + [variasi[0]], f"Bentuk baku menurut KBBI adalah \"{baku.lower()}\", bukan \"{variasi[0].lower()}\".", "Kamus Besar Bahasa Indonesia"))

    return hasil
