# SICASN — Panduan Lengkap untuk Agent

> Dokumen ini adalah **sumber kebenaran tunggal** untuk membangun SICASN. Baca seluruhnya sebelum menulis kode.
> Dokumen pendukung: [`docs/data-sources.md`](docs/data-sources.md) (hasil riset data), [`docs/ppid-bkn-request.md`](docs/ppid-bkn-request.md) (draf permohonan data ke BKN), [`docs/samples/bkn-spf-sample.json`](docs/samples/bkn-spf-sample.json) (fixture respons API BKN).

---

## 1. Tujuan produk

SICASN membantu calon ASN (CPNS/PPPK) **memilih formasi yang realistis** dengan melihat:

1. formasi yang sedang dibuka (instansi, jabatan, lokasi, kuota, syarat), dan
2. **tingkat persaingan berdasarkan jumlah pelamar tahun 2024**, supaya mereka tahu instansi/jabatan mana yang berpotensi.

Semua eksplorasi dilakukan lewat **filter** (jenis, pendidikan, jurusan, lokasi, instansi). **Tidak ada form isian profil.**

Produk ini **berbayar manual dan tertutup**: tidak ada registrasi publik, hanya akun yang dibuat admin.

### Prinsip yang tidak boleh dilanggar

1. **Jangan mengarang data.** Angka pelamar, kuota, syarat, dan jurusan harus punya sumber dan tanggal. Kalau tidak ada, tampilkan "Data belum tersedia", jangan diperkirakan, dan jangan diisi dengan angka contoh di production.
2. **Bukan portal resmi.** Sistem ini tidak boleh terlihat seperti situs pemerintah. Semua halaman formasi menampilkan disclaimer dan tautan ke sumber resmi (sscasn.bkn.go.id).
3. **Tidak menyimpan data pribadi pelamar.** Hanya agregat (jumlah). Nama dan NIK peserta dari PDF pengumuman tidak boleh masuk database atau repo.
4. **Tidak menembus perlindungan.** Sumber data memakai WAF. Jangan mengakali captcha, rotasi IP, atau header palsu. Ambil seperlunya dengan sopan (lihat §8).
5. **Siap deploy.** Pemilik tidak mau mengumpulkan data manual. Semua yang bisa otomatis harus otomatis (§8, §14).

---

## 2. Keputusan yang sudah final (jangan dibuka ulang)

| Topik | Keputusan |
|---|---|
| Framework | **Laravel 13** (verifikasi versi dan syarat PHP lewat dokumentasi resmi sebelum `composer create-project`) |
| Database | **MySQL** (8.x), charset `utf8mb4` |
| Frontend | **Inertia + React + TypeScript + shadcn/ui** (pakai starter kit React resmi Laravel jika tersedia di v13; verifikasi) |
| Arsitektur | **Domain-Driven Design**, clean architecture, clean code |
| Desain | Bersih dan minimalis, mobile-first, mode terang dan gelap |
| Akses | Setiap pengguna **login**. Tidak ada registrasi publik |
| Pembayaran | **Manual**: chat admin via WhatsApp → transfer → kirim bukti ke WA → admin membuat akun |
| Email | Setelah admin membuat pengguna, sistem **otomatis mengirim email berisi email login + password sementara** |
| Sumber formasi | API publik BKN (§8), sinkron terjadwal |
| Data pelamar 2024 | Strategi bertingkat A + B + C (§9) |

---

## 3. Fakta hasil riset (sudah diverifikasi, Nov 2026)

- API publik tanpa autentikasi: `GET https://api-rekrutmen.bkn.go.id/portal/api/spf`. Dipakai oleh halaman `sscasn.bkn.go.id/daftar-formasi/`. Respons ±4,5 MB, satu request, tanpa paginasi.
- Bentuk respons: `{ status, error, message, data: [ ... ] }`. Contoh ada di `docs/samples/bkn-spf-sample.json`.
- Isi saat riset: **hanya event yang sedang dibuka** (Seleksi CASN Sekolah Rakyat 2026, 2.012 baris, 100% PPPK, instansi Kementerian Sosial). Formasi CASN reguler belum ada. Sistem harus otomatis menampung event baru ketika BKN mempublikasikannya.
- **Tidak ada field jumlah pelamar**, dan **tidak ada data 2024** di API itu.
- Kualitas data (dari 2.012 baris): `group_pendidikan` dan `nama_pendidikan` **kosong di 1.992 baris** (semua PPPK Guru; kualifikasi tersirat di nama jabatan). `jobdesc`, `penghasilan_min`, `penghasilan_max` kosong semua. `is_disabilitas` kosong di 266 baris. Persyaratan (`persyaratan[]`) selalu ada.
- `lokasi_formasi` berupa teks bebas: `Kab. X`, `Kota X`, `Prov. X`, atau nama satuan kerja (`Pusat ... | Wilayah IV`).
- `nama_pendidikan` memakai format: `D-IV PSIKOLOGI / S-1 PSIKOLOGI / ...`, dan `D-IV SEMUA JURUSAN / S-1 SEMUA JURUSAN` untuk semua jurusan.
- Rekap pelamar CPNS 2024 dari BKN **hanya sebagian** (instansi terbanyak/tersedikit peminat) dan bergantung tanggal ambil data. Total akhir per 17 Sep 2024: 3.963.832 pelamar, 3,57 juta yang submit (rujukan validasi, lihat `docs/data-sources.md`).
- Banyak PDF resmi BKN 2024 sudah 404. Jangan mengandalkan URL lama.

