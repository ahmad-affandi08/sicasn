# SICASN — Panduan Lengkap untuk Agent

> Dokumen ini adalah **sumber kebenaran tunggal** untuk membangun SICASN. Baca seluruhnya sebelum menulis kode.
> Pendukung: [`docs/data-sources.md`](docs/data-sources.md) (riset data), [`docs/ppid-bkn-request.md`](docs/ppid-bkn-request.md) (draf permohonan data ke BKN), [`docs/samples/bkn-spf-sample.json`](docs/samples/bkn-spf-sample.json) (fixture respons API BKN), [`CekKonvensi.py`](CekKonvensi.py) (pemeriksa konvensi).

---

## 1. Tujuan produk

SICASN membantu **calon CPNS** memilih formasi yang realistis dengan melihat:

1. formasi CPNS yang sedang dibuka (instansi, jabatan, lokasi, kuota, syarat), dan
2. **tingkat persaingan berdasarkan jumlah pelamar CPNS 2024**, supaya mereka tahu instansi dan jabatan mana yang berpotensi.

Eksplorasi dilakukan lewat **filter** (pendidikan, jurusan, lokasi, instansi). **Tidak ada form isian profil.**

**Fokus saat ini: CPNS saja.** PPPK ditunda; arsitektur tetap menyimpan `jenis_pengadaan` agar PPPK bisa diaktifkan kemudian lewat konfigurasi, tanpa perubahan skema.

Produk ini **berbayar manual dan tertutup**: tidak ada registrasi publik. Akun dibuat admin setelah pembayaran diterima.

### Prinsip yang tidak boleh dilanggar

1. **Jangan mengarang data.** Angka pelamar, kuota, syarat, dan jurusan harus punya sumber dan tanggal. Bila tidak ada, tampilkan "Data belum tersedia". Tidak ada data contoh di production.
2. **Bukan portal resmi.** Tampilan tidak boleh menyerupai situs pemerintah. Halaman formasi memuat disclaimer dan tautan ke sumber resmi (sscasn.bkn.go.id).
3. **Tidak menyimpan data pribadi pelamar.** Hanya agregat. Nama dan NIK dari PDF pengumuman tidak boleh masuk database atau repo.
4. **Tidak menembus perlindungan.** Sumber data memakai WAF. Dilarang mengakali captcha, memutar IP, atau memalsukan header.
5. **Siap deploy.** Pemilik tidak mau mengumpulkan data manual dan tidak mau mengisi `.env` panjang. Semua yang bisa otomatis harus otomatis; semua pengaturan operasional diatur dari dashboard admin.

---

## 2. Keputusan final (jangan dibuka ulang)

| Topik | Keputusan |
|---|---|
| Framework | **Laravel 13** (verifikasi versi dan syarat PHP lewat dokumentasi resmi sebelum `composer create-project`) |
| Database | **MySQL** 8.x, `utf8mb4` |
| Frontend | **Inertia + React + TypeScript + shadcn/ui** (pakai starter kit React resmi Laravel bila tersedia di v13; verifikasi) |
| Arsitektur | **DDD**, clean architecture, clean code |
| Font | **Roboto**, di-host sendiri (`@fontsource/roboto`), tanpa Google Fonts |
| Ikon | **react-icons** saja (disarankan paket `react-icons/lu`) |
| Komentar kode | **Dilarang seluruhnya** (`//`, `#`, `/* */`, `{{-- --}}`, `<!-- -->`). Diperiksa `CekKonvensi.py` |
| Notifikasi | **AlertDialog modal di tengah layar** untuk sukses, gagal, galat, peringatan, dan konfirmasi. Tanpa toast |
| CI | **Tidak dipakai.** Pemeriksaan dijalankan lokal (§16) |
| `.env` | **Hanya `APP_KEY` dan `DB_*`.** Semua pengaturan lain di database, diatur dari dashboard admin |
| Email | Beberapa penyedia (termasuk **Hostinger**), dikonfigurasi di dashboard admin |
| WhatsApp | Nomor dan pesan dikonfigurasi di dashboard admin |
| Akses | Setiap pengguna **login**. Tidak ada registrasi publik |
| Masa aktif | **Tidak ada masa aktif.** Akun aktif sampai **admin menonaktifkannya** (mis. belum bayar) |
| Pembayaran | **Manual**: chat admin via WhatsApp → transfer → kirim bukti ke WA → admin membuat akun |
| Kredensial | Setelah admin membuat pengguna, sistem **otomatis mengirim email berisi email login dan password sementara** |
| Cakupan | **CPNS saja** dulu |
| Responsif | Desktop, tablet, dan HP wajib nyaman dipakai |

---

## 3. Fakta hasil riset (diverifikasi, Nov 2026)

- API publik tanpa autentikasi: `GET https://api-rekrutmen.bkn.go.id/portal/api/spf`, dipakai halaman `sscasn.bkn.go.id/daftar-formasi/`. Respons ±4,5 MB, satu request, tanpa paginasi. Bentuk: `{ status, error, message, data: [...] }`. Contoh di `docs/samples/bkn-spf-sample.json`.
- Isi saat riset: **hanya Seleksi CASN Sekolah Rakyat 2026** (2.012 baris, **100% PPPK**, Kementerian Sosial). **Belum ada satu pun formasi CPNS.**
- **Tidak ada field jumlah pelamar** dan **tidak ada data 2024** di API itu.
- Kualitas data: `nama_pendidikan` kosong di 1.992/2.012 baris (semua PPPK Guru). `jobdesc` dan `penghasilan_*` kosong semua. `is_disabilitas` kosong di 266 baris. `lokasi_formasi` berupa teks bebas (`Kab. X`, `Kota X`, `Prov. X`, atau nama satuan kerja).
- Format `nama_pendidikan`: `D-IV PSIKOLOGI / S-1 PSIKOLOGI / ...` dan `D-IV SEMUA JURUSAN / S-1 SEMUA JURUSAN`.
- Rekap pelamar CPNS 2024 dari BKN **hanya sebagian** (instansi terbanyak/tersedikit peminat) dan bergantung tanggal ambil data. Total akhir per 17 Sep 2024: 3.963.832 pelamar (rujukan validasi, lihat `docs/data-sources.md`).
- Banyak PDF resmi BKN 2024 kini 404.

### Implikasi untuk fokus CPNS

Hari ini API **belum berisi formasi CPNS**, jadi katalog formasi akan kosong sampai BKN menayangkan event CPNS. Karena itu:

