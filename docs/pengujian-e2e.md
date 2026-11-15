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
