# Panduan Desain SICASN

Arah: **tinta dan kertas**. Antarmuka seperti lembar kerja yang rapi: struktur dari tipografi dan garis tipis, bukan dari kotak, bayangan, dan dekorasi. Dokumen ini adalah rujukan agar fase berikutnya tidak kembali ke pola template.

## Token (di `resources/css/app.css`)

| Token | Terang | Gelap | Pakai untuk |
|---|---|---|---|
| `latar` | `#fafaf8` | `#121311` | Latar halaman |
| `kartu` | `#ffffff` | `#1a1b19` | Panel, tabel, input |
| `permukaan` | `#f3f2ee` | `#1f201d` | Sidebar admin, kepala tabel, band bagian |
| `teks` / `redup` | `#1c1b18` / `#66625a` | `#ecebe6` / `#a29e94` | Teks utama dan sekunder (kontras ≥ 4,5:1) |
| `garis` | `#e5e2db` | `#2e2f2b` | Pemisah dan border kartu |
| `garis-input` | `#8f8b81` | `#6f6d65` | Batas kontrol (kontras ≥ 3:1 untuk WCAG 1.4.11) |
| `primer` | `#0b5d53` | `#4fc3af` | Aksi utama, tautan, penanda aktif |
| `sukses`, `peringatan`, `bahaya`, `info` | lihat CSS | lihat CSS | **Hanya status** (lencana persaingan, galat, hasil) |

Radius: `0.625rem` untuk panel, `0.5rem` untuk kontrol, `0.375rem` untuk lencana. Tanpa bayangan kecuali dialog dan laci.

## Tipografi

Roboto saja. Judul halaman 24–28 px berbobot 500; judul bagian 24–30 px; badan 14–16 px. `font-variant-numeric: tabular-nums` aktif global agar angka sejajar. Jangan memakai huruf kapital semua, jarak huruf lebar, atau bobot 700 kecuali wordmark.

## Kontrol dan sentuh

Tinggi kontrol 44 px di bawah `md` (target sentuh) dan 40 px mulai `md`. Teks input 16 px di HP agar tidak memicu zoom iOS, 14 px di desktop. Ikon selalu menyertai teks atau punya `aria-label`.

## Pola

- **Halaman depan**: kiri-rata, berbasis bagian berbaris (angka 01/02/03, garis tipis), satu ilustrasi produk yang jujur (tampilan tabel dengan angka disamarkan), bukan blob dekoratif. Bagian "Angka yang bisa ditelusuri" menjelaskan cara membaca skala.
- **Daftar data**: tabel padat di layar lebar, daftar berbaris di HP. Satu wadah ber-border dengan pemisah `divide-y`, bukan deretan kartu terpisah.
- **Angka ringkasan**: `dl` dalam satu strip ber-border dengan pemisah vertikal, label kecil di atas angka besar, sumber satu baris di bawahnya.
- **Catatan sumber/disclaimer**: komponen `Catatan` (garis kiri, teks redup, satu paragraf; versi ringkas di HP). Kotak berwarna hanya untuk galat nyata atau formasi yang ditarik.
- **Persaingan**: lencana (ikon + teks) dan `MeterRasio`. Skala tiga segmen sama lebar; penanda bergeser menurut rasio, dengan segitiga kecil bila angka sementara (batas bawah). Legenda skala dengan ambang aktif ditampilkan di atas daftar.
- **Navigasi member**: tautan teks dengan garis bawah aktif di desktop; bilah bawah tiga tujuan di HP. **Admin**: sidebar `permukaan` dengan penanda aktif berupa panel putih dan garis kiri.
- **Dialog**: tetap modal di tengah layar, lebih ringkas (judul 18 px, pesan 14 px).

## Daftar periksa sebelum menambah halaman baru

1. Adakah kartu di dalam kartu atau ikon di kotak berwarna? Hapus.
2. Apakah informasi utama terlihat tanpa menggulir melewati banner atau penjelasan panjang?
3. Apakah angka punya sumber, tanggal, dan tingkat data?
4. Apakah warna semantik dipakai hanya untuk status?
5. Uji di 390, 820, dan 1366 px, terang dan gelap, tanpa scroll horizontal.