---

## 4. Arsitektur

### 4.1 Struktur direktori

```
app/                      # Lapisan delivery Laravel (tipis)
  Http/Controllers/       # Controller tipis: terima request → panggil use case → return Inertia/redirect
  Http/Requests/          # FormRequest: validasi input
  Http/Resources/         # Pemetaan DTO → props Inertia
  Console/Commands/       # Command artisan: tipis, memanggil use case
  Providers/              # Binding interface → implementasi
src/                      # Inti aplikasi (namespace Src\), PSR-4 di composer.json
  Formasi/
    Domain/               # Entity, Value Object, Domain Service, interface Repository
    Application/          # Use case (Action/Handler), DTO, Query object
    Infrastructure/       # Eloquent model, repository, adapter BKN, migrasi terkait
  DataImport/             # Sinkron BKN, impor CSV, batch, validasi
  Pantauan/               # Daftar pantauan pengguna
  Identity/               # Akun, peran, kredensial sementara, notifikasi email
  Shared/                 # Value object bersama, util, interface lintas konteks
resources/js/             # React + Inertia + shadcn
  pages/                  # Satu file per halaman Inertia
  components/ui/          # Hasil shadcn (jangan diedit sembarangan)
  components/             # Komponen aplikasi (FormasiCard, FilterPanel, dll.)
  features/               # Logika per fitur (hooks, tipe)
  lib/                    # Util
tests/                    # Pest: Unit, Feature, Arch
docs/
```

### 4.2 Aturan dependensi (wajib, dijaga oleh arch test)

```
Presentation (app/Http, Console)  →  Application  →  Domain
Infrastructure                    →  Application, Domain
Domain  →  (tidak bergantung ke apa pun selain Shared dan PHP)
```

- **Domain** tidak boleh memakai `Illuminate\*` (kecuali `Collection` bila dipertimbangkan, lebih baik tidak), Eloquent, `Request`, atau fasad.
- **Application** boleh memakai interface (Repository, Mailer, Clock, PasswordGenerator), bukan implementasi.
- **Eloquent model hanya ada di Infrastructure.** Repository mengembalikan **DTO/read model** atau entity domain, bukan model Eloquent.
- Antar bounded context berkomunikasi lewat interface Application atau domain event, bukan lewat model konteks lain.
- Pendekatan pragmatis **CQRS-lite**: sisi tulis (Identity, DataImport) lewat entity/use case. Sisi baca berat (pencarian formasi dengan filter) lewat **Query object + read model** yang dioptimasi SQL.

### 4.3 Bounded context

| Context | Tanggung jawab | Konsep inti |
|---|---|---|
| `Formasi` | Katalog formasi, filter, statistik pelamar, penanda persaingan | `Formasi`, `Instansi`, `Jabatan`, `KualifikasiPendidikan`, `Lokasi`, `StatistikPelamar`, `TingkatPersaingan` |
| `DataImport` | Sinkron API BKN, impor CSV statistik pelamar, jejak batch | `ImportBatch`, `SumberData`, `SnapshotDate` |
| `Pantauan` | Daftar pantauan dan perbandingan formasi | `Pantauan` |
| `Identity` | Akun dibuat admin, peran, password sementara, masa aktif | `Pengguna`, `Peran`, `KredensialSementara`, `MasaAktif` |

### 4.4 Clean code, kesepakatan tim

- `declare(strict_types=1);` di semua file PHP. Kelas domain `final` bila tidak dirancang untuk diwarisi. Properti `readonly` untuk Value Object dan DTO.
- Satu use case = satu kelas dengan satu method publik (`__invoke` atau `handle`). Nama berbasis maksud: `CreateMemberAccount`, `SyncFormasiFromBkn`, `SearchFormasi`.
- Tidak ada logika bisnis di controller, model, atau komponen React. Controller maksimal ±15 baris per method.
- Enum PHP untuk nilai tetap (`JenisPengadaan`, `JenisFormasi`, `TingkatPersaingan`, `Peran`). Jangan pakai string ajaib.
- Nama dalam bahasa Inggris untuk kode (class, method, kolom) atau istilah domain Indonesia yang baku (`formasi`, `instansi`, `jabatan`, `pelamar`). Pilih satu konvensi dan konsisten. **Default: istilah domain Indonesia untuk konsep CASN, Inggris untuk teknis.**
- Teks UI dalam bahasa Indonesia lewat file terjemahan, bukan string yang ditanam.
- Tidak ada komentar yang mengulang kode. Komentar hanya untuk "mengapa".
- Larastan level 8+ (verifikasi level tertinggi yang realistis), Laravel Pint, ESLint + `tsc --strict`. Semuanya harus hijau di CI.

