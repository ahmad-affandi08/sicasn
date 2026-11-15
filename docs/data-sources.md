# Sumber Data SICASN — Hasil Riset

Diperbarui: 15 Nov 2026. Dokumen ini mencatat apa yang **sudah diperiksa langsung** dan apa yang **hanya ringkasan hasil pencarian** (perlu dibuka dan diverifikasi agent sebelum dipakai).

## 1. Formasi saat ini: API publik BKN (diverifikasi langsung)

| Item | Nilai |
|---|---|
| Endpoint | `GET https://api-rekrutmen.bkn.go.id/portal/api/spf` |
| Autentikasi | Tidak ada |
| Dipakai oleh | Halaman `https://sscasn.bkn.go.id/daftar-formasi/` (komponen Astro `DaftarFormasi`) |
| Ukuran | ±4,5 MB, satu respons, tanpa paginasi |
| Cache di portal | 1 jam (konstanta `G = 36e5` di kode portal) |
| Perlindungan | Ada WAF F5 di depan portal. Jangan menembus. |

### Skema respons

```
{ status: 200, error: false, message: "success", data: [ Formasi, ... ] }
```

Field `Formasi`:

| Field | Tipe | Catatan |
|---|---|---|
| `id` | uuid | unik per baris (dipakai sebagai `external_id`) |
| `event_id`, `nama_event` | uuid, string | mis. "Seleksi CASN Sekolah Rakyat 2026" |
| `rekrutmen_id` | uuid | |
| `instansi` | string | nama instansi |
| `jenis_pengadaan` | string | terlihat: `PPPK Guru`, `PPPK Teknis` (nilai CPNS belum teramati) |
| `jenis_formasi` | string | terlihat: `UMUM` |
| `periode` | int | |
| `jabatan_kode`, `nama_jabatan` | string | `jabatan_kode` tidak unik per baris |
| `group_pendidikan`, `nama_pendidikan` | string/null | **kosong untuk PPPK Guru** |
| `lokasi_formasi` | string | teks bebas |
| `is_disabilitas` | bool/null | |
| `jumlah` | int | kuota |
| `usia_min`, `usia_max` | int | |
| `jobdesc`, `penghasilan_min`, `penghasilan_max` | null | kosong pada seluruh baris yang teramati |
| `persyaratan` | array | `{ id, nama, mandatory }` |

### Profil data saat riset (2.012 baris)

- Event: Seleksi CASN Sekolah Rakyat 2026 (1.754 baris) dan Seleksi CASN Guru Agama Sekolah Rakyat 2026 (258 baris). Instansi tunggal: Kementerian Sosial.
- Jenis: PPPK Guru 1.992, PPPK Teknis 20. Total kuota 8.511.
- Kosong: `group_pendidikan` dan `nama_pendidikan` 1.992/2.012; `jobdesc`, `penghasilan_*` 2.012/2.012; `is_disabilitas` 266/2.012.
- Lokasi: `Kab.` 1.448, `Kota` 523, `Prov.` 21, satuan kerja "Pusat ..." 20.
- Usia: (20–40) 1.734, (20–50) 258, (20–45) 20.
- 29 nilai `jabatan_kode` berbeda.
- Contoh `nama_pendidikan`: `D-IV PSIKOLOGI / D-IV BIMBINGAN KONSELING / S-1 PSIKOLOGI`, dan `D-IV SEMUA JURUSAN / S-1 SEMUA JURUSAN`.
- Lokasi: 163 teks lokasi unik pada data 16 Nov 2026; 153 geografis cocok seluruhnya dengan referensi Kemendagri setelah normalisasi (penulisan seperti "Tojo Una-Una", "Padang Sidimpuan", "Palangka Raya", "Prov. DKI Jakarta" berbeda dari nama resmi), 10 adalah nama satuan kerja.

Fixture uji: [`samples/bkn-spf-sample.json`](samples/bkn-spf-sample.json) (2 baris, persyaratan dipotong).

### Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Endpoint tidak berdokumen resmi, bisa berubah atau diblokir | Adapter dengan validasi skema; data lama tetap dipakai; konfigurasi URL; jalur cadangan permohonan resmi (C) |
| Respons kosong/rusak menghapus data | Pengaman: tarik formasi hanya bila baris ≥ 50% sinkron sukses sebelumnya |
| Beban ke server BKN | 1 request per sinkron, default harian, hash respons |
| Skema berubah | Batch gagal, notifikasi di panel admin, `payload` mentah tersimpan |

## 2. Data pelamar 2024: tidak ada API publik

Yang **tidak** ditemukan: API publik, dataset terbuka (data.go.id tidak memuat), atau kumpulan data pelamar per jabatan 2024 di GitHub.

Status per 15 Nov 2026 (Fase 3): angka tingkat A sudah dimuat ke `database/seeders/data/statistik_instansi_2024.csv` dari sumber yang **dibuka langsung** (BKN, detikEdu, Kompas TV, Liputan6). Tirto dilindungi Cloudflare (403) dan sengaja tidak ditembus. Tabel di bawah adalah daftar rujukan awal; kolom catatan menunjukkan sumber mana yang akhirnya dipakai.

Rujukan awal:

| Rujukan | Isi menurut ringkasan | Catatan |
|---|---|---|
| [Tirto: 30 instansi pusat dengan pelamar CPNS 2024 terbanyak](https://tirto.id/30-instansi-pusat-dengan-pelamar-cpns-2024-terbanyak-menurut-bkn-g3Ul) | Data akhir BKN; total 3.963.832 pelamar (±3,57 juta submit); Kemenkumham 567.552; Kemenag 411.194 dari 20.772 formasi | Tidak dipakai (Cloudflare 403); angka yang sama diambil dari detikEdu |
| [Tirto: 30 instansi pusat sepi peminat](https://tirto.id/30-instansi-pusat-sepi-peminat-cpns-2024-acuan-tahun-depan-g3Xb) | 30 instansi terendah, dimulai Kemenko PMK 385 pelamar | Tidak dipakai (Cloudflare 403) |
| [Liputan6: instansi paling ramai dan sepi](https://www.liputan6.com/bisnis/read/5689091/cpns-2024-daftar-instansi-pemerintah-paling-ramai-dan-sepi-peminat) | Snapshot 29 Agu 2024 (mis. Kemenkumham 183.931 submit dari 9.070 formasi) | Dipakai: snapshot 29 Agu 2024 (sementara) |
| [Kompas TV: cara melihat jumlah pelamar](https://www.kompas.tv/nasional/537705/cara-melihat-jumlah-pelamar-cpns-2024-per-instansi-ini-data-terbaru-bkn) | Menyebut BKN hanya merilis instansi terbanyak/tersedikit; snapshot awal (Kemenag 287.683) | Dipakai: menegaskan rekap BKN parsial dan snapshot awal |
| [Detik: statistik pelamar CPNS 2024](https://news.detik.com/berita/d-7506615/info-data-statistik-pelamar-cpns-2024-berdasarkan-data-bkn-terkini) | Statistik per tanggal tertentu | Snapshot |
| [Goodnews: pesaing dari hasil seleksi administrasi](https://www.goodnewsfromindonesia.id/ragam/serba-serbi/ternyata-jumlah-pesaing-bisa-diketahui-lewat-hasil-seleksi-administrasi-cpns) | Jumlah pesaing per jabatan bisa dihitung dari hasil seleksi administrasi | Dasar tingkat B |
| [CNN Indonesia: cek pesaing](https://www.cnnindonesia.com/edukasi/20241004165434-561-1151655/cara-cek-jumlah-pesaing-cpns-2024-sesuai-jabatan-yang-dilamar) | Peserta lolos administrasi dapat melihat pesaing per jabatan di SSCASN | Tidak bisa diakses publik tanpa login |
| [Kemenkes: cek jumlah pelamar](https://umsu.ac.id/health/cara-cek-jumlah-pelamar-cpns-kemenkes-2024-di-casn-kemkes-go-id) | Kemenkes menyediakan rekap sendiri di `casn.kemkes.go.id` | Contoh sumber per instansi |

Catatan penting untuk agent:
- Angka antar-sumber **berbeda karena tanggal snapshot**. Simpan semua sebagai snapshot terpisah dengan sumber; tampilan memakai yang terbaru.
- "Submit/pelamar" ≠ "lolos administrasi". Beri label yang tepat (kolom `keyakinan` dan teks UI).
- Nama jabatan antar-tahun tidak selalu sama persis. Pencocokan harus konservatif.

## 3. Sumber lain yang diperiksa

- **PDF BKN 2024**: sebagian besar URL lama (`bkn.go.id/unggahan/2024/...`) kini **404**. Satu PDF yang masih ada (siaran pers 23 Apr 2025 soal optimalisasi) tidak memuat angka per instansi. Jangan mengandalkan URL lama; cari lewat halaman rilis BKN yang aktif.
- **Portal SSCASN lama (2024)**: tidak lagi tersedia; portal baru hanya menampilkan event yang sedang berjalan. Archive.org belum diperiksa. Boleh dicoba agent untuk Fase 4–5 bila hostnya diizinkan.
- **PPID BKN**: jalur permohonan resmi tersedia (permohonan tertulis langsung atau elektronik). Lihat [`ppid-bkn-request.md`](ppid-bkn-request.md). Alamat/surel PPID resmi harus diverifikasi di situs BKN sebelum dikirim.
- **Referensi wilayah** (provinsi/kab/kota): dipilih di Fase 4. Sumber: repositori `cahyadsn/wilayah` (`db/wilayah.sql`), **lisensi MIT** (Copyright (c) 2017-2025 Cahya DSN), memuat kode dan nama wilayah administrasi sesuai Kepmendagri No 300.2.2-2138 Tahun 2025 (berkas diunduh 16 Nov 2026; terakhir diubah oleh sumbernya 13 Feb 2026). Hanya tingkat 1 dan 2 yang diambil (38 provinsi, 416 kabupaten, 98 kota) ke `database/seeders/data/wilayah_kemendagri.csv`; kecamatan dan desa tidak dipakai. Pembaruan wilayah di masa depan: unduh ulang berkas sumber, regenerasi CSV dengan skrip yang sama, jalankan `php artisan sicasn:muat-wilayah`.
- **Katalog jurusan/jenjang**: tidak ada sumber master; dibangun dari data `nama_pendidikan` yang tersinkron.

## 4. Yang belum bisa ditentukan sekarang

- Fokus produk saat ini **CPNS saja**. Seluruh baris API saat riset adalah PPPK, sehingga katalog formasi CPNS kosong sampai BKN menayangkan event CPNS. Sistem tetap mencatat event PPPK sebagai "terdeteksi, diabaikan".
- Bentuk data formasi **CPNS reguler** (nilai `jenis_pengadaan`, kelengkapan `nama_pendidikan`, kode jabatan) karena belum dipublikasikan di API. Parser harus toleran dan diuji ulang begitu event CPNS muncul.
- Apakah BKN menambahkan jumlah pelamar ke API pada seleksi tahun ini. Bila ada, itu menjadi sumber tingkat resmi baru, dan adapter harus memetakannya.
