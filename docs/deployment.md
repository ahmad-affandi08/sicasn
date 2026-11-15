# Deployment SICASN

Dua jalur, keduanya hanya membutuhkan `.env` berisi `APP_KEY` dan `DB_*`. Semua pengaturan lain (email, WhatsApp, rekening, harga) diisi dari dashboard admin setelah instalasi.

> **Status verifikasi.** Alur `php artisan sicasn:install` sudah diuji otomatis (Pest) dan dijalankan dari salinan bersih tanpa `.env` terhadap API BKN sungguhan, memakai SQLite. Yang **belum** bisa diuji di lingkungan pengembangan: MySQL sungguhan, Docker (build dan compose), nginx, dan hosting Hostinger. Berkas Docker dan skrip `bin/` sudah diperiksa sintaksnya saja. Jalankan jalur pilihan Anda sekali di mesin percobaan sebelum dipakai produksi, dan laporkan bila ada langkah yang tidak cocok.

## 1. Prasyarat

| Kebutuhan | Keterangan |
|---|---|
| PHP | 8.3 atau lebih baru, dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `curl`, `dom`, `xml`, `fileinfo`, `tokenizer`, `ctype` |
| MySQL | 8.x, karakter `utf8mb4` |
| Keluar ke internet | Server harus bisa membuka `https://api-rekrutmen.bkn.go.id` (sinkron formasi) dan server SMTP penyedia email Anda |
| HTTPS | Wajib untuk produksi. Cookie sesi dan header HSTS mengandalkannya |

`php artisan sicasn:install` memeriksa semua butir PHP di atas dan koneksi database, jadi Anda tidak perlu menghafalnya.

## 2. Jalur A: hosting biasa (termasuk Hostinger)

Hosting biasa umumnya tidak punya Node dan sering tidak punya Composer, jadi paket rilis dibangun di komputer Anda.

### 2.1 Bangun paket rilis (di komputer Anda)

Prasyarat lokal: PHP 8.3, Composer, Node 22, `zip`.

```
sh bin/buat-rilis.sh
```

Skrip membangun aset frontend, menyalin kode ke folder sementara, memasang pustaka PHP **tanpa paket pengembangan** di salinan itu (folder kerja Anda tidak diubah), lalu menghasilkan `sicasn-rilis-<tanggal>.zip`. Berkas ini sudah berisi `public/build` dan `vendor`.

### 2.2 Unggah dan atur

1. Buat database MySQL dan pengguna di panel hosting. Catat host, nama database, pengguna, dan kata sandi.
2. Pilih PHP 8.3 untuk domain Anda dan pastikan ekstensi pada tabel di atas aktif.
3. Unggah dan ekstrak zip. Arahkan **document root** domain ke folder `public` (di panel hosting, pengaturan domain atau situs). Bila panel Anda tidak memungkinkannya, ikuti panduan Laravel untuk memindahkan isi `public` ke `public_html` dan menyesuaikan jalur di `index.php`; periksa dokumentasi penyedia Anda.
4. Salin `.env.example` menjadi `.env` dan isi data database:

   ```
   APP_KEY=
   DB_CONNECTION=mysql
   DB_HOST=...
   DB_PORT=3306
   DB_DATABASE=...
   DB_USERNAME=...
   DB_PASSWORD=...
   ```

   Biarkan `APP_KEY` kosong; instalasi mengisinya.
5. Jalankan instalasi lewat terminal SSH (atau fitur terminal panel hosting):

   ```
   php artisan sicasn:install
   ```

   Perintah akan memeriksa lingkungan, membuat `APP_KEY`, menjalankan migrasi, menanyakan nama, email, dan kata sandi admin pertama serta alamat situs, memuat statistik pelamar 2024, melakukan sinkron BKN pertama, dan mencetak daftar periksa. Aman diulang.

   Tanpa SSH: jalankan perintah yang sama dari komputer Anda dengan `.env` yang menunjuk ke database hosting (bila MySQL hosting mengizinkan koneksi jarak jauh), atau gunakan jalur Docker di server sendiri.
6. Atur **satu cron tiap menit** di panel hosting (ganti jalur sesuai server Anda):

   ```
   * * * * * cd /jalur/ke/sicasn && php artisan schedule:run >> /dev/null 2>&1
   ```

   Scheduler Laravel menjalankan antrean email tiap menit (`queue:work --stop-when-empty`) dan sinkron BKN harian, jadi **tidak perlu proses antrean permanen**. Email dapat tertunda sampai sekitar satu menit.

### 2.3 Selesaikan di dashboard