---

## 5. Model data (MySQL)

Semua tabel: `id` bigint unsigned (PK), `created_at`, `updated_at`. Foreign key dengan indeks. Gunakan migrasi bergaya modern Laravel.

### 5.1 Katalog formasi

**`instansis`**: `nama` (unik ternormalisasi), `slug`, `tipe` enum(`pusat`,`daerah`,`unknown`), `provinsi_id` nullable.

**`wilayahs`** (referensi lokasi): `kode` nullable, `nama`, `tingkat` enum(`provinsi`,`kabupaten`,`kota`), `provinsi_id` nullable. Diisi dari referensi wilayah resmi (agent mencari sumber yang sah, lihat §15 butir 6).

**`jabatans`**: `kode` (`jabatan_kode` BKN, **tidak unik global**, simpan bersama `nama_normal`), `nama`, `rumpun` nullable.

**`formasis`**: `external_id` char(36) **unik** (field `id` BKN), `event_id`, `nama_event`, `rekrutmen_id`, `instansi_id`, `jabatan_id`, `jenis_pengadaan` enum(`cpns`,`pppk_guru`,`pppk_teknis`,`pppk_nakes`,`lainnya`), `jenis_formasi` varchar (UMUM, dll.), `periode` tinyint, `lokasi_teks` varchar (asli dari BKN), `wilayah_id` nullable, `provinsi_id` nullable, `kuota` int, `usia_min`, `usia_max`, `penghasilan_min/max` nullable, `jobdesc` text nullable, `is_disabilitas` boolean nullable, `kualifikasi_tersedia` boolean (false jika `nama_pendidikan` kosong), `payload` JSON (respons mentah untuk audit), `first_seen_at`, `last_seen_at`, `status` enum(`aktif`,`ditarik`), indeks gabungan untuk filter (`status`,`jenis_pengadaan`,`provinsi_id`,`instansi_id`).

**`formasi_kualifikasis`**: `formasi_id`, `jenjang` enum(`SMA`,`D1`,`D2`,`D3`,`D4`,`S1`,`S2`,`S3`,...), `jurusan_id` nullable, `semua_jurusan` boolean. Hasil parsing `nama_pendidikan` (§8.3).

**`jurusans`**: `nama`, `nama_normal` unik. Dari semua teks jurusan yang terparsing.

**`formasi_persyaratans`**: `formasi_id`, `uraian` text, `wajib` boolean, `urutan`.

### 5.2 Statistik pelamar

**`statistik_pelamars`**:
`tahun` smallint, `jenis_pengadaan`, `cakupan` enum(`instansi`,`jabatan`), `instansi_id`, `jabatan_id` nullable, `wilayah_id` nullable, `jumlah_pelamar` int, `jumlah_formasi` int nullable, `tanggal_snapshot` date, `tingkat_data` enum(`A`,`B`,`C`) (§9), `sumber_url` varchar(500), `sumber_nama`, `keyakinan` enum(`resmi`,`turunan`), `import_batch_id`. Indeks unik (`tahun`,`jenis_pengadaan`,`cakupan`,`instansi_id`,`jabatan_id`,`wilayah_id`,`tanggal_snapshot`). Snapshot terbaru per kunci dipakai untuk tampilan.

### 5.3 Impor dan sinkron

**`import_batches`**: `tipe` enum(`sinkron_bkn`,`csv_statistik`), `status` enum(`berjalan`,`sukses`,`sebagian`,`gagal`), `mulai_at`, `selesai_at`, `ringkasan` JSON (baris baru, diubah, ditarik, gagal), `sumber`, `dibuat_oleh` nullable, `file_path` nullable.
**`import_errors`**: `import_batch_id`, `baris` int, `kolom`, `pesan`, `nilai` text.

### 5.4 Identity dan pantauan

**`users`**: `name`, `email` unik, `password`, `peran` enum(`admin`,`member`), `no_whatsapp` nullable, `aktif` boolean, `masa_aktif_sampai` datetime nullable, `wajib_ganti_password` boolean default true, `password_sementara_berlaku_sampai` datetime nullable, `dibuat_oleh` nullable (admin), `last_login_at`.
**`pantauans`**: `user_id`, `formasi_id`, `catatan` nullable. Unik (`user_id`,`formasi_id`).
**`pengaturans`**: key-value (`nomor_wa_admin`, `info_rekening`, `harga`, `teks_beranda`, ambang persaingan). Bisa diubah admin tanpa deploy.
**`email_logs`**: `user_id`, `tipe`, `status`, `error` nullable, `dikirim_at`. Jangan menyimpan password di sini.
**`audit_logs`**: siapa melakukan apa pada pengguna (buat, reset, nonaktifkan, perpanjang).

