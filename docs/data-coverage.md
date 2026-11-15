# Cakupan Data Tingkat B (per jabatan)

Diperbarui: 16 Nov 2026. Dokumen ini mencatat jujur apa yang sudah dicoba untuk mengumpulkan jumlah peserta lulus seleksi administrasi CPNS 2024 per jabatan, dan hasilnya.

**Ringkasan: belum ada satu pun data per jabatan nyata yang dimuat.** Instansi terbesar tidak dapat diambil dari lingkungan pengembangan, dan PDF yang terjangkau tidak memuat daftar lengkap per jabatan. Alur lengkapnya (impor, pencocokan, tampilan, alat agregasi) sudah dibangun dan diuji dengan data sintetis; data nyata dapat dimasukkan lewat jalur di bawah.

## Hasil percobaan per instansi

| Instansi | Sumber yang dicoba | Hasil | Keputusan |
|---|---|---|---|
| Kemenkumham (volume terbesar, 567.552 pelamar) | `casn.kemenkumham.go.id/assets/upload2024/...` | Host sudah diizinkan, tetapi gateway tidak bisa menyambung (HTTP 502 berulang) | Belum diambil. Coba lagi dari mesin lain atau di lain waktu |
| Kemenag (411.194 pelamar) | Hasil dimuat di `kemenag.go.id`/`casn.kemenag.go.id`; URL PDF langsung tidak ditemukan | Host belum diizinkan | Belum dicoba |
| Kemenkes | `casn.kemkes.go.id/documents24/cpns/...Pasca_Sanggah...pdf` | 404, berkas sudah tidak ada | Tidak tersedia |
| BPS | `casn.bps.go.id/assets/docs/B-15-...pdf` | 403 dari perlindungan situs | **Tidak ditembus** (prinsip 4) |
| Setjen MPR | `setjen.mpr.go.id/pengumuman/...pdf` | 403 dari perlindungan situs | **Tidak ditembus** (prinsip 4) |
| Bawaslu | `bawaslu.go.id/sites/default/files/pengumuman/20240919 - PENGUMUMAN HASIL SELEKSI ADMINISTRASI PRASANGGAH.pdf` | Terjangkau, 2 halaman. Hanya memuat total: 22.847 pelamar submit, 19.948 memenuhi syarat (pra-sanggah, 19 Sep 2024). Lampiran nama terpisah dan tidak ditemukan | Tidak ada data per jabatan. Total tidak dimasukkan ke seed karena jumlah formasi tidak tercantum dan angkanya pra-sanggah |
| Kemensetneg | `cdn.setneg.go.id/_multimedia/document/20240929/3107P-05Pansel-CPNS092024_TTE.pdf` | Terjangkau, 6 halaman, tetapi hanya daftar sanggahan yang diterima, bukan daftar lulus lengkap | Tidak dipakai |
| Kemhan | `www.kemhan.go.id/ropeg/wp-content/uploads/2024/09/PENGUMUMAN-SELEKSI-ADM-CPNS-KEMHAN-2024_PASCA-SANGGAH.pdf` | Terjangkau (setelah pengalihan 301), 3 halaman berisi pengumuman saja tanpa lampiran | Tidak ada data per jabatan |

Semua berkas mentah yang diunduh sudah dihapus. Tidak ada PDF, nama, atau nomor registrasi peserta di repo atau database.

## Cara memasukkan data nyata

1. **Tingkat C (disarankan, resmi):** kirim permohonan PPID BKN (`docs/ppid-bkn-request.md`), lalu impor CSV hasilnya lewat menu admin **Statistik Pelamar** dengan `cakupan=jabatan`, `tingkat_data=C`, `keyakinan=resmi`.
2. **Tingkat B dari lampiran PDF yang Anda unduh sendiri:**
   ```
   pdftotext -layout lampiran.pdf lampiran.txt
   php artisan sicasn:agregat-lampiran lampiran.txt \
     --pola='/^\s*\d+\s+\S+\s+.+?\s{2,}(?<jabatan>.+?)\s{2,}(?<lokasi>Kab\..+|Kota\s.+)\s+MS\s*$/u' \
     --instansi="Nama Instansi" --tanggal=2024-09-19 --sumber="Pengumuman Hasil Seleksi Administrasi ..." \
     --url=https://... --total-resmi=19948 --keluaran=agregat.csv
   ```
   Pola regex berbeda untuk tiap instansi; grup bernama `jabatan` wajib, `lokasi` opsional. `--total-resmi` adalah angka yang tercantum di pengumuman: bila hitungan tidak sama, perintah **gagal dan tidak menulis berkas**, sehingga salah pola tidak bisa lolos diam-diam. Perintah hanya mencetak jumlah, tidak pernah isi baris, dan CSV hasilnya hanya berisi hitungan per jabatan dan lokasi. Hapus berkas teks dan PDF mentah setelahnya, lalu impor CSV agregat lewat menu admin.
3. Setiap instansi yang berhasil dimuat dicatat di tabel berikut (kosong saat ini).

| Instansi | Sumber (URL) | Tanggal data | Jabatan tercakup | Total peserta | Tingkat |
|---|---|---|---|---|---|
| (belum ada) | | | | | |

## Pencocokan ke formasi 2026

Aturan di CLAUDE.md §11.4. Data per jabatan hanya dipakai bila instansi sama dan nama jabatan ternormalisasi (huruf besar-kecil dan spasi diabaikan) sama persis; nama yang mirip tidak dicocokkan. Bila data jabatan punya lokasi, lokasinya harus sama dengan lokasi formasi. Bila tidak cocok, tampilan kembali ke rata-rata instansi (tingkat A).