Masuk di `/masuk` sebagai admin pertama. Dashboard menampilkan daftar periksa kesiapan dan kesehatan sistem:

1. **Pengaturan > Email**: pilih Hostinger (atau penyedia lain), isi kredensial SMTP, kirim email uji, simpan.
2. **Pengaturan > WhatsApp**: nomor admin dan templat pesan.
3. **Pengaturan > Pembayaran**: harga dan rekening.
4. **Pengaturan > Situs**: alamat situs (dipakai pada tautan login di email kredensial).
5. Pastikan kartu **Penjadwal (cron)** berstatus *Berjalan* beberapa menit setelah cron dipasang.

## 3. Jalur B: Docker

Layanan: `app` (php-fpm), `web` (nginx), `mysql`, `queue` (worker), `scheduler`. Semuanya punya healthcheck atau bergantung pada layanan yang sehat.

```
cp .env.example .env
```

Isi `.env`:

```
APP_KEY=
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=sicasn
DB_USERNAME=sicasn
DB_PASSWORD=<kata sandi kuat>
```

Compose menimpa `DB_HOST` menjadi `mysql` untuk semua layanan aplikasi. Kata sandi root MySQL dibuat acak dan tidak dipakai aplikasi.

```
docker compose build
docker compose run --rm app php artisan key:generate --show
```

Tempel hasilnya ke `APP_KEY=` di `.env`, lalu:

```
docker compose up -d mysql
docker compose run --rm app php artisan sicasn:install
docker compose up -d
```

Situs tersedia di `127.0.0.1:8080`, sengaja **hanya dapat dijangkau dari server itu sendiri**. **Pasang HTTPS di depannya** (reverse proxy di host, Caddy, Traefik, Cloudflare Tunnel, dan sebagainya) dan teruskan ke `127.0.0.1:8080`. Aplikasi mempercayai header `X-Forwarded-*` hanya dari alamat privat dan loopback (`127.0.0.1`, `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`), yang mencakup jaringan Docker. Karena itu jangan membuka port `web` langsung ke internet (ubah `127.0.0.1:8080:80` menjadi `8080:80`) tanpa proxy yang menimpa header `X-Forwarded-For`: klien dapat memalsukan alamat IP-nya dan melewati pembatas laju per IP. Pembatas login tetap memiliki batas per email (20 percobaan gagal per 15 menit) yang tidak bergantung pada IP.

Catatan:

- `app` menjalankan `php artisan migrate --force` dan `php artisan sicasn:muat-soal` setiap mulai, jadi pembaruan versi otomatis memigrasikan database dan memperbarui bank soal latihan.
- Volume `penyimpanan` menyimpan `storage` (log, pratinjau impor, cache berkas) dan `data-mysql` menyimpan database. Jangan hapus keduanya.
- `scheduler` menjalankan `schedule:work` dan `queue` memproses antrean terus-menerus. Scheduler juga menjalankan `queue:work --stop-when-empty` tiap menit; keduanya aman berjalan bersamaan karena setiap pekerjaan hanya diambil satu pekerja.
- Log: `docker compose logs -f app queue scheduler`.

## 4. HTTPS, header keamanan, dan proxy

Aplikasi mengirim header berikut pada setiap respons halaman: `Content-Security-Policy` (skrip hanya dari asal sendiri dengan nonce acak per permintaan, tanpa `unsafe-eval`, tanpa sumber font eksternal), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, dan `Strict-Transport-Security` bila permintaan lewat HTTPS. Jangan menambahkan kebijakan yang bertabrakan di server web; bila proxy Anda menambah header sendiri, pastikan CSP tidak ganda.

Bila situs berada di belakang proxy di luar rentang privat di atas, alamat IP klien yang dipakai pembatas laju login akan tampak sebagai alamat proxy. Dalam kasus itu ubah daftar `trustProxies` di `bootstrap/app.php` sesuai proxy Anda.

## 5. Cadangan MySQL harian

Skrip `bin/cadangkan-database.sh` membaca `DB_*` dari `.env`, membuat `cadangan/sicasn-<waktu>.sql.gz` (izin 600), dan menghapus cadangan lebih tua dari 14 hari (argumen kedua mengubahnya). Membutuhkan `mysqldump`.

Cron harian pukul 01:30:

```
30 1 * * * sh /jalur/ke/sicasn/bin/cadangkan-database.sh /jalur/aman/di-luar-web >> /dev/null 2>&1
```