---

## 6. Fitur dan halaman

### 6.1 Pengunjung (belum login)

- **Beranda**: penjelasan singkat, contoh tampilan (data tersamarkan), harga dan cara akses, tombol **Chat Admin WhatsApp** (`https://wa.me/{nomor}?text={pesan_terisi}`; nomor dari pengaturan).
- **Halaman Cara Akses**: 4 langkah (chat admin → transfer → kirim bukti ke WA → akun dikirim via email). Info rekening dari pengaturan.
- **Login**. Tidak ada tombol daftar.

### 6.2 Member (sudah login)

**Eksplorasi Formasi** (`/formasi`), fitur inti:
- Filter (semua opsional, kombinasi AND, tersimpan di query string):
  - Jenis pengadaan (CPNS / PPPK)
  - Jenjang pendidikan
  - Jurusan (combobox pencarian, multi pilih)
  - Provinsi, lalu Kabupaten/Kota (bertingkat)
  - Instansi (combobox)
  - Jenis formasi (umum, disabilitas, dll.)
  - Tingkat persaingan (Longgar/Sedang/Ketat/Belum ada data)
  - Pencarian teks pada nama jabatan atau instansi
- Urutan: rasio terendah, kuota terbanyak, pelamar tersedikit, terbaru.
- Paginasi **sisi server** (volume saat musim CPNS bisa >100 ribu baris).
- Kartu/baris formasi menampilkan: instansi, jabatan, lokasi, jenis, kuota, syarat pendidikan ringkas, **pelamar 2024**, **rasio**, **lencana persaingan**, label tingkat data (lihat §9.3), tombol pantau.
- Mobile: filter dalam `Sheet` (drawer) dengan jumlah filter aktif, kartu bukan tabel lebar.
- **Aturan kualifikasi kosong**: jika `kualifikasi_tersedia = false`, tampilkan "Kualifikasi mengikuti ketentuan jabatan, lihat persyaratan" dan **jangan** memasukkan formasi itu ke hasil filter jurusan/jenjang sebagai cocok. Beri opsi filter "sertakan formasi tanpa rincian kualifikasi" (default: mati).

**Detail Formasi** (`/formasi/{id}`): data lengkap, daftar persyaratan, riwayat statistik pelamar (semua snapshot, sumber dan tanggal), tautan sumber resmi, disclaimer.

**Daftar Pantauan** (`/pantauan`): simpan, catatan pribadi, bandingkan hingga 4 formasi berdampingan.

**Akun** (`/akun`): ubah password (wajib saat login pertama dengan password sementara), lihat masa aktif, tombol hubungi admin untuk perpanjangan.

### 6.3 Admin (`/admin`, peran `admin`)

- **Dashboard**: jumlah member aktif, akan kedaluwarsa (≤7 hari), status sinkron BKN terakhir, batch impor terakhir.
- **Pengguna**: daftar, cari, **buat akun** (nama, email, nomor WA, masa aktif), **kirim ulang email** (membuat password sementara baru), reset password, nonaktifkan/aktifkan, perpanjang masa aktif. Setiap aksi tercatat di `audit_logs`.
- **Sinkron BKN**: status, tombol "Sinkron sekarang", riwayat batch, ringkasan perubahan.
- **Impor Statistik Pelamar**: unggah CSV, pratinjau dan validasi, laporan baris gagal, unduh template, riwayat batch, tombol rollback batch.
- **Data Referensi**: instansi, jabatan, jurusan, wilayah (lihat/koreksi pemetaan).
- **Pengaturan**: nomor WA admin, info rekening, harga, teks beranda, ambang persaingan.

### 6.4 Alur akun (inti bisnis)

```
Calon member --chat--> Admin (WhatsApp)
Calon member --transfer manual--> Rekening
Calon member --kirim bukti--> Admin (WhatsApp)
Admin (panel) --> Buat Pengguna (nama, email, WA, masa aktif)
Sistem --> generate password sementara (acak, kuat) --> hash --> simpan
Sistem --> queue --> email ke pengguna: email login + password sementara + tautan login
Pengguna login --> dipaksa ganti password --> akses penuh
```

