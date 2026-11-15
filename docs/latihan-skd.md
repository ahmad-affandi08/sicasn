# Latihan SKD: bank soal, sumber, dan batasannya

Fitur ini memungkinkan member berlatih Tes Kompetensi Dasar (TWK, TIU, TKP) dengan format mirip CAT. Bank soal **dibundel di repositori** (`database/seeders/data/soal/*.json`) dan dimuat otomatis oleh `php artisan sicasn:install`, sehingga pemilik tidak perlu mengisi soal apa pun setelah produksi. Setelah pembaruan kode, `php artisan sicasn:muat-soal` memperbarui bank (aman diulang: hanya soal yang berubah yang ditulis).

## Mengapa soal dibuat sendiri

Tidak ada bank soal resmi yang boleh disalin. BKN hanya menyediakan simulasi CAT untuk mengenalkan sistem, bukan soal latihan, dan soal yang beredar di situs bimbel, blog, atau media umumnya berasal dari buku penerbit sehingga berhak cipta dan kunci jawabannya sering tidak teruji. Produk ini berbayar, jadi soal harus bisa dipertanggungjawabkan. Karena itu setiap soal di bank ini **dibuat asli untuk SICASN** dan memuat sumber, pembahasan, dan lisensinya (`Soal asli SICASN`).

Jangan menambahkan soal dari situs lain tanpa izin tertulis atau lisensi yang jelas.

## Isi bank saat ini

| Tes | Jumlah | Cara dibuat | Cara kunci dijamin |
|---|---|---|---|
| TIU | 1.435 | Dibangkitkan program (`tools/soal/bangkitkan_tiu.py`): deret angka dan huruf, hitungan, persentase, bunga tunggal, perbandingan, skala peta, soal cerita (kecepatan, pekerjaan, umur), aljabar, geometri, interpretasi data, kombinatorika, peluang, himpunan, kalender, sudut jam, satuan, penalaran logis dan analitis, plus daftar sinonim, antonim, dan analogi | Kunci dihitung oleh kode, lalu **diaudit ulang secara independen**: `tools/soal/audit_deret.py` (225 deret, tiap deret cocok tepat satu aturan) dan `tools/soal/audit_hitungan.py` (933 soal hitungan dihitung ulang dari teks soal dengan rumus terpisah). Soal urutan dibuktikan unik dengan mencoba semua permutasi. Soal verbal ditulis tangan |
| TWK | 330 | Ditulis tangan (`data_twk.py`, 77 soal) dan dibentuk dari **tabel fakta** (`twk_fakta.py`, 253 soal): isi 39 pasal UUD 1945, 24 tanggal peristiwa dan hari nasional, 23 tokoh pergerakan, hubungan lima sila dengan lambangnya, 14 lembaga negara dengan tugasnya, dan 38 kata baku | Tiap soal memetakan satu baris tabel fakta ke bentuk pertanyaan dan pengecoh diambil dari baris lain; pengecoh yang berbagi hari dan bulan dengan jawaban dibuang agar tidak ada dua jawaban benar. Fakta di tabel **belum dicocokkan mesin dengan teks sumber** (lihat batasan) |
| TKP | 97 | Ditulis tangan (`data_tkp.py` dan `data_tkp_tambahan.py`) dalam enam aspek: pelayanan publik, jejaring kerja, sosial budaya, TIK, profesionalisme, anti radikalisme | Skor 1 sampai 5 disusun mengikuti nilai dasar ASN (BerAKHLAK) dan prinsip pelayanan publik. **Penilaian TKP pada dasarnya subjektif** |

Menjalankan ulang pembangkit menghasilkan berkas yang sama persis (benih acak tetap):

```
cd tools/soal
python3 bangkitkan_tiu.py
python3 bangkitkan_twk_tkp.py
python3 audit_deret.py
python3 audit_hitungan.py
```

## Aturan data soal