Simpan cadangan **di luar folder web** dan salin berkala ke tempat lain (penyimpanan awan atau mesin berbeda). Panel hosting biasanya juga menyediakan cadangan otomatis; aktifkan keduanya.

Docker:

```
docker compose exec -T mysql sh -c 'mysqldump --single-transaction --no-tablespaces -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' | gzip > cadangan-$(date +%F).sql.gz
```

Pulihkan:

```
gunzip -c cadangan-2026-11-15.sql.gz | mysql -h HOST -u PENGGUNA -p NAMA_DATABASE
```

Docker: `gunzip -c berkas.sql.gz | docker compose exec -T mysql sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'`.

Cadangkan juga berkas `.env`, terutama `APP_KEY`. Tanpa kunci itu, kata sandi SMTP yang tersimpan terenkripsi di database tidak bisa dibuka dan harus diisi ulang.

Uji pemulihan sedikitnya sekali sebelum Anda membutuhkannya.

## 6. Pembaruan

Hosting biasa:

1. Bangun paket rilis baru (bagian 2.1).
2. Unggah dan timpa kode. **Jangan menimpa** `.env` dan `storage/`.
3. `php artisan migrate --force`
4. `php artisan sicasn:muat-soal` (memperbarui bank soal latihan SKD bila berkasnya berubah; aman diulang)
5. `php artisan optimize:clear`

Docker:

```
git pull
docker compose build
docker compose up -d
```

Migrasi dan pembaruan bank soal latihan berjalan otomatis. Periksa dashboard admin sesudahnya.

## 7. Pemecahan masalah

| Gejala | Penyebab yang umum | Tindakan |
|---|---|---|
| Halaman putih atau 500 | Izin tulis `storage` atau `bootstrap/cache`, `APP_KEY` kosong | `php artisan sicasn:install` mencetak butir yang gagal; lihat `storage/logs/laravel.log` |
| `Koneksi database` gagal saat instal | `DB_*` salah, host tidak mengizinkan koneksi, `pdo_mysql` tidak aktif | Periksa `.env`; di Docker `DB_HOST` harus `mysql` |
| Email kredensial tidak terkirim | Email belum diatur, kata sandi SMTP salah, cron mati | Dashboard: kartu Kesehatan sistem (email gagal, antrean gagal). Kirim email uji, lalu "Kirim ulang kredensial" di halaman pengguna |
| Kartu penjadwal *Tidak terdeteksi* | Cron belum dipasang atau jalur PHP salah | Jalankan `php artisan schedule:run` manual; pakai jalur PHP lengkap di cron |
| Sinkron BKN gagal 403 atau 429 | Sumber memakai WAF | Sistem berhenti dan mencoba di jadwal berikutnya; **jangan** mencoba menembusnya. Data lama tetap dipakai |
| Sinkron BKN: "belum ada formasi CPNS" | BKN memang belum menayangkan CPNS | Normal. Halaman Peluang Instansi tetap berfungsi dari statistik 2024; dashboard memberi tahu saat event CPNS muncul |
| Pengguna kembali ke halaman masuk terus | Satu sesi per akun aktif dan akun dipakai di perangkat lain; atau cookie tidak bisa disimpan karena situs tidak lewat HTTPS | Matikan "Satu sesi" di Pengaturan > Situs bila perlu; pastikan HTTPS |
| Aset tidak termuat / layar kosong | `public/build` tidak terunggah, atau proxy menambah CSP sendiri | Pastikan folder `public/build` ada; periksa header CSP ganda |
| `419` / "Sesi halaman habis" | Halaman terbuka terlalu lama | Muat ulang halaman |
| Pesan "Table ... doesn't exist" | Migrasi belum berjalan | `php artisan migrate --force` |

## 8. Daftar periksa sebelum membuka untuk pelanggan

- [ ] `php artisan sicasn:install` selesai tanpa butir gagal.
- [ ] HTTPS aktif; `curl -I https://domain/` menampilkan `Strict-Transport-Security` dan `Content-Security-Policy`.
- [ ] Dashboard: Email, WhatsApp, Pembayaran, Sinkron berstatus Siap; Penjadwal *Berjalan*.
- [ ] Email uji sampai ke kotak masuk (bukan spam). Buat satu akun uji dan pastikan kredensialnya terkirim, login pertama memaksa ganti kata sandi, lalu nonaktifkan akun uji.
- [ ] Cron cadangan harian terpasang dan satu pemulihan sudah dicoba.
- [ ] `.env` tidak dapat diakses dari web (`https://domain/.env` harus 404/403).
- [ ] Kata sandi admin kuat dan unik; tidak ada akun contoh.