Aturan:
- Password sementara: ≥12 karakter acak (CSPRNG), tampil **hanya di email**, tidak ditampilkan di UI admin, tidak masuk log. Berlaku 7 hari (`password_sementara_berlaku_sampai`). `wajib_ganti_password = true`.
- Email dikirim lewat **queue** dengan retry/backoff. Jika gagal, pengguna tetap dibuat; admin melihat status gagal di panel dan bisa tekan "Kirim ulang".
- Login dibatasi (rate limit per email + IP). Pesan galat tidak membocorkan apakah email ada.
- Akun nonaktif atau kedaluwarsa: login ditolak dengan pesan ramah ("Hubungi admin untuk perpanjangan").
- **Opsional (default aktif, bisa dimatikan di pengaturan): satu sesi aktif per akun**, untuk mencegah berbagi akun berbayar.
- Isi email: sapaan, email login, password sementara, tautan login, masa aktif, nomor WA admin, imbauan ganti password. Template Blade/Markdown Mailable dalam bahasa Indonesia, tampilan bersih.

---

## 7. Penanda persaingan

```
rasio = jumlah_pelamar_2024 / kuota_2024   // dihitung pada statistik, bukan pada kuota tahun ini
```

- Default ambang (bisa diubah admin, **bukan klaim resmi**): `< 5` Longgar, `5–20` Sedang, `> 20` Ketat. Tampilkan angka rasio di samping lencana.
- Kuota 2024 dan kuota tahun ini berbeda. Yang ditampilkan adalah **riwayat persaingan 2024**, bukan prediksi. Beri teks: "Berdasarkan data pelamar 2024, bukan jaminan tahun ini".
- Jika tidak ada data → lencana "Belum ada data". **Jangan** menebak dari instansi lain.

---

## 8. Sinkron formasi dari BKN (otomatis)

### 8.1 Adapter

- Interface Application `BknFormasiSource::fetch(): iterable<BknFormasiRecord>`; implementasi Infrastructure `HttpBknFormasiSource` memakai `Http` Laravel dengan `timeout` 120 dtk, `retry` 3× backoff.
- URL dari `config('sicasn.bkn.formasi_url')` (default endpoint §3). Mudah diganti bila BKN memindahkan API.
- Header: `User-Agent: SICASN/1.0 (+kontak admin dari konfigurasi)`, `Accept: application/json`. Tidak ada penyamaran.
- Validasi skema respons: wajib `error === false` dan `data` array. Bila skema berubah (field wajib hilang), batch ditandai gagal dan data lama **tetap dipakai**. Jangan hapus apa pun karena respons rusak/kosong.

### 8.2 Jadwal dan kesopanan

- `php artisan sicasn:sync-formasi` dijadwalkan **1× per hari** (default 02:00 WIB) lewat scheduler Laravel. Selama masa pendaftaran aktif dapat dinaikkan ke 1× per 6 jam lewat konfigurasi, tidak lebih sering.
- Satu request per sinkron. Simpan hash respons; jika sama dengan sinkron terakhir, lewati pemrosesan.
- Jika menerima 403/429/WAF challenge: berhenti, catat, coba lagi di jadwal berikutnya. **Jangan** mencoba menembus.
- Cek ketentuan layanan situs BKN dan pertimbangkan permohonan resmi (§9, C) sebagai jalur cadangan yang sah.

### 8.3 Pemrosesan (use case `SyncFormasiFromBkn`)

1. Buka `ImportBatch(tipe=sinkron_bkn)`.
2. Untuk setiap record: normalisasi instansi → `instansis` (upsert), jabatan → `jabatans`, lokasi → `wilayahs` (§8.4), kualifikasi → parsing (di bawah), persyaratan.
3. Upsert `formasis` berdasarkan `external_id`; perbarui `last_seen_at`.
4. Formasi aktif yang **tidak muncul** pada sinkron sukses → `status = ditarik` (bukan hapus). Terapkan hanya bila respons sah dan jumlah baris ≥ 50% dari sinkron sukses sebelumnya (pengaman).
5. Tutup batch dengan ringkasan (baru/diubah/ditarik/gagal). Satu record rusak tidak boleh menggagalkan seluruh batch (catat ke `import_errors`).
6. Idempoten: menjalankan dua kali berturut-turut menghasilkan state yang sama.

**Parsing `nama_pendidikan`** (turunan dari logika portal BKN): pisahkan dengan ` / `; tiap item `<JENJANG> <JURUSAN>`, token pertama = jenjang (`D-IV`, `S-1`, ...), sisanya = jurusan; jurusan `SEMUA JURUSAN` → `semua_jurusan = true`. Normalisasi jenjang: `D-IV→D4`, `S-1→S1`, dst. Uppercase + rapikan spasi. Jika `nama_pendidikan` kosong → `kualifikasi_tersedia = false` dan **jangan menurunkan jurusan dari nama jabatan** kecuali ada tabel pemetaan resmi.

### 8.4 Normalisasi lokasi

- Awalan `Kab.` / `Kota` / `Prov.` → cocokkan ke `wilayahs`. Nama satuan kerja (mis. `Pusat ... | Wilayah IV`) → `wilayah_id = null`, simpan `lokasi_teks`, tampilkan apa adanya.
- Provinsi diturunkan dari wilayah. Jika tidak bisa dipetakan, formasi tetap tampil tetapi tidak muncul pada filter provinsi. Tampilkan jumlah "belum terpetakan" di panel admin.

