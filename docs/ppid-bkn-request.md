# Draf Permohonan Informasi Publik ke PPID BKN (Jalur C)

Tujuan: memperoleh data **agregat** jumlah formasi dan jumlah pelamar seleksi CASN 2024 per instansi dan jabatan, agar SICASN dapat menampilkan tingkat persaingan yang akurat dan resmi.

**Cara pakai**
1. Isi semua bagian bertanda `[...]` dengan identitas pemohon yang sebenarnya.
2. Verifikasi alamat, surel, dan formulir PPID BKN terbaru di situs resmi BKN (`bkn.go.id`) sebelum mengirim. Jangan memakai alamat yang belum dicek.
3. Kirim lewat jalur yang disediakan PPID BKN (tertulis langsung atau elektronik), lampirkan fotokopi identitas bila diminta.
4. Simpan bukti pengiriman dan nomor registrasi. Catat tenggat jawab.
5. Saat data diterima, impor lewat menu admin **Impor Statistik Pelamar** (format di bagian 3).

Dasar hukum yang lazim dirujuk: UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik. Batas waktu jawaban badan publik dan jalur keberatan sebaiknya dicek ulang pada teks undang-undang atau pedoman PPID BKN.

---

## 1. Surat permohonan

```
[Kota], [tanggal]

Kepada Yth.
Pejabat Pengelola Informasi dan Dokumentasi (PPID)
Badan Kepegawaian Negara
[alamat resmi PPID BKN — verifikasi di situs BKN]

Perihal: Permohonan Informasi Publik — Data Agregat Pelamar Seleksi CASN Tahun 2024

Dengan hormat,

Yang bertanda tangan di bawah ini:
  Nama                : [nama lengkap]
  Nomor identitas     : [NIK/KTP — sesuai ketentuan formulir PPID]
  Alamat              : [alamat]
  Nomor telepon/WA    : [nomor]
  Surel               : [surel]
  Pekerjaan/lembaga   : [pekerjaan atau nama lembaga/usaha]

Berdasarkan Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi
Publik, saya mengajukan permohonan informasi publik berikut:

1. Rincian informasi yang dibutuhkan
   Data agregat (bukan data pribadi pelamar) seleksi CPNS Tahun 2024
   (serta PPPK Tahun 2024 apabila tersedia) per formasi, berisi:
   a. Tahun dan jenis pengadaan (CPNS / PPPK)
   b. Nama instansi
   c. Kode dan nama jabatan
   d. Lokasi formasi/penempatan
   e. Jumlah formasi (kuota)
   f. Jumlah pelamar yang mendaftar dan/atau menyelesaikan pengiriman berkas (submit)
   g. Jumlah pelamar yang dinyatakan memenuhi syarat pada seleksi administrasi
   h. Tanggal data (snapshot) dan sumber data

2. Tujuan penggunaan informasi
   Untuk layanan informasi bagi calon pelamar ASN yang membantu memahami tingkat
   persaingan formasi pada tahun sebelumnya. Informasi akan ditampilkan sebagai data
   agregat dengan mencantumkan BKN sebagai sumber, dan tidak diklaim sebagai
   layanan resmi pemerintah.

3. Bentuk informasi yang diminta
   Salinan elektronik dalam format tabular (CSV atau Excel). Tidak memerlukan nama,
   NIK, atau data pribadi pelamar mana pun.

4. Cara memperoleh informasi
   [ ] Dikirim melalui surel: [surel]
   [ ] Diunduh dari tautan yang diberikan PPID

Apabila sebagian informasi tidak dapat diberikan, mohon disampaikan alasan
penolakannya beserta bagian informasi yang tetap dapat diberikan.

Demikian permohonan ini saya ajukan. Atas perhatian dan kerja samanya,
saya ucapkan terima kasih.

Hormat saya,

[tanda tangan]
[nama lengkap]
```

## 2. Versi singkat (untuk kolom formulir elektronik)

> Mohon data agregat seleksi CPNS 2024 (PPPK 2024 bila tersedia) per formasi (instansi, kode/nama jabatan, lokasi, jumlah formasi, jumlah pelamar mendaftar/submit, jumlah memenuhi syarat administrasi, tanggal data) dalam format CSV/Excel, tanpa data pribadi pelamar. Tujuan: layanan informasi bagi calon pelamar ASN dengan mencantumkan BKN sebagai sumber.

## 3. Format impor yang disiapkan SICASN

CSV UTF-8 (BOM, titik koma, tab, dan Windows-1252 juga diterima), baris pertama header. Unduh templatenya di menu admin **Statistik Pelamar**.

| Kolom | Wajib | Contoh |
|---|---|---|
| `tahun` | ya | `2024` |
| `jenis_pengadaan` | tidak (bawaan `cpns`) | `cpns` / `pppk_guru` / `pppk_teknis` / `pppk_nakes` |
| `cakupan` | ya | `nasional`, `instansi`, atau `jabatan` |
| `instansi` | ya bila cakupan instansi atau jabatan; kosong bila nasional | `Kementerian Agama` |
| `kode_jabatan` | tidak, hanya cakupan jabatan | `JF0000418-79` |
| `nama_jabatan` | ya bila cakupan jabatan | `Penata Layanan Operasional` |
| `lokasi` | tidak, hanya cakupan jabatan | `Kota Malang` |
| `jumlah_formasi` | salah satu kolom jumlah wajib terisi | `20772` |
| `jumlah_pelamar` | | `411194` |
| `jumlah_submit` | | `3572414` (tidak boleh melebihi pelamar) |
| `jumlah_lolos_administrasi` | | `1203` (tidak boleh melebihi pelamar) |
| `tanggal_snapshot` | ya | `2024-09-17` (tidak boleh di masa depan) |
| `status_angka` | tidak (bawaan `sementara`) | `sementara` / `akhir` |
| `tingkat_data` | tidak (bawaan `A`) | `A` / `B` / `C` |
| `keyakinan` | tidak (bawaan `turunan`) | `resmi` / `turunan` |
| `sumber_nama` | ya | `PPID BKN` |
| `sumber_url` | tidak | tautan http(s) |
| `catatan` | tidak | teks bebas, maks 255 karakter |

Untuk data dari jalur ini isi `tingkat_data=C`, `keyakinan=resmi`, dan `status_angka=akhir` bila angkanya final. Data C menggantikan A/B pada tampilan untuk kunci yang sama (cakupan per jabatan tersedia setelah Fase 5).

## 4. Bila ditolak atau tidak dijawab

- Simpan surat penolakan dan alasannya.
- Ajukan keberatan sesuai mekanisme KIP dan pedoman PPID BKN (cek ketentuan waktu dan atasan PPID di situs BKN).
- Sistem tetap berjalan dengan data tingkat A dan B. Tampilan "Belum ada data" untuk formasi tanpa statistik tidak berubah.