Setiap soal wajib: kategori (`twk`/`tiu`/`tkp`), subkategori, pertanyaan, pembahasan, sumber, lisensi, dan tepat lima opsi A sampai E. TWK dan TIU memiliki tepat satu kunci (skor 5), sisanya 0. TKP memberi skor 1, 2, 3, 4, 5 masing-masing sekali. Pertanyaan kembar dalam satu berkas ditolak. Aturan ini ditegakkan oleh `SoalBaru` dan diuji di `tests/Unit/SoalBaruTest.php` serta `tests/Feature/BankSoalBawaanTest.php`.

Pemuatan ulang bank bersifat sinkron penuh: soal yang tidak lagi ada di berkas dinonaktifkan (tidak dihapus, supaya riwayat latihan tetap utuh), soal yang berubah diperbarui tanpa mengganti id opsi sehingga jawaban lama tetap tersambung.

## Perilaku latihan

- Mode: simulasi lengkap (30 TWK, 35 TIU, 45 TKP, 100 menit, urutan TWK lalu TIU lalu TKP seperti CAT) dan latihan per tes (20 soal, 90 detik per soal). Bila bank lebih kecil dari komposisi resmi, jumlah soal dan durasi disesuaikan proporsional.
- Soal dipilih acak dan menghindari soal dari tiga percobaan terakhir pengguna selama masih ada soal lain.
- Waktu dikendalikan server. Jawaban disimpan satu per satu saat dipilih (toleransi 5 detik setelah batas), diulang sekali bila jaringan gagal. Latihan yang waktunya habis diselesaikan otomatis dan jawaban yang sudah tersimpan dinilai.
- Kunci jawaban dan pembahasan **tidak dikirim ke browser** sebelum latihan selesai.
- Skor: TWK dan TIU 5 per jawaban benar tanpa pengurangan, TKP sesuai skor opsi.
- Ambang lolos awal: TWK 65, TIU 80, TKP 166 (skor maksimum 150, 175, dan 225), menurut pemberitaan tentang Kepmenpan RB 321/2024. **Angka ini belum dicocokkan dengan teks keputusan** dan berubah tiap tahun seleksi; dapat diubah lewat pengaturan grup `latihan` (`passing_twk`, `passing_tiu`, `passing_tkp`). Pada latihan yang soalnya lebih sedikit, ambang dihitung proporsional ("ambang setara"). Pernyataan "memenuhi ambang" hanya dibuat untuk simulasi.
- Pantauan privasi: riwayat dan hasil hanya dapat dibuka pemiliknya.

## Batasan yang perlu diketahui

1. **TWK belum diverifikasi mesin.** Rencana awal mencocokkan kunci dengan teks UUD 1945 dari Wikisource, tetapi host itu menjawab 429 (batas laju) dari sandbox dan tidak ditembus. Soal TWK hanya memakai fakta yang sangat baku, tetapi sebaiknya ditinjau pengajar atau dicocokkan dengan teks resmi sebelum dipasarkan sebagai materi belajar.
2. **TKP subjektif.** Skor 1 sampai 5 bukan kebenaran objektif. Tinjauan oleh praktisi seleksi ASN akan menaikkan mutu.
3. **Tidak ada soal gambar atau figural.** Format teks saja; TIU figural (pola gambar) belum didukung.
4. **Jumlah soal terbatas.** 1.862 soal cukup untuk belasan simulasi tanpa pengulangan penuh, tetapi TKP (97 soal) paling cepat berulang; pengguna aktif akan mengulang soal TKP lebih dulu. Menambah soal berarti menambah berkas JSON atau generator lalu menjalankan `sicasn:muat-soal`.
5. **Ambang bukan prediksi.** Skor latihan tidak menjamin hasil ujian sebenarnya; halaman hasil menyatakan hal itu.
6. Admin belum punya halaman untuk mengubah ambang atau mengimpor soal; keduanya lewat berkas dan pengaturan basis data.