---

## 9. Data pelamar 2024: strategi bertingkat (A + B + C)

Tidak ada sumber resmi yang memuat jumlah pelamar 2024 per jabatan secara terbuka. Sistem menyimpan semua tingkat dan menampilkan yang paling rinci yang tersedia, **dengan label tingkatnya**.

| Tingkat | Cakupan | Sumber | Status |
|---|---|---|---|
| **A** | Per **instansi** (CPNS 2024) | Rilis resmi BKN dan pemberitaan yang mengutip BKN (daftar instansi terbanyak/tersedikit peminat, total nasional) | Dikerjakan agent di Fase 2 |
| **B** | Per **jabatan/formasi** | Hasil seleksi administrasi 2024 yang dipublikasikan tiap instansi (PDF/web), dihitung per jabatan | Dikerjakan agent bertahap, mulai dari instansi besar |
| **C** | Per jabatan, lengkap | **Permohonan informasi publik ke PPID BKN** (UU KIP) | Pemilik yang mengirim surat; sistem menyiapkan format impor dan draf surat |

### 9.1 Pengerjaan A (Fase 2)

- Kumpulkan angka dari sumber resmi BKN/arsip rilis; bila hanya tersedia lewat media, catat **nama media, URL, tanggal snapshot** dan tandai `keyakinan = turunan`.
- Validasi silang: total nasional 17 Sep 2024 = 3.963.832 pelamar. Angka per instansi dengan snapshot berbeda **disimpan sebagai baris snapshot terpisah**, jangan ditimpa. Tampilan memakai snapshot terbaru per instansi.
- Hasil disimpan sebagai berkas seed `database/seeders/data/statistik_instansi_2024.csv` (dengan kolom sumber dan tanggal) dan dimuat lewat `StatistikPelamarSeeder` memakai use case impor yang sama dengan CSV admin.
- Agent **tidak boleh** mengisi angka dari ingatan. Hanya angka yang benar-benar ditemukan dan bersumber.

### 9.2 Pengerjaan B (Fase 3, bertahap)

- Prioritas instansi: yang volumenya terbesar (Kemenkumham, Kemenag, Kemenkes, Kejaksaan Agung, Kemenhub, dan sejenisnya) lalu pemda besar.
- Tahapan: temukan PDF/halaman "hasil seleksi administrasi" resmi instansi → ekstrak teks (`pdftotext -layout`) → **hitung jumlah baris peserta per jabatan** (tahap administrasi = peserta yang memenuhi syarat; catat bahwa ini **bukan** total pendaftar) → simpan hanya agregat (jabatan, jumlah, sumber, tanggal).
- **Jangan menyimpan nama/NIK**. Berkas unduhan mentah berada di luar repo dan dihapus setelah diagregasi.
- Beri `keyakinan = turunan` dan label "lolos administrasi", beda dari "total pelamar".
- Tulis parser per format dengan uji terhadap sampel; hentikan bila format tidak konsisten, jangan menebak.
- Setiap instansi yang selesai dicatat di `docs/data-coverage.md` (instansi, jumlah jabatan terhitung, tanggal, sumber).

### 9.3 Pengerjaan C

- Draf surat permohonan: [`docs/ppid-bkn-request.md`](docs/ppid-bkn-request.md). Pemilik mengisi identitas dan mengirimkannya.
- Format impor hasil permohonan: template CSV di admin (§10), kolom: `tahun, jenis_pengadaan, instansi, kode_jabatan, nama_jabatan, lokasi, jumlah_formasi, jumlah_pelamar, tanggal_snapshot, sumber_nama, sumber_url`.
- Ketika data C masuk, ia otomatis menggantikan A/B pada tampilan untuk kunci yang sama (tingkat C paling rinci dan resmi).

### 9.4 Aturan tampilan data pelamar

- Pencocokan formasi saat ini ↔ statistik 2024: (1) `instansi` + `kode_jabatan` atau `nama_jabatan` ternormalisasi (+ lokasi bila ada) → **per jabatan** (B/C); (2) bila tidak ada, `instansi` saja → **rata-rata instansi** (A) dengan label "Rata-rata instansi, bukan per jabatan"; (3) bila tidak ada → "Belum ada data".
- Selalu tampilkan: sumber, tanggal snapshot, tingkat data. Beri ikon info yang menjelaskan.
- Pencocokan nama jabatan antar-tahun harus konservatif. Jika ragu, jangan cocokkan.

---

## 10. Impor CSV (admin)