1. Sistem **harus berguna sebelum formasi CPNS terbit**: halaman **Peluang Instansi 2024** (§7.2) berbasis statistik pelamar 2024 sudah bernilai dan tidak bergantung API.
2. Sinkron tetap berjalan dan **mendeteksi event baru**. Admin diberi tahu begitu event CPNS muncul (§11.3).
3. Halaman formasi menampilkan keadaan kosong yang jelas: "Formasi CPNS belum dibuka BKN. Sementara itu lihat peluang instansi berdasarkan data 2024."
4. Parser CPNS **belum pernah melihat data CPNS nyata**. Pemetaan nilai `jenis_pengadaan` harus toleran (awalan `CPNS`, tidak sensitif huruf besar-kecil) dan diuji ulang saat data nyata muncul.

---

## 4. Arsitektur

### 4.1 Struktur direktori

```
app/                      Lapisan delivery Laravel (tipis)
  Http/Controllers/       Controller tipis: request → use case → Inertia/redirect
  Http/Requests/          FormRequest: validasi input
  Http/Resources/         Pemetaan DTO → props Inertia
  Console/Commands/       Command artisan tipis, memanggil use case
  Providers/              Binding interface → implementasi
src/                      Inti aplikasi (namespace Src\), PSR-4 di composer.json
  Formasi/                Domain · Application · Infrastructure
  DataImport/
  Pantauan/
  Identity/
  Pengaturan/
  Shared/                 Value object dan interface lintas konteks
resources/js/
  pages/                  Satu berkas per halaman Inertia
  components/ui/          Hasil shadcn (jangan diedit sembarangan)
  components/             Komponen aplikasi (KartuFormasi, PanelFilter, ...)
  components/dialog/      DialogProvider, useDialog, AlertDialog bergaya
  components/ilustrasi/   Ilustrasi SVG sebagai komponen React
  features/               Logika per fitur (hooks, tipe)
  lib/
resources/css/app.css     Token desain, Roboto
tests/                    Pest: Unit, Feature, Arch
docs/
CekKonvensi.py            Pemeriksa konvensi (wajib hijau sebelum commit)
.githooks/pre-commit      Menjalankan CekKonvensi.py --staged
```

### 4.2 Aturan dependensi (dijaga `CekKonvensi.py` dan arch test)

```
app/Http, app/Console  →  Application  →  Domain
Infrastructure         →  Application, Domain
Domain                 →  Shared dan PHP saja
```

- **Domain** tidak boleh memakai `Illuminate\*`, `App\*`, Eloquent, atau lapisan lain.
- **Application** memakai interface (Repository, `MailGateway`, `Clock`, `PasswordGenerator`), bukan implementasi, bukan Eloquent, bukan HTTP.
- **Eloquent model hanya di `src/<Context>/Infrastructure`.** Tidak ada `app/Models`. Model `User` untuk autentikasi juga di Identity/Infrastructure dan didaftarkan di `config/auth.php`.
- Repository mengembalikan DTO/read model atau entity domain, bukan model Eloquent.
- Antar-context lewat interface Application atau domain event.
- **CQRS-lite**: sisi tulis lewat entity dan use case; sisi baca berat (pencarian formasi) lewat Query object dan read model yang dioptimasi SQL.

### 4.3 Bounded context

| Context | Tanggung jawab | Konsep inti |
|---|---|---|
| `Formasi` | Katalog formasi CPNS, filter, statistik pelamar, penanda persaingan, peluang instansi | `Formasi`, `Instansi`, `Jabatan`, `KualifikasiPendidikan`, `StatistikPelamar`, `TingkatPersaingan` |
| `DataImport` | Sinkron API BKN, impor CSV statistik, jejak batch | `ImportBatch`, `SnapshotDate` |
| `Pantauan` | Daftar pantauan dan perbandingan | `Pantauan` |
| `Identity` | Akun dibuat admin, peran, kredensial sementara, aktif/nonaktif, audit | `Pengguna`, `Peran`, `KredensialSementara` |
| `Pengaturan` | Pengaturan operasional di database: email, WhatsApp, rekening, situs, ambang persaingan | `PengaturanEmail`, `PenyediaEmail`, `PengaturanWhatsApp`, `PengaturanSitus` |

### 4.4 Konvensi kode (semua diperiksa `CekKonvensi.py` bila memungkinkan)

- **Tanpa komentar apa pun.** Kode harus menjelaskan dirinya lewat nama. Tidak ada `//`, `#`, `/* */`, docblock, `{/* */}`, `{{-- --}}`, `<!-- -->`. Gunakan tipe native, nama yang jelas, dan fungsi kecil. Tidak ada `@ts-ignore` atau `@phpstan-ignore`; perbaiki tipenya.
- `declare(strict_types=1);` di semua PHP pada `src/`, `app/`, `tests/`, `database/`. Kelas `final` bila tidak dirancang untuk diwarisi. Properti `readonly` untuk Value Object dan DTO.
- Satu use case = satu kelas dengan satu method publik (`handle`). Nama berbasis maksud: `CreateMemberAccount`, `SyncFormasiFromBkn`, `SearchFormasi`.
- Controller maksimal ±15 baris per method, tanpa logika bisnis. Begitu pula model dan komponen React.
- PHP `enum` untuk nilai tetap. Tidak ada string ajaib.
- Penamaan: istilah domain Indonesia yang baku untuk konsep CASN (`formasi`, `instansi`, `jabatan`, `pelamar`), Inggris untuk teknis (`Repository`, `Handler`). Konsisten.
- Teks UI bahasa Indonesia lewat berkas terjemahan, bukan string yang ditanam di komponen.
- React: komponen fungsi, TypeScript `strict`, tanpa `any`. Satu komponen per berkas. Logika ke `features/` atau hook. Maksimal ±400 baris per berkas.
- Dilarang: `dd`, `dump`, `var_dump`, `console.log`, `alert/confirm/prompt` bawaan browser, `env()` di luar `config/`.
- Larastan (level tertinggi yang realistis), Laravel Pint, ESLint, `tsc --noEmit` hijau sebelum commit.

---

## 5. Pengaturan di database (bukan `.env`)

### 5.1 `.env` hanya ini

