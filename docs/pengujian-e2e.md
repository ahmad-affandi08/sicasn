# Uji Menyeluruh (E2E)

Skrip di `tests/e2e/` menjalankan seluruh alur bisnis di browser sungguhan dengan server SMTP lokal, jadi email benar-benar dikirim, diterima, dan dibaca.

## Kebutuhan

- Node dengan paket `playwright` (dan browser Chromium), Python 3, dan `aiosmtpd` (`pip install aiosmtpd`).
- Database lokal kosong (SQLite cukup) dan aplikasi sudah dibangun (`npm run build`).

## Langkah

```
php artisan migrate:fresh --force
php artisan sicasn:buat-admin --nama="Admin Uji" --email="admin@uji.test" --password="AdminUji12345"
php artisan serve --host=127.0.0.1 --port=8000 &
python3 tests/e2e/smtp_sink.py /tmp/surat-uji &
node tests/e2e/fase1.cjs /tmp/tangkapan-layar /tmp/surat-uji
```

Argumen pertama adalah folder tangkapan layar, argumen kedua folder tempat server SMTP uji menyimpan email (`.eml`). Ubah alamat aplikasi dengan variabel `E2E_BASE`.

## Yang diperiksa (Fase 1)

Login dan galat inline; preset Hostinger; menyimpan pengaturan email dengan konfirmasi modal; kata sandi SMTP tidak dikirim balik; email uji sampai ke server SMTP; WhatsApp dan pembayaran; pembuatan akun, antrean, dan email kredensial yang benar-benar berisi kata sandi sementara; login pertama dipaksa ganti kata sandi; member ditolak dari panel admin; nonaktif lalu aktif kembali dengan dialog WhatsApp; satu sesi per akun; beranda menampilkan data dari dashboard; tanpa scroll horizontal di 7 halaman admin pada 3 ukuran dan 2 tema; tanpa galat JavaScript.

## Catatan

Satu pemeriksaan membuka tab `wa.me`. Di lingkungan tanpa internet pemeriksaan itu gagal karena halaman tujuan tidak terjangkau, bukan karena aplikasi. Verifikasi URL-nya dengan mencegat `window.open` (lihat riwayat pengembangan Fase 1).

## Fase 2 (sinkron BKN)

Skrip `tests/e2e/fase2.cjs` memakai database yang sama dengan langkah di atas dan **API BKN sungguhan** (butuh internet). Urutannya: sinkron nyata lewat artisan, cek dasbor dan halaman Sinkron, klik "Sinkron sekarang" dan proses antrean, uji validasi pengaturan, lalu `tests/e2e/sinkron_fixture.php` memasukkan fixture CPNS sintetis untuk menguji modal event baru.

```
php artisan migrate:fresh --force
php artisan sicasn:buat-admin --nama="Admin Uji" --email="admin@uji.test" --password="AdminUji12345"
php artisan serve --host=127.0.0.1 --port=8000 &
node tests/e2e/fase2.cjs /tmp/tangkapan-layar
```

## Fase 3 (statistik 2024 dan Peluang Instansi)

Skrip `tests/e2e/fase3.cjs` tidak butuh internet. Ia memerlukan admin, seed statistik, dan satu member uji.

```
php artisan migrate:fresh --force
php artisan sicasn:buat-admin --nama="Admin Uji" --email="admin@uji.test" --password="AdminUji12345"
php artisan db:seed --class=StatistikPelamarSeeder --force
php tests/e2e/buat_member.php
npm run build
php artisan serve --host=127.0.0.1 --port=8000 &
node tests/e2e/fase3.cjs /tmp/tangkapan-layar
```

Yang diperiksa (41 pemeriksaan): redirect member setelah login, disclaimer dan tautan sumber resmi, angka nasional bersumber, peringkat tidak dimulai dari angka sementara, urut lewat kepala kolom, pencarian dan filter di URL yang bisa dimuat ulang, rincian riwayat snapshot, keadaan kosong, tampilan kartu/filter/navigasi bawah/target sentuh di HP, impor CSV dengan baris bermasalah (pratinjau, konfirmasi modal, dialog hasil, unduhan galat, total baris), rollback dengan konfirmasi berisiko, berkas ditolak, validasi dan penyimpanan ambang persaingan, member ditolak dari panel admin, tanpa scroll horizontal pada 5 halaman x 3 ukuran x 2 tema, tanpa galat konsol.

Catatan: aturan satu sesi per akun membuat login member kedua mengeluarkan sesi pertama, jadi skrip memakai konteks browser baru untuk tiap pemeriksaan yang butuh member.