- Unggah → validasi seluruh baris dulu → pratinjau (berhasil/gagal) → konfirmasi → proses lewat queue.
- Validasi: kolom wajib, tipe angka, `jumlah_pelamar ≥ 0`, tanggal valid, instansi dikenali (fuzzy match dengan saran; yang tidak dikenali dibuat setelah konfirmasi).
- Hasil: ringkasan, daftar baris gagal (unduh CSV), `ImportBatch` tercatat. **Rollback** batch menghapus baris statistik milik batch itu saja.
- Ukuran maksimal dan jenis berkas dibatasi. Tangani BOM, pemisah `;`/`,`, dan encoding Windows-1252.

---

## 11. Desain UI (shadcn, bersih)

- Komponen shadcn: `Button`, `Input`, `Select`, `Combobox (Command+Popover)`, `Sheet`, `Dialog`, `Badge`, `Table`, `Card`, `Tabs`, `Skeleton`, `Toast/Sonner`, `Form (react-hook-form + zod)`, `Alert`, `Tooltip`.
- Token desain di CSS variables: dukung terang/gelap. Satu warna aksen, banyak ruang kosong, tipografi konsisten, radius seragam.
- Mobile-first: tap target ≥ 44px, tanpa scroll horizontal, filter di drawer.
- Status lencana persaingan: tidak mengandalkan warna saja (sertakan teks/ikon), kontras memenuhi WCAG AA.
- Loading dengan skeleton, state kosong yang membantu ("Tidak ada formasi cocok. Coba longgarkan filter"), state galat yang jelas.
- Aksesibilitas: label form, urutan fokus, `aria` pada combobox, dukung keyboard.
- Performa: filter lewat Inertia partial reload (`only`), debounce pencarian, daftar opsi filter di-cache.
- Disclaimer ringkas di footer dan di setiap halaman formasi.

---

## 12. Keamanan

- Password di-hash (bcrypt/argon2 bawaan Laravel). Password sementara dibuat CSPRNG.
- Rate limiting login dan impor; CSRF aktif; kebijakan `Gate`/Policy untuk semua aksi admin; middleware `role:admin`.
- Validasi unggahan, simpan di disk privat, nama file diacak.
- Header keamanan (CSP yang cocok dengan Vite/Inertia, `X-Frame-Options`, dll.).
- `APP_DEBUG=false` di production. Secret hanya di `.env`. Tidak ada kredensial di repo.
- Kebijakan privasi singkat: data pribadi pengguna hanya nama, email, nomor WA. Tidak dibagikan.
- Audit log untuk aksi sensitif. Log tidak boleh memuat password.

---

## 13. Pengujian (Pest)

- **Unit**: Value Object, parsing kualifikasi, perhitungan rasio dan tingkat persaingan, pencocokan statistik ↔ formasi, normalisasi lokasi.
- **Feature**: pencarian formasi dengan kombinasi filter; alur buat pengguna → email terkirim (`Mail::fake`) → login pertama → ganti password; kontrol akses admin/member; kedaluwarsa akun; impor CSV (valid/invalid/rollback).
- **Integrasi sinkron**: `Http::fake` dengan `docs/samples/bkn-spf-sample.json` dan kasus rusak (skema berubah, kosong, 403, timeout); idempotensi; pengaman 50%.
- **Arch tests**: Domain tidak mengimpor `Illuminate`/Eloquent; Eloquent hanya di Infrastructure; controller tidak memakai model langsung; tidak ada `dd/dump/env()` di luar config.
- **Frontend**: pengujian komponen untuk FilterPanel dan kartu formasi (Vitest + Testing Library), minimal satu uji E2E Playwright untuk alur filter.
- CI (GitHub Actions): Pint, Larastan, Pest, `tsc`, ESLint, build Vite.

---

## 14. Deployment (target: tinggal deploy)

- `docker-compose` untuk produksi dan lokal: `app` (php-fpm), `web` (nginx), `mysql`, `queue` (worker), `scheduler`. Healthcheck.
- `.env.example` lengkap dan terdokumentasi. Variabel penting:
  `APP_URL`, `DB_*`, `MAIL_*` (SMTP), `MAIL_FROM_ADDRESS`, `SICASN_ADMIN_WHATSAPP`, `SICASN_ADMIN_EMAIL`, `SICASN_ADMIN_NAME`, `SICASN_ADMIN_PASSWORD` (hanya untuk instalasi pertama), `SICASN_BKN_FORMASI_URL`, `SICASN_SINGLE_SESSION`.
- Perintah satu langkah: `php artisan sicasn:install`, yang menjalankan migrasi, seeding peran dan pengaturan awal, membuat admin pertama dari env (lalu mewajibkan ganti password), memuat seed statistik instansi 2024 (§9.1), melakukan sinkron BKN pertama, dan mencetak ringkasan kesiapan (cek SMTP, DB, scheduler, queue).
- Tidak ada langkah manual pengumpulan data setelah `install`.
- Dokumentasi `docs/deployment.md`: langkah deploy (VPS/Docker), cron/scheduler, supervisor queue, backup MySQL harian, rotasi log, pembaruan.
- Observabilitas: log terstruktur; halaman admin menampilkan status sinkron, antrean gagal, dan email gagal.