```
APP_KEY=
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

`APP_KEY` wajib bagi Laravel (enkripsi) dan dibuat otomatis oleh `sicasn:install`. `APP_ENV`/`APP_DEBUG` boleh diatur untuk lokal, tetapi tidak ada di `.env.example`. Karena itu `config/*.php` harus diubah supaya:

- `app.debug` default `false`, `app.env` default `production`.
- `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE` di-hardcode ke `database`.
- Mailer bawaan tidak membaca `.env`; mailer dibangun saat runtime dari pengaturan database (§5.3).
- Tidak ada `env('...')` selain kunci di atas (dicek `CekKonvensi.py`, aturan `ENV_HANYA_DB`).

### 5.2 Penyimpanan

Tabel `pengaturans`: `grup`, `kunci`, `nilai` (text), `terenkripsi` (boolean). Nilai rahasia (kata sandi SMTP, API key) memakai `Crypt` Laravel dan **tidak pernah dikirim balik ke browser**; UI hanya menampilkan "tersimpan" dengan tombol "Ganti". Pembacaan lewat `SettingsRepository` dengan cache yang dibersihkan saat ada perubahan. Worker antrean **harus membaca pengaturan terbaru pada setiap pengiriman**, bukan memakai konfigurasi yang dibekukan saat boot.

### 5.3 Pengaturan Email (dashboard admin)

Admin memilih **penyedia aktif** dan, opsional, **penyedia cadangan** (otomatis dipakai bila aktif gagal). Bidang form menyesuaikan penyedia.

| Penyedia | Cara | Preset awal (verifikasi ke dokumentasi masing-masing saat implementasi) |
|---|---|---|
| **Hostinger** | SMTP | host `smtp.hostinger.com`, port `465` (SSL/TLS) atau `587` (STARTTLS), username = alamat email lengkap |
| SMTP Kustom | SMTP | host, port, enkripsi, username, password |
| Gmail / Google Workspace | SMTP | `smtp.gmail.com`, port `587`, wajib *app password* |
| Brevo | SMTP | `smtp-relay.brevo.com`, port `587` |
| SendGrid | SMTP | `smtp.sendgrid.net`, username `apikey` |
| Mailgun | SMTP | `smtp.mailgun.org` (Eropa: `smtp.eu.mailgun.org`), port `587` |
| Amazon SES | SMTP | `email-smtp.<region>.amazonaws.com`, port `587`, kredensial SMTP SES |
| Resend | SMTP | `smtp.resend.com`, port `465`, username `resend` |
| Postmark | SMTP | `smtp.postmarkapp.com`, port `587`, username dan sandi sama-sama server token |

**Keputusan implementasi: semua penyedia dijalankan lewat SMTP** (tanpa paket tambahan seperti SDK API), sehingga satu mekanisme melayani sembilan penyedia dan tidak ada ketergantungan yang sulit dipasang. Transport API khusus bisa ditambahkan nanti bila dibutuhkan.

Persyaratan:
- Identitas pengirim (nama, alamat) dan alamat balas diatur di sini. Tema email kustom ditunda.
- Tombol **Kirim Email Uji** ke alamat pilihan admin; hasil (berhasil/gagal beserta pesan galat) tampil di **AlertDialog**.
- Menyimpan atau mengganti penyedia aktif memerlukan **konfirmasi modal**; disarankan lulus uji dulu.
- Pengiriman memakai mailer yang dibangun saat runtime dari konfigurasi (Laravel mendukung `Mail::build`; verifikasi di dokumentasi) dan `failover` ke cadangan.
- Setiap pengiriman dicatat di `email_logs` (tanpa password/isi rahasia).
- Bila belum ada penyedia terkonfigurasi: dashboard menampilkan peringatan; pembuatan akun tetap bisa tetapi status email "belum terkirim" (§9).

### 5.4 Pengaturan WhatsApp (dashboard admin)

- Nomor WA admin (format internasional, divalidasi), nama tampilan.
- **Template pesan** dengan penanda, mis. `Halo {nama_admin}, saya ingin berlangganan SICASN.` Tombol di situs memakai `https://wa.me/{nomor}?text={pesan_ter-encode}`.
- Teks sapaan di halaman Cara Akses dan di email.

### 5.5 Pengaturan lain

Info rekening (mendukung beberapa rekening: bank, nomor, atas nama), harga dan teks paket, teks beranda, URL situs (diisi otomatis saat instal), ambang tingkat persaingan, satu-sesi-per-akun (default aktif), kontak balas, jam sinkron BKN, tombol "Tampilkan password sementara sekali" (default **mati**).

---

## 6. Model data (MySQL)

Semua tabel: `id` bigint unsigned, `created_at`, `updated_at`. Foreign key beserta indeks. Migrasi bergaya modern Laravel.

### 6.1 Katalog formasi

**`instansis`**: `nama` (unik ternormalisasi), `slug`, `tipe` enum(`pusat`,`daerah`,`unknown`), `provinsi_id` nullable.
**`wilayahs`**: `kode` nullable, `nama`, `tingkat` enum(`provinsi`,`kabupaten`,`kota`), `provinsi_id` nullable. Sumber referensi dipilih agent (§18 butir 3).
**`jabatans`**: `kode` (tidak unik global), `nama`, `nama_normal`.
**`formasis`**: `external_id` char(36) **unik**, `event_id`, `nama_event`, `rekrutmen_id`, `instansi_id`, `jabatan_id`, `jenis_pengadaan` enum(`cpns`,`pppk_guru`,`pppk_teknis`,`pppk_nakes`,`lainnya`), `jenis_formasi`, `periode`, `lokasi_teks`, `wilayah_id` nullable, `provinsi_id` nullable, `kuota`, `usia_min`, `usia_max`, `penghasilan_min/max` nullable, `jobdesc` nullable, `is_disabilitas` nullable, `kualifikasi_tersedia` boolean, `payload` JSON, `first_seen_at`, `last_seen_at`, `status` enum(`aktif`,`ditarik`). Indeks gabungan untuk filter.
**`formasi_kualifikasis`**: `formasi_id`, `jenjang`, `jurusan_id` nullable, `semua_jurusan` boolean.
**`jurusans`**: `nama`, `nama_normal` unik.
**`formasi_persyaratans`**: `formasi_id`, `uraian`, `wajib`, `urutan`.

### 6.2 Statistik pelamar

**`statistik_pelamars`**: `tahun`, `jenis_pengadaan`, `cakupan` enum(`instansi`,`jabatan`), `instansi_id`, `jabatan_id` nullable, `wilayah_id` nullable, `jumlah_pelamar`, `jumlah_formasi` nullable, `jumlah_lolos_administrasi` nullable, `tanggal_snapshot`, `tingkat_data` enum(`A`,`B`,`C`), `sumber_nama`, `sumber_url`, `keyakinan` enum(`resmi`,`turunan`), `import_batch_id`. Unik pada kombinasi kunci + `tanggal_snapshot`. Tampilan memakai snapshot terbaru per kunci.

### 6.3 Impor dan sinkron

**`import_batches`**: `tipe` enum(`sinkron_bkn`,`csv_statistik`), `status`, `mulai_at`, `selesai_at`, `ringkasan` JSON, `sumber`, `dibuat_oleh` nullable, `file_path` nullable.
**`import_errors`**: `import_batch_id`, `baris`, `kolom`, `pesan`, `nilai`.
**`event_terdeteksis`**: `event_id`, `nama_event`, `jenis_pengadaan_teramati`, `jumlah_baris`, `diabaikan` boolean (mis. PPPK karena fokus CPNS), `first_seen_at`, `diberitahukan_at` nullable.

### 6.4 Identity, pantauan, pengaturan

**`users`**: `name`, `email` unik, `password`, `peran` enum(`admin`,`member`), `no_whatsapp` nullable, `aktif` boolean default true, `diaktifkan_at`, `dinonaktifkan_at` nullable, `alasan_nonaktif` nullable, `catatan_pembayaran` nullable (mis. "TF 14/11 BCA", hanya untuk pembukuan admin), `wajib_ganti_password` boolean default true, `password_sementara_berlaku_sampai` nullable, `dibuat_oleh` nullable, `last_login_at`. **Tidak ada kolom masa aktif.**
**`pantauans`**: `user_id`, `formasi_id`, `catatan` nullable. Unik (`user_id`,`formasi_id`).
**`pengaturans`**: lihat §5.2.
**`email_logs`**: `user_id` nullable, `tipe`, `penyedia`, `status`, `error` nullable, `dikirim_at`.
**`audit_logs`**: `aktor_id`, `aksi`, `target_id`, `rincian` JSON. Tanpa data rahasia.

---

## 7. Fitur dan halaman

### 7.1 Pengunjung (belum login)

- **Beranda**: hero dengan ilustrasi SVG, penjelasan nilai produk, cuplikan tampilan (data tersamarkan, tanpa angka asli), harga, tombol **Chat Admin WhatsApp**.
- **Cara Akses**: 4 langkah bergambar (chat admin → transfer → kirim bukti ke WA → akun dikirim via email), info rekening dari pengaturan.
- **Login**. Tidak ada tombol daftar.

### 7.2 Member (login, akun aktif)

**Peluang Instansi 2024** (`/peluang-instansi`), usable sebelum formasi 2026 terbit:
- Daftar/ranking instansi berdasarkan statistik CPNS 2024: formasi 2024, pelamar 2024, rasio, lencana persaingan, sumber dan tanggal.
- Filter: tipe instansi (pusat/daerah), provinsi (bila ada), tingkat persaingan; cari nama; urut rasio, pelamar, formasi.
- Bila instansi punya formasi aktif tahun ini, tampil tautan "Lihat formasi".
- Tampil label tingkat data dan disclaimer.

**Eksplorasi Formasi** (`/formasi`):
- Filter (opsional, AND, tersimpan di query string):
  - Jenjang pendidikan
  - Jurusan (combobox pencarian, multi pilih)
  - Provinsi → Kabupaten/Kota (bertingkat)
  - Instansi (combobox)
  - Jenis formasi (umum, disabilitas, dll.)
  - Tingkat persaingan
  - Cari teks pada jabatan atau instansi
- Urutan: rasio terendah, kuota terbanyak, pelamar tersedikit, terbaru.
- Paginasi **sisi server**.
- Tiap formasi: instansi, jabatan, lokasi, kuota, syarat pendidikan ringkas, **pelamar 2024**, **rasio**, **lencana persaingan**, label tingkat data, tombol pantau.
- **Kualifikasi kosong**: tampilkan "Kualifikasi mengikuti ketentuan jabatan, lihat persyaratan"; formasi demikian tidak dihitung cocok pada filter jenjang/jurusan kecuali opsi "sertakan formasi tanpa rincian kualifikasi" dinyalakan (default mati).
- **Keadaan kosong CPNS**: bila belum ada formasi CPNS aktif, tampilkan ilustrasi dan arahkan ke Peluang Instansi.

**Detail Formasi** (`/formasi/{id}`): data lengkap, persyaratan, riwayat statistik pelamar (semua snapshot beserta sumber), tautan sumber resmi, disclaimer.
**Daftar Pantauan** (`/pantauan`): simpan, catatan pribadi, bandingkan hingga 4 formasi.
**Akun** (`/akun`): ubah password (wajib saat login pertama), hubungi admin.
**Panduan Singkat**: modal 3 langkah bergambar saat login pertama (bisa dilewati, bisa dibuka lagi dari menu).

### 7.3 Admin (`/admin`)

- **Dashboard**: member aktif/nonaktif, status sinkron BKN terakhir, event baru terdeteksi, batch impor terakhir, **daftar periksa kesiapan** (email terkonfigurasi? WA terisi? rekening terisi? sinkron berhasil?) dengan tautan ke pengaturan terkait.
- **Pengguna**: daftar, cari, filter (aktif/nonaktif), **buat akun** (nama, email, WA, catatan pembayaran), **kirim ulang kredensial**, reset password, **nonaktifkan/aktifkan** (dengan alasan, konfirmasi modal), riwayat audit per pengguna.
- **Sinkron BKN**: status, "Sinkron sekarang" (konfirmasi), riwayat batch, event terdeteksi dan yang diabaikan.
- **Statistik Pelamar**: unggah CSV, pratinjau, laporan baris gagal, template CSV, riwayat, rollback.
- **Data Referensi**: instansi, jabatan, jurusan, wilayah (koreksi pemetaan).
- **Pengaturan**: Email (§5.3), WhatsApp (§5.4), Rekening dan Harga, Situs, Persaingan, Keamanan.

---

## 8. Penanda persaingan

```
rasio = jumlah_pelamar_2024 / jumlah_formasi_2024
```

- Ambang awal (diubah admin, **bukan klaim resmi**): `< 5` Longgar, `5–20` Sedang, `> 20` Ketat. Angka rasio selalu tampil di samping lencana.
- Yang ditampilkan adalah **riwayat persaingan 2024**, bukan prediksi. Teks: "Berdasarkan data pelamar 2024, bukan jaminan tahun ini".
- Tidak ada data → lencana "Belum ada data". Jangan menebak dari instansi lain.
- Lencana tidak mengandalkan warna saja: sertakan ikon dan teks.

---

## 9. Alur akun (inti bisnis)

```
Calon member → chat Admin (WhatsApp)
Calon member → transfer manual → kirim bukti ke WhatsApp
Admin → Buat Pengguna (nama, email, WA, catatan pembayaran)
Sistem → password sementara acak → hash → simpan
Sistem → antrean → email: email login + password sementara + tautan login
Pengguna login → dipaksa ganti password → akses penuh
Admin kapan saja → Nonaktifkan (dengan alasan) bila tidak bayar
```

Aturan:
- Password sementara: ≥12 karakter acak (CSPRNG), hanya ada di email; tidak tampil di UI, tidak di log. Berlaku 7 hari. `wajib_ganti_password = true`.
- **Tidak ada kedaluwarsa otomatis.** Akun hanya berubah status oleh tindakan admin.
- Email dikirim lewat antrean dengan retry dan backoff. Bila gagal atau belum ada penyedia email, pengguna tetap dibuat; admin melihat status email di daftar pengguna dan menekan "Kirim ulang kredensial" (membuat password sementara baru). Bila pengaturan "Tampilkan password sementara sekali" aktif, admin boleh menampilkannya satu kali lewat konfirmasi modal dan tercatat di audit.
- Login dibatasi (rate limit per email dan IP). Pesan galat tidak membocorkan apakah email ada.
- Akun nonaktif: login ditolak dengan modal ramah ("Akun Anda sedang nonaktif. Hubungi admin") berisi tombol WhatsApp.
- Sesi: satu sesi aktif per akun (bisa dimatikan di pengaturan).
- Isi email: sapaan, email login, password sementara, tautan login, kontak admin, imbauan ganti password. Template Markdown Mailable berbahasa Indonesia, tampilan bersih, responsif.

---

## 10. Sinkron formasi dari BKN (otomatis)

### 10.1 Adapter

- Interface Application `BknFormasiSource`; implementasi Infrastructure `HttpBknFormasiSource` memakai `Http` Laravel (`timeout` 120 dtk, `retry` 3× backoff).
- URL endpoint disimpan di `pengaturans` (default §3), mudah diganti admin bila BKN memindahkan API.
- Header: `User-Agent: SICASN/1.0 (+kontak dari pengaturan)`, `Accept: application/json`. Tanpa penyamaran.
- Validasi skema respons: `error === false` dan `data` array. Bila field wajib hilang, batch gagal, **data lama tetap dipakai**, jangan hapus apa pun.

### 10.2 Jadwal dan kesopanan

- `sicasn:sync-formasi` dijadwalkan lewat scheduler Laravel, **1× per hari** (default 02:00 WIB). Selama pendaftaran bisa dinaikkan sampai 1× per 6 jam lewat pengaturan, tidak lebih sering.
- Satu request per sinkron. Hash respons disimpan; bila sama, lewati pemrosesan.
- Bila 403, 429, atau tantangan WAF: berhenti, catat, coba lagi di jadwal berikutnya. **Jangan menembus.**

### 10.3 Pemrosesan (use case `SyncFormasiFromBkn`)

1. Buka `ImportBatch(sinkron_bkn)`.
2. Catat semua `nama_event` dan jenis pengadaan yang teramati ke `event_terdeteksis` (termasuk yang diabaikan).
3. **Proses hanya jenis pengadaan aktif** (`config('sicasn.jenis_pengadaan_aktif') = ['cpns']`). Baris PPPK dilewati dan dihitung sebagai "diabaikan" di ringkasan.
4. Untuk tiap baris CPNS: normalisasi instansi, jabatan, lokasi (§10.4), kualifikasi (di bawah), persyaratan; upsert `formasis` per `external_id`; perbarui `last_seen_at`.
5. Formasi aktif yang tidak muncul pada sinkron sukses → `ditarik` (bukan hapus), hanya bila respons sah dan jumlah baris ≥ 50% sinkron sukses sebelumnya.
6. Event CPNS baru yang belum diberitahukan → tandai untuk notifikasi admin (§11.3).
7. Tutup batch dengan ringkasan (baru/diubah/ditarik/diabaikan/gagal). Satu record rusak tidak menggagalkan batch (catat di `import_errors`). Idempoten.

**Parsing `nama_pendidikan`** (mengikuti logika portal BKN): pisahkan dengan ` / `; tiap item `<JENJANG> <JURUSAN>`, token pertama jenjang, sisanya jurusan; `SEMUA JURUSAN` → `semua_jurusan`. Normalisasi `D-IV→D4`, `S-1→S1`, dst., uppercase, rapikan spasi. Bila kosong → `kualifikasi_tersedia = false`; **jangan menurunkan jurusan dari nama jabatan**.

### 10.4 Normalisasi lokasi

Awalan `Kab.`/`Kota`/`Prov.` dicocokkan ke `wilayahs`. Nama satuan kerja → `wilayah_id = null`, `lokasi_teks` ditampilkan apa adanya. Formasi yang tidak terpetakan tetap tampil tetapi tidak muncul pada filter provinsi; jumlahnya ditampilkan di panel admin.

---

## 11. Data pelamar CPNS 2024: strategi bertingkat A + B + C

Tidak ada sumber resmi terbuka untuk pelamar 2024 per jabatan. Sistem menyimpan semua tingkat dan menampilkan yang paling rinci, **dengan label tingkatnya**.

| Tingkat | Cakupan | Sumber | Pelaksana |
|---|---|---|---|
| **A** | Per **instansi** | Rilis resmi BKN dan pemberitaan yang mengutip BKN | Agent, Fase 3 |
| **B** | Per **jabatan** | Hasil seleksi administrasi 2024 yang dipublikasikan tiap instansi | Agent, Fase 5, bertahap |
| **C** | Per jabatan, lengkap | Permohonan informasi publik ke PPID BKN | Pemilik mengirim surat; sistem menyiapkan format impor |

### 11.1 Tingkat A (Fase 3)

- Kumpulkan angka dari sumber resmi BKN atau arsipnya; bila hanya lewat media, catat **nama media, URL, tanggal snapshot** dan tandai `keyakinan = turunan`.
- Validasi silang: total nasional 17 Sep 2024 = 3.963.832. Angka dengan snapshot berbeda disimpan sebagai baris terpisah.
- Simpan sebagai `database/seeders/data/statistik_instansi_2024.csv` (dengan kolom sumber dan tanggal), dimuat `StatistikPelamarSeeder` lewat use case impor yang sama dengan CSV admin.
- **Dilarang mengisi angka dari ingatan.**

### 11.2 Tingkat B (Fase 5)

- Prioritas: instansi dengan volume terbesar, lalu pemda besar.
- Alur: temukan PDF/halaman hasil seleksi administrasi resmi → `pdftotext -layout` → **hitung baris peserta per jabatan** → simpan hanya agregat (jabatan, jumlah, sumber, tanggal). Label **"lolos administrasi"**, bukan total pendaftar; `keyakinan = turunan`.
- Dilarang menyimpan nama/NIK. Berkas mentah di luar repo dan dihapus setelah diagregasi.
- Parser per format dengan uji; hentikan bila format tidak konsisten. Catat progres di `docs/data-coverage.md`.

### 11.3 Tingkat C dan notifikasi event

- Surat: `docs/ppid-bkn-request.md`. Format impor: template CSV di admin.
- Data C menggantikan A/B untuk kunci yang sama.
- **Notifikasi event**: saat sinkron menemukan event dengan baris CPNS yang belum pernah dilihat, dashboard admin menampilkan modal "Event CPNS baru terdeteksi" dan (opsional) email ke admin.

### 11.4 Aturan tampilan

Pencocokan formasi ↔ statistik 2024: (1) instansi + kode/nama jabatan ternormalisasi (+ lokasi) → **per jabatan** (B/C); (2) instansi saja → **rata-rata instansi** (A) dengan label "bukan per jabatan"; (3) tidak ada → "Belum ada data". Selalu tampilkan sumber, tanggal snapshot, tingkat data. Pencocokan nama jabatan antar-tahun konservatif; bila ragu, jangan cocokkan.

---

## 12. Impor CSV (admin)

- Unggah → validasi seluruh baris → pratinjau (berhasil/gagal) → konfirmasi modal → proses lewat antrean.
- Validasi: kolom wajib, angka, `jumlah_pelamar ≥ 0`, tanggal valid, instansi dikenali (saran fuzzy; yang baru dibuat setelah konfirmasi).
- Hasil: ringkasan di modal, unduh CSV baris gagal, `ImportBatch` tercatat, **rollback** per batch (konfirmasi modal).
- Tangani BOM, pemisah `;`/`,`, encoding Windows-1252. Batasi ukuran dan jenis berkas.
- Format kolom: lihat `docs/ppid-bkn-request.md` §3.

---

## 13. Desain, UX, dan responsivitas

### 13.1 Prinsip

Bersih, lapang, mudah dipahami orang awam, modern. Satu warna aksen, banyak ruang kosong, radius seragam, tipografi **Roboto** (400/500/700, `font-display: swap`) lewat `@fontsource/roboto` yang di-bundle. Mode terang dan gelap dengan token CSS variables. Bahasa antarmuka Indonesia yang ramah dan lugas.

### 13.2 Responsif (wajib diuji di tiga lebar)

| Perangkat | Lebar uji | Perilaku |
|---|---|---|
| HP | 360–430 px | Navigasi bawah atau menu `Sheet`; filter dalam drawer dengan penghitung filter aktif; formasi sebagai **kartu**; target sentuh ≥ 44 px; tanpa scroll horizontal |
| Tablet | 768–1024 px | Sidebar dapat dilipat; filter sebagai panel dapat dibuka; grid 2 kolom; tabel hanya bila muat |
| Desktop | ≥ 1280 px | Sidebar tetap; filter di panel samping; tabel padat dengan kolom bisa diurutkan; konten maksimal ±1280 px |

Admin pun responsif (tabel pengguna menjadi kartu di HP). Hindari teks terpotong; gunakan *truncate* dengan tooltip.

### 13.3 Sistem dialog (pengganti toast)

- Komponen: `DialogProvider` + hook `useDialog()` di `components/dialog/`, dibangun di atas shadcn `AlertDialog`, tampil **di tengah layar**.
- API: `sukses`, `gagal`, `galat`, `peringatan`, `info`, `konfirmasi(...) → Promise<boolean>`, `konfirmasiKetik(...)` (pengguna mengetik kata kunci untuk aksi berisiko).
- Setiap dialog punya **ilustrasi/ikon** sesuai jenis, judul singkat, pesan jelas, dan tombol aksi yang spesifik (bukan sekadar "OK").
- Pesan server dikirim lewat flash Inertia (`dialog`) dan dibuka otomatis oleh `DialogProvider`.
- Galat validasi form ditampilkan **inline di bawah field**; dialog dipakai untuk hasil akhir (sukses/gagal), galat sistem (4xx/5xx, koneksi), dan aturan bisnis.
- **Wajib konfirmasi modal** untuk: nonaktifkan/aktifkan akun, kirim ulang kredensial, reset password, tampilkan password sementara, rollback impor, sinkron paksa, ganti penyedia email aktif, simpan pengaturan email/WA, hapus data apa pun, keluar dari form berisi perubahan belum tersimpan.
- Fokus terkunci di dialog, `Esc` menutup (kecuali konfirmasi berisiko), dukungan pembaca layar, animasi halus yang menghormati `prefers-reduced-motion`.

### 13.4 Ilustrasi SVG

- Folder `components/ilustrasi/`; tiap ilustrasi adalah **komponen React SVG inline** (tanpa gambar eksternal), ≤ ~8 KB, `role="img"` dengan `title` atau `aria-hidden` bila dekoratif.
- Gaya: flat dua-nada dengan satu warna aksen, bentuk membulat, tanpa gradien berat. Warna memakai CSS variables (`--ilustrasi-*`) agar otomatis cocok terang/gelap.
- Daftar minimal: `IlustrasiBeranda` (hero), `IlustrasiCariFormasi`, `IlustrasiKosong`, `IlustrasiSukses`, `IlustrasiGagal`, `IlustrasiGalat`, `IlustrasiPeringatan`, `IlustrasiKonfirmasi`, `IlustrasiLogin`, `IlustrasiAkunNonaktif`, `IlustrasiLangkahChat`, `IlustrasiLangkahTransfer`, `IlustrasiLangkahEmail`, `IlustrasiLangkahMasuk`, `IlustrasiNotFound`, `IlustrasiServerError`.
- Dipakai pada: hero, keadaan kosong, setiap jenis dialog, halaman Cara Akses, login, panduan singkat, halaman galat.

### 13.5 Ikon

Hanya `react-icons` (disarankan `react-icons/lu` agar seragam dengan shadcn). Setelah menambahkan komponen shadcn, ganti impor `lucide-react` di dalamnya dengan padanan `react-icons`. Ikon dekoratif `aria-hidden`; tombol ikon wajib `aria-label`.

### 13.6 Komponen shadcn

`Button`, `Input`, `Textarea`, `Select` (elemen native yang diberi gaya, ramah layar sentuh), `Label`, `Sheet`, `AlertDialog`, `Badge`, `Table`, `Card`, `Alert`, `Switch`. Komponen tambahan (`Combobox`, `Tabs`, `Skeleton`, `Tooltip`) ditulis saat dibutuhkan. Formulir memakai `useForm` Inertia dengan validasi sisi server dan galat inline (`BidangForm`); `react-hook-form` dan `zod` tidak dipakai. **Tanpa** `sonner`/toast.

### 13.7 Aksesibilitas dan performa

Label form, urutan fokus, `aria` pada combobox, navigasi keyboard, kontras WCAG AA, status tidak hanya lewat warna. Inertia partial reload (`only`), debounce pencarian, opsi filter di-cache, skeleton saat memuat, keadaan kosong yang membantu, keadaan galat yang jelas.

---

## 14. Keamanan

- Password di-hash (bcrypt/argon2 bawaan Laravel). Password sementara CSPRNG. Rahasia pengaturan dienkripsi (`Crypt`).
- Rate limit login, impor, dan uji email. CSRF aktif. Policy/Gate untuk semua aksi admin; middleware peran.
- Unggahan divalidasi, disimpan di disk privat, nama diacak.
- Header keamanan (CSP yang cocok dengan Vite/Inertia, `X-Frame-Options`, dll.).
- Tidak ada kredensial di repo. Log tidak boleh memuat password (aturan `LOG_RAHASIA`).
- Kebijakan privasi singkat: data pribadi pengguna hanya nama, email, nomor WA; tidak dibagikan.
- Audit log untuk aksi sensitif.

---

## 15. Pengujian (Pest)

- **Unit**: Value Object, parsing kualifikasi, rasio dan tingkat persaingan, pencocokan statistik ↔ formasi, normalisasi lokasi, pemetaan `jenis_pengadaan`.
- **Feature**: pencarian formasi dengan kombinasi filter; alur buat pengguna → email terkirim (`Mail::fake`) → login pertama → ganti password; kontrol akses admin/member; akun nonaktif ditolak; pengaturan email (simpan terenkripsi, rahasia tidak bocor ke respons, email uji, failover); impor CSV (valid/invalid/rollback); halaman Peluang Instansi.
- **Sinkron**: `Http::fake` dengan fixture dan kasus rusak (skema berubah, kosong, 403, timeout); baris PPPK diabaikan; idempoten; pengaman 50%; event CPNS baru terdeteksi.
- **Arch**: Domain tidak mengimpor `Illuminate`/Eloquent; Eloquent hanya di Infrastructure; controller tidak memakai model.
- **Frontend**: Vitest + Testing Library untuk PanelFilter, KartuFormasi, DialogProvider; Playwright untuk alur filter dan tangkapan layar **360, 768, dan 1280 px** pada halaman utama.

---

## 16. Pemeriksaan kualitas (tanpa CI)

Tidak ada CI. Semua pemeriksaan dijalankan **lokal** dan wajib hijau sebelum commit.

```
python3 CekKonvensi.py
composer check
npm run check
```

- `composer check`: Pint (cek) dan Pest. **Larastan belum terpasang**: di sandbox Claude paket `phpstan/phpstan` tidak bisa diunduh (hanya tersedia sebagai dist dari GitHub API yang diblokir). Pasang di mesin pemilik dengan `composer require --dev larastan/larastan`, tambahkan konfigurasi `phpstan.neon` (level tertinggi yang realistis), lalu tambahkan `vendor/bin/phpstan analyse` ke skrip `check`.
- `npm run check`: `tsc --noEmit`, ESLint, Vitest.
- Aktifkan hook sekali: `git config core.hooksPath .githooks`. Hook `pre-commit` menjalankan `python3 CekKonvensi.py --staged` dan menggagalkan commit bila ada pelanggaran.
- **Dilarang** menambahkan `.github/workflows` atau konfigurasi CI lain (diperiksa).

### `CekKonvensi.py`

| Opsi | Fungsi |
|---|---|
| (tanpa opsi) | Periksa seluruh proyek |
| `<path> ...` | Periksa berkas/folder tertentu |
| `--staged` | Periksa berkas yang di-stage git |
| `--ketat` | Peringatan dianggap pelanggaran |
| `--bersihkan` | Hapus semua komentar dari berkas kode proyek (untuk membersihkan scaffold) |
| `--aturan` | Tampilkan daftar aturan |

Aturan: `KOMENTAR`, `STRICT_TYPES`, `DOMAIN_MURNI`, `APPLICATION_MURNI`, `DELIVERY_TIPIS`, `ELOQUENT_DI_INFRA`, `FUNGSI_DILARANG`, `ENV_HANYA_DB`, `IKON_REACT_ICONS`, `MODAL_BUKAN_TOAST`, `FONT_ROBOTO`, `TANPA_CI`, `LOG_RAHASIA`, `UKURAN_BERKAS`. Berkas bawaan generator tidak diperiksa: `vendor`, `node_modules`, `storage`, `bootstrap/cache`, `public/build`, `resources/js/components/ui`, `resources/js/actions|routes|wayfinder`, `*.d.ts`, `docs/`.

Keterbatasan yang perlu diketahui: pendeteksi komentar adalah pemindai sederhana (belum menangani heredoc/nowdoc PHP; regex JavaScript dideteksi dengan heuristik). Bila ada false positive pada kode sah, **perbaiki skrip dan ujinya**, jangan mematikan aturan. Jika perlu perilaku baru, ubah `CekKonvensi.py` dan dokumen ini bersamaan.

---

## 17. Deployment (target: tinggal deploy)

Dua jalur, keduanya hanya perlu `.env` berisi database:

1. **Hosting biasa** (termasuk Hostinger): unggah kode dengan `public/build` sudah dibangun, atur satu **cron tiap menit**: `php artisan schedule:run`. Scheduler juga menjalankan `queue:work --stop-when-empty --max-time=50` tiap menit, sehingga **tidak butuh proses antrean permanen**. Email dapat tertunda ±1 menit.
2. **Docker**: `docker-compose` dengan `app` (php-fpm), `web` (nginx), `mysql`, `queue` (worker), `scheduler`, healthcheck.

`php artisan sicasn:install` (interaktif, aman diulang):
1. Memeriksa PHP, ekstensi, izin tulis, dan koneksi database.
2. Membuat `APP_KEY` bila belum ada, menjalankan migrasi.
3. Menanyakan **nama, email, dan password admin pertama** (tidak lewat env) dan URL situs; menyimpan URL ke pengaturan.
4. Mengisi pengaturan awal dan peran.
5. Memuat seed statistik instansi 2024 (§11.1).
6. Melakukan sinkron BKN pertama dan mencetak ringkasannya (termasuk "belum ada formasi CPNS" bila demikian).
7. Mencetak daftar periksa: scheduler aktif? email terkonfigurasi? WA terisi? Dan mengarahkan admin ke dashboard untuk mengatur email dan WhatsApp.

Dokumentasi `docs/deployment.md`: kedua jalur, cron, backup MySQL harian, pembaruan, pemecahan masalah. Observabilitas: status sinkron, antrean gagal, dan email gagal tampil di dashboard admin.

---

## 18. Rencana kerja bertahap

Kerjakan berurutan. Setiap fase selesai bila **kriteria penerimaan** terpenuhi dan `CekKonvensi.py`, `composer check`, `npm run check` hijau. Commit kecil dan bermakna.

**Fase 0, Fondasi**
- Proyek Laravel 13 + MySQL + Inertia React + shadcn; struktur `src/` + PSR-4; Pest, Pint, Larastan, ESLint, Vitest; arch test.
- Terapkan konvensi pada scaffold: jalankan `python3 CekKonvensi.py --bersihkan`, ganti lucide/sonner/font bawaan (Roboto lokal, react-icons, AlertDialog), hapus `.github`, rapikan `config/*` agar hanya `APP_KEY` dan `DB_*` dari env, driver database untuk session/queue/cache, aktifkan hook.
- Dasar desain: token CSS, Roboto, tata letak responsif, `DialogProvider`, beberapa ilustrasi awal.
- ✅ Semua pemeriksaan hijau; beranda tampil benar di 360/768/1280 px.
- **Status: selesai (14 Nov 2026)**, dengan satu pengecualian: Larastan (lihat §16). Terverifikasi: Pest 15 tes (termasuk arch), Vitest 8 tes, `tsc`, ESLint, Pint, `CekKonvensi.py` hijau; tangkapan layar 390/820/1366 px terang dan gelap tanpa scroll horizontal, font Roboto aktif, dialog modal tepat di tengah.
- Catatan sandbox Claude (bukan masalah proyek): `composer install` perlu `--prefer-source` karena unduhan dist GitHub diblokir; registry shadcn tidak terjangkau sehingga `components/ui` ditulis manual mengikuti pola shadcn dengan paket Radix. Di mesin biasa, `composer install` dan `npx shadcn@latest add ...` bekerja normal.

**Fase 1, Identity dan Pengaturan**
- Login, peran, policy; admin Pengguna (buat, kirim ulang, reset, nonaktifkan/aktifkan, audit); **pengaturan Email multi-penyedia (termasuk Hostinger) + email uji + failover**; pengaturan WhatsApp, rekening, harga; email kredensial otomatis via antrean; ganti password wajib; satu-sesi; beranda dan Cara Akses dengan tombol WA dinamis.
- ✅ Admin mengatur Hostinger SMTP di dashboard → email uji berhasil → membuat pengguna → email masuk antrean → login → dipaksa ganti password. Akun nonaktif ditolak dengan modal ramah. Tidak ada kolom/logika masa aktif.
- **Status: selesai (15 Nov 2026).** Terverifikasi: Pest 102 tes (unit, fitur, arsitektur), Vitest 21 tes, `tsc`, ESLint, Pint, `CekKonvensi.py` hijau. Uji menyeluruh di browser dengan server SMTP asli (`tests/e2e/`, lihat `docs/pengujian-e2e.md`): 40 pemeriksaan, semuanya lulus (satu pemeriksaan tab `wa.me` gagal hanya karena sandbox tanpa internet; URL-nya terbukti benar lewat intersepsi `window.open`). Tangkapan layar 7 halaman admin x 390/820/1366 px x terang/gelap tanpa scroll horizontal.
- Perintah membuat admin pertama sudah ada: `php artisan sicasn:buat-admin` (interaktif, atau dengan opsi `--nama --email --password`). `sicasn:install` yang lengkap tetap di Fase 7 dan akan memanggilnya.
- **Tidak dikerjakan di Fase 1 (sengaja ditunda):** tema email kustom, "tampilkan password sementara sekali", notifikasi event CPNS (Fase 2), dan email uji memakai isian form yang belum disimpan (saat ini email uji memakai pengaturan yang sudah tersimpan).
- Catatan teknis penting: kata sandi sementara hanya ada di payload job yang dienkripsi (`ShouldBeEncrypted`) dan di email, tidak pernah di log, audit, atau database selain hash. Pengujian guard autentikasi di Pest perlu `auth()->forgetGuards()` setelah mengubah database langsung, karena klien tes menyimpan objek pengguna di memori.

**Fase 2, Sinkron BKN (CPNS)**
- Skema §6.1/§6.3, adapter dan use case §10, event terdeteksi, wilayah, scheduler, panel status.
- ✅ Dari fixture dan API nyata: baris PPPK diabaikan dan terhitung, nol formasi CPNS ditangani mulus, event baru memicu notifikasi, idempoten, aman terhadap respons rusak.

**Fase 3, Statistik 2024 dan Peluang Instansi**
- Skema §6.2, use case impor, impor CSV + rollback, seed tingkat A, lencana persaingan, halaman **Peluang Instansi 2024**.
- ✅ Halaman berfungsi tanpa formasi 2026; setiap angka menampilkan sumber, tanggal, dan tingkat data.

**Fase 4, Eksplorasi Formasi CPNS**
- Query + read model, `/formasi` dan detail, semua filter, paginasi server, UI tiga ukuran layar, aturan kualifikasi kosong, keadaan kosong CPNS.
- ✅ Filter gabungan benar; URL bisa dibagikan; p95 query < 300 ms pada 100 ribu baris uji. Diuji dengan data uji lokal yang **tidak** ikut seeding production.

**Fase 5, Tingkat B**
- Parser hasil seleksi administrasi instansi besar, `docs/data-coverage.md`.
- ✅ Beberapa instansi besar tercakup per jabatan dengan sumber; tanpa data pribadi tersimpan.

**Fase 6, Pantauan dan perbandingan**
- ✅ Simpan, catatan, bandingkan hingga 4 formasi.

**Fase 7, Pengerasan dan rilis**
- `sicasn:install` lengkap, `docs/deployment.md`, audit keamanan (`/security-review`), uji aksesibilitas, panduan singkat, backup.
- ✅ Deploy bersih di mesin baru hanya dengan `.env` database dan perintah dokumentasi.

### Izin jaringan agent
Host yang sudah diizinkan di lingkungan: `sscasn.bkn.go.id`, `api-sscasn.bkn.go.id`, `api-rekrutmen.bkn.go.id`, `bkn.go.id`, `www.bkn.go.id`, `data.go.id`. Untuk Fase 3 dan 5, agent perlu menambah host sumber lewat `update_environment` dengan host eksak per kebutuhan nyata dan alasan jelas.

---

## 19. Keputusan yang masih terbuka (tidak menghalangi Fase 0–2; default sudah ditetapkan)

1. **Sumber referensi wilayah**: agent memilih dataset resmi/terbuka berlisensi jelas dan mencatatnya. Default: cari yang mencantumkan kode Kemendagri.
2. **Satu sesi per akun**: default aktif, dapat dimatikan di pengaturan.
3. **Tampilkan password sementara sekali**: default mati.
4. **Domain dan hosting**: diputuskan saat Fase 7. Rancangan mendukung hosting biasa dan Docker.
5. **Fitur tahap berikutnya** (belum dikerjakan): PPPK, checklist berkas, pengingat jadwal, latihan SKD.

Harga, rekening, nomor WhatsApp, dan kredensial email **bukan keputusan pengembangan**; semuanya diisi admin di dashboard setelah deploy.

---

## 20. Cara agent bekerja di repo ini

- Baca dokumen ini dulu. Bila kenyataan berbeda (mis. API BKN berubah), **perbarui dokumen** dan jelaskan; jangan diam-diam menyimpang.
- Verifikasi versi dan perintah pustaka lewat dokumentasi resmi sebelum menulis (Laravel 13, starter kit, shadcn, Inertia, `@fontsource/roboto`, penyedia email). Jangan mengandalkan ingatan.
- Kerjakan per fase. Jalankan `python3 CekKonvensi.py` sebelum setiap commit.
- Jangan membuat pull request kecuali diminta pemilik. Jangan menambah fitur di luar dokumen tanpa persetujuan.
- Jangan menulis komentar dalam kode, termasuk berkas yang dihasilkan generator yang Anda sentuh.
- Laporkan hasil dengan jujur: apa yang lolos, gagal, atau dilewati.