---

## 15. Rencana kerja bertahap

Kerjakan berurutan. Setiap fase selesai bila **kriteria penerimaan** terpenuhi dan tes hijau. Jangan melompat.

**Fase 0, Fondasi**
- Proyek Laravel 13 + MySQL + Inertia React + shadcn, struktur `src/` + PSR-4, Pest, Pint, Larastan, arch test, CI, docker-compose.
- ✅ `composer test` dan `npm run build` hijau; halaman beranda tampil.

**Fase 1, Identity dan alur akun**
- Login, peran, policy, panel admin Pengguna, buat akun + email otomatis, ganti password wajib, kedaluwarsa, audit log, beranda dengan tombol WA, pengaturan.
- ✅ Admin membuat pengguna → email terkirim ke antrean → pengguna login dan dipaksa ganti password → akses `/formasi` hanya bila aktif.

**Fase 2, Katalog formasi dan sinkron BKN**
- Skema §5.1, adapter dan use case §8, normalisasi lokasi, scheduler, panel status sinkron, seed wilayah.
- ✅ Sinkron dari fixture dan dari API nyata menghasilkan 2.012 formasi aktif (angka saat riset), idempoten, aman terhadap respons rusak.

**Fase 3, Eksplorasi Formasi**
- Query + read model, halaman `/formasi` dan detail, semua filter, paginasi server, UI mobile, aturan kualifikasi kosong.
- ✅ Filter bekerja gabungan, URL bisa dibagikan, p95 query < 300 ms pada 100 ribu baris uji.

**Fase 4, Data pelamar 2024**
- Skema §5.2, use case impor, impor CSV admin + rollback, seed A (§9.1), lencana persaingan, aturan tampilan §9.4.
- ✅ Formasi menampilkan pelamar/rasio/tingkat data dengan sumber; "Belum ada data" bila tidak ada.

**Fase 5, Tingkat B**
- Parser hasil seleksi administrasi per instansi besar, `docs/data-coverage.md`.
- ✅ Minimal beberapa instansi besar tercakup dengan agregat per jabatan dan sumber; tanpa data pribadi tersimpan.

**Fase 6, Pantauan dan perbandingan**
- ✅ Simpan, catatan, bandingkan hingga 4 formasi.

**Fase 7, Pengerasan dan rilis**
- `sicasn:install`, `docs/deployment.md`, audit keamanan (`/security-review`), pemeriksaan aksesibilitas, backup, ringkasan kesiapan.
- ✅ Deploy bersih di mesin baru hanya dengan mengisi `.env` dan menjalankan perintah dokumentasi.

### Izin jaringan yang diperlukan agent
Lingkungan sesi membatasi host keluar. Host yang sudah diizinkan: `sscasn.bkn.go.id`, `api-sscasn.bkn.go.id`, `api-rekrutmen.bkn.go.id`, `bkn.go.id`, `www.bkn.go.id`, `data.go.id`. Untuk Fase 4–5 agent akan perlu menambah host sumber (mis. situs media yang mengutip rilis BKN dan situs instansi). **Ajukan lewat `update_environment` dengan host eksak, satu per kebutuhan nyata**, dan sebutkan alasannya.

---

## 16. Hal yang masih perlu keputusan pemilik (tidak menghalangi Fase 0–2)

1. **Masa aktif akun & harga** (mis. 30/90 hari atau sampai akhir seleksi)? Default sementara: field `masa_aktif_sampai` diisi admin.
2. **Satu sesi per akun**: aktifkan? (default: ya)
3. **Penyedia SMTP** dan domain pengirim.
4. **Nomor WhatsApp admin**, info rekening, teks harga.
5. **Nama domain** dan lokasi hosting.
6. Sumber **referensi wilayah** (provinsi/kabupaten/kota) yang dipilih. Default: cari dataset resmi/terbuka yang berlisensi jelas, catat sumbernya.
7. Kebutuhan fitur lanjutan (tahap berikutnya): checklist berkas, pengingat jadwal, latihan SKD.

---

## 17. Cara agent bekerja di repo ini

- Baca dokumen ini dulu. Bila ada ketidaksesuaian dengan kenyataan (mis. API BKN berubah), **perbarui dokumen** dan jelaskan, jangan diam-diam menyimpang.
- Verifikasi versi dan perintah pustaka lewat dokumentasi resmi sebelum menulis (Laravel 13, starter kit, shadcn, Inertia). Jangan mengandalkan ingatan.
- Kerjakan per fase, commit kecil dan bermakna di branch kerja yang ditetapkan, tes dulu sebelum lanjut.
- Jangan membuat pull request kecuali diminta pemilik.
- Jangan menambah fitur di luar dokumen ini tanpa persetujuan.
- Laporkan hasil dengan jujur: apa yang lolos, gagal, atau dilewati.
