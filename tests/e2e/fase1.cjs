const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');

const OUT = process.argv[2];
const MAIL = process.argv[3];
const REPO = process.cwd();
const PY = 'python3';
const AMBIL_PASSWORD = `${REPO}/tests/e2e/ambil_password.py`;
const BASE = process.env.E2E_BASE || 'http://127.0.0.1:8000';
const hasil = [];

function cek(nama, kondisi, detail = '') {
  hasil.push({ nama, ok: Boolean(kondisi), detail });
  console.log(`${kondisi ? 'LULUS' : 'GAGAL '}  ${nama}${detail ? '  -> ' + detail : ''}`);
}

async function tutup(page, tombol = 'Mengerti') {
  await page.getByRole('button', { name: tombol, exact: true }).click();
  await page.waitForSelector('[role=alertdialog]', { state: 'detached' });
}

async function dialogTeks(page) {
  await page.waitForSelector('[role=alertdialog]');
  await page.waitForTimeout(250);
  return (await page.locator('[role=alertdialog]').innerText()).replace(/\s+/g, ' ');
}

function surat() {
  return JSON.parse(execSync(`${PY} ${AMBIL_PASSWORD} ${MAIL}`).toString());
}

function antrean() {
  execSync('php artisan queue:work --stop-when-empty --max-time=20', { cwd: REPO, stdio: 'pipe' });
}

async function masuk(page, email, password) {
  await page.goto(BASE + '/masuk');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill(password);
  await page.getByRole('button', { name: 'Masuk', exact: true }).click();
}

(async () => {
  const browser = await chromium.launch();
  const galatKonsol = [];
  const baru = async (lebar = 1366, tinggi = 900, skema = 'light') => {
    const ctx = await browser.newContext({ viewport: { width: lebar, height: tinggi }, colorScheme: skema });
    const page = await ctx.newPage();
    page.on('console', (m) => { if (m.type() === 'error') galatKonsol.push(m.text()); });
    page.on('pageerror', (e) => galatKonsol.push(String(e)));
    return { ctx, page };
  };

  const admin = await baru();
  const ap = admin.page;

  await ap.goto(BASE + '/masuk');
  await ap.screenshot({ path: `${OUT}/01-masuk-desktop.png` });
  await masuk(ap, 'admin@uji.test', 'salah-salah-1');
  await ap.waitForSelector('text=Email atau kata sandi tidak cocok.');
  cek('Login dengan kata sandi salah menampilkan galat inline', true);
  await masuk(ap, 'admin@uji.test', 'AdminUji12345');
  await ap.waitForURL('**/admin');
  cek('Admin masuk ke dasbor admin', ap.url().endsWith('/admin'));
  const dasborAwal = await ap.locator('main').innerText();
  cek('Dasbor menandai email belum siap', /Belum siap/.test(dasborAwal));
  await ap.screenshot({ path: `${OUT}/02-dasbor-awal-desktop.png`, fullPage: true });

  await ap.goto(BASE + '/admin/pengaturan/email');
  cek('Preset Hostinger terisi otomatis (host)', (await ap.locator('#hostinger-host').inputValue()) === 'smtp.hostinger.com');
  cek('Preset Hostinger terisi otomatis (port 465)', (await ap.locator('#hostinger-port').inputValue()) === '465');
  cek('Preset Hostinger memakai enkripsi SSL', (await ap.locator('#hostinger-enkripsi').inputValue()) === 'ssl');
  await ap.screenshot({ path: `${OUT}/03-email-hostinger-desktop.png`, fullPage: true });

  await ap.selectOption('#aktif', 'smtp_kustom');
  await ap.locator('#smtp_kustom-host').fill('127.0.0.1');
  await ap.locator('#smtp_kustom-port').fill('1025');
  await ap.selectOption('#smtp_kustom-enkripsi', 'tanpa');
  await ap.locator('#smtp_kustom-username').fill('akun-uji');
  await ap.locator('#smtp_kustom-password').fill('rahasia-smtp-uji');
  await ap.locator('#pengirim_alamat').fill('noreply@uji.test');
  await ap.getByRole('button', { name: 'Simpan pengaturan email' }).click();
  const konfirmasi = await dialogTeks(ap);
  cek('Simpan pengaturan email meminta konfirmasi modal', /Simpan pengaturan email\?/.test(konfirmasi));
  await ap.screenshot({ path: `${OUT}/04-dialog-konfirmasi-desktop.png` });
  await tutup(ap, 'Ya, simpan');
  const sukses = await dialogTeks(ap);
  cek('Setelah simpan muncul dialog sukses', /Pengaturan email tersimpan/.test(sukses));
  await tutup(ap);

  await ap.reload();
  cek('Kata sandi SMTP tidak dikirim balik ke halaman', !(await ap.content()).includes('rahasia-smtp-uji'));
  cek('Kolom kata sandi menandai tersimpan', await ap.getByText('Tersimpan. Kosongkan bila tidak ingin menggantinya.').isVisible());

  await ap.locator('#tujuan').fill('tes@uji.test');
  await ap.getByRole('button', { name: 'Kirim email uji' }).click();
  const uji = await dialogTeks(ap);
  cek('Email uji melaporkan sukses lewat dialog', /Email uji terkirim/.test(uji) && /smtp_kustom/.test(uji), uji.slice(0, 120));
  await ap.screenshot({ path: `${OUT}/05-dialog-email-uji-desktop.png` });
  await tutup(ap);
  const setelahUji = surat();
  cek('Email uji benar-benar diterima server SMTP', setelahUji.length === 1 && setelahUji[0].subjek === 'Email uji SICASN' && setelahUji[0].ke.includes('tes@uji.test'));

  await ap.goto(BASE + '/admin/pengaturan/whatsapp');
  await ap.locator('#nomor').fill('0812-3456-7890');
  await ap.locator('#nama_tampilan').fill('Kak Rina');
  await ap.getByRole('button', { name: 'Simpan pengaturan WhatsApp' }).click();
  await dialogTeks(ap);
  await tutup(ap, 'Ya, simpan');
  cek('Simpan WhatsApp sukses', /Pengaturan WhatsApp tersimpan/.test(await dialogTeks(ap)));
  await tutup(ap);
  await ap.reload();
  cek('Nomor WhatsApp disimpan dalam format 62', (await ap.locator('#nomor').inputValue()) === '6281234567890');

  await ap.goto(BASE + '/admin/pengaturan/pembayaran');
  await ap.locator('#harga_teks').fill('Rp 99.000 untuk 3 bulan');
  await ap.locator('#catatan').fill('Akun dibuat setelah bukti transfer dikirim.');
  await ap.getByRole('button', { name: 'Tambah rekening' }).click();
  await ap.locator('#bank-0').fill('BCA');
  await ap.locator('#nomor-0').fill('1234567890');
  await ap.locator('#atas-0').fill('PT Contoh Sejahtera');
  await ap.getByRole('switch', { name: /Tampilkan nomor rekening/ }).click();
  await ap.screenshot({ path: `${OUT}/06-pembayaran-desktop.png`, fullPage: true });
  await ap.getByRole('button', { name: 'Simpan info pembayaran' }).click();
  cek('Simpan pembayaran sukses', /Info pembayaran tersimpan/.test(await dialogTeks(ap)));
  await tutup(ap);

  await ap.goto(BASE + '/admin/pengaturan/situs');
  cek('Satu sesi per akun aktif secara bawaan', (await ap.getByRole('switch').getAttribute('aria-checked')) === 'true');

  await ap.goto(BASE + '/admin');
  const dasborSiap = await ap.locator('main').innerText();
  cek('Dasbor menandai semua kesiapan siap', (dasborSiap.match(/Siap/g) || []).length >= 3 && !/Belum siap/.test(dasborSiap));

  await ap.goto(BASE + '/admin/pengguna/buat');
  await ap.getByLabel('Nama lengkap').fill('Siti Aminah');
  await ap.getByLabel('Email', { exact: true }).fill('siti@uji.test');
  await ap.getByLabel('Nomor WhatsApp (opsional)').fill('081298765432');
  await ap.getByLabel('Catatan pembayaran (opsional)').fill('TF 14/11 BCA');
  await ap.screenshot({ path: `${OUT}/07-buat-akun-desktop.png`, fullPage: true });
  await ap.getByRole('button', { name: 'Buat akun dan kirim email' }).click();
  await ap.waitForURL(/\/admin\/pengguna\/\d+$/);
  const buat = await dialogTeks(ap);
  cek('Pembuatan akun menampilkan dialog sukses', /Akun berhasil dibuat/.test(buat));
  await ap.screenshot({ path: `${OUT}/08-dialog-akun-dibuat-desktop.png` });
  await tutup(ap);
  const urlDetail = ap.url();

  antrean();
  await ap.reload();
  cek('Status email kredensial berubah menjadi terkirim setelah antrean diproses', await ap.getByText('Email terkirim').first().isVisible());
  await ap.screenshot({ path: `${OUT}/09-detail-pengguna-desktop.png`, fullPage: true });

  const semuaSurat = surat();
  const kredensial = semuaSurat.find((s) => s.subjek === 'Akun SICASN Anda sudah siap');
  cek('Email kredensial diterima di alamat pengguna', kredensial && kredensial.ke.includes('siti@uji.test'));
  cek('Email berisi kata sandi sementara 14 karakter', kredensial && kredensial.password && kredensial.password.length === 14);
  cek('Email berisi tautan login dan tautan WhatsApp admin', kredensial && kredensial.adaTautanLogin && kredensial.adaTautanWa);
  cek('Pengirim email sesuai pengaturan dashboard', kredensial && kredensial.dari.includes('noreply@uji.test'));
  const sementara = kredensial.password;

  const member = await baru(390, 844);
  const mp = member.page;
  await masuk(mp, 'siti@uji.test', sementara);
  await mp.waitForURL('**/ganti-password');
  cek('Login pertama dipaksa ke halaman ganti kata sandi', mp.url().endsWith('/ganti-password'));
  await mp.screenshot({ path: `${OUT}/10-ganti-password-hp.png`, fullPage: true });
  await mp.goto(BASE + '/akun');
  cek('Halaman member lain ditahan sebelum kata sandi diganti', mp.url().endsWith('/ganti-password'));

  await mp.getByLabel('Kata sandi sementara dari email').fill(sementara);
  await mp.getByLabel('Kata sandi baru', { exact: true }).fill('pendek');
  await mp.getByLabel('Ulangi kata sandi baru').fill('pendek');
  await mp.getByRole('button', { name: 'Simpan kata sandi' }).click();
  await mp.waitForSelector('[role=alert]');
  cek('Kata sandi lemah ditolak dengan galat inline', /minimal 10 karakter/i.test(await mp.locator('[role=alert]').first().innerText()));

  await mp.getByLabel('Kata sandi sementara dari email').fill(sementara);
  await mp.getByLabel('Kata sandi baru', { exact: true }).fill('KataSandiBaru2026');
  await mp.getByLabel('Ulangi kata sandi baru').fill('KataSandiBaru2026');
  await mp.getByRole('button', { name: 'Simpan kata sandi' }).click();
  await mp.waitForURL('**/akun');
  const gantiOk = await dialogTeks(mp);
  cek('Setelah ganti kata sandi member masuk ke akun dengan dialog sukses', /Kata sandi diperbarui/.test(gantiOk));
  await mp.screenshot({ path: `${OUT}/11-dialog-password-diganti-hp.png` });
  await tutup(mp);
  await mp.screenshot({ path: `${OUT}/12-akun-hp.png`, fullPage: true });

  const adminGagal = await mp.goto(BASE + '/admin');
  cek('Member tidak boleh membuka panel admin (403)', adminGagal && adminGagal.status() === 403);

  await ap.goto(urlDetail);
  await ap.getByLabel('Alasan (opsional)').fill('Belum bayar');
  await ap.getByRole('button', { name: 'Nonaktifkan akun' }).click();
  const konfNon = await dialogTeks(ap);
  cek('Menonaktifkan akun meminta konfirmasi modal', /Nonaktifkan akun ini\?/.test(konfNon));
  await ap.screenshot({ path: `${OUT}/13-dialog-nonaktifkan-desktop.png` });
  await tutup(ap, 'Ya, nonaktifkan');
  cek('Akun berhasil dinonaktifkan', /Akun dinonaktifkan/.test(await dialogTeks(ap)));
  await tutup(ap);

  await mp.goto(BASE + '/akun');
  await mp.waitForURL('**/masuk');
  const nonaktif = await dialogTeks(mp);
  cek('Member yang sedang masuk langsung dikeluarkan saat dinonaktifkan', /Akun Anda sedang nonaktif/.test(nonaktif));
  const tautanWa = await mp.getByRole('link', { name: 'Hubungi admin via WhatsApp' }).getAttribute('href');
  cek('Dialog nonaktif memuat tombol WhatsApp admin', tautanWa && tautanWa.startsWith('https://wa.me/6281234567890'), tautanWa || '');
  await mp.screenshot({ path: `${OUT}/14-dialog-akun-nonaktif-hp.png` });
  await tutup(mp);

  await masuk(mp, 'siti@uji.test', 'KataSandiBaru2026');
  cek('Login akun nonaktif ditolak dengan dialog', /Akun Anda sedang nonaktif/.test(await dialogTeks(mp)));
  await tutup(mp);

  await ap.goto(urlDetail);
  await ap.getByRole('button', { name: 'Aktifkan kembali' }).click();
  await dialogTeks(ap);
  await tutup(ap, 'Ya, aktifkan');
  await dialogTeks(ap);
  await tutup(ap);
  await masuk(mp, 'siti@uji.test', 'KataSandiBaru2026');
  await mp.waitForURL('**/akun');
  cek('Setelah diaktifkan kembali member bisa masuk dengan kata sandi barunya', mp.url().endsWith('/akun'));

  const perangkatLain = await baru(1024, 768);
  await masuk(perangkatLain.page, 'siti@uji.test', 'KataSandiBaru2026');
  await perangkatLain.page.waitForURL('**/akun');
  await mp.goto(BASE + '/akun');
  await mp.waitForURL('**/masuk');
  cek('Masuk di perangkat kedua mengeluarkan sesi di perangkat pertama', /Akun dipakai di perangkat lain/.test(await dialogTeks(mp)));
  await tutup(mp);

  const publik = await baru(1366, 900);
  await publik.page.goto(BASE + '/');
  const beranda = await publik.page.locator('main').innerText();
  cek('Beranda menampilkan harga dari pengaturan admin', beranda.includes('Rp 99.000 untuk 3 bulan'));
  cek('Beranda menampilkan rekening karena admin mengizinkan', beranda.includes('1234567890') && beranda.includes('a.n. PT Contoh Sejahtera'));
  const [popup] = await Promise.all([
    publik.page.waitForEvent('popup'),
    publik.page.getByRole('button', { name: /Chat Admin via WhatsApp/ }).first().click(),
  ]);
  cek('Tombol Chat Admin membuka WhatsApp dengan nomor dan pesan dari dashboard', popup.url().startsWith('https://wa.me/6281234567890?text=Halo%20Kak%20Rina'), popup.url());
  await popup.close().catch(() => {});
  await publik.page.screenshot({ path: `${OUT}/15-beranda-info-pembayaran-desktop.png`, fullPage: true });

  const ukuran = [['hp', 390, 844], ['tablet', 820, 1180], ['desktop', 1366, 900]];
  const halamanAdmin = [['dasbor', '/admin'], ['pengguna', '/admin/pengguna'], ['detail', urlDetail.replace(BASE, '')], ['email', '/admin/pengaturan/email'], ['whatsapp', '/admin/pengaturan/whatsapp'], ['pembayaran', '/admin/pengaturan/pembayaran'], ['situs', '/admin/pengaturan/situs']];
  for (const [namaUkuran, lebar, tinggi] of ukuran) {
    for (const skema of ['light', 'dark']) {
      const konteks = await baru(lebar, tinggi, skema);
      await masuk(konteks.page, 'admin@uji.test', 'AdminUji12345');
      await konteks.page.waitForURL('**/admin');
      for (const [namaHalaman, jalur] of halamanAdmin) {
        await konteks.page.goto(BASE + jalur, { waitUntil: 'networkidle' });
        await konteks.page.waitForTimeout(150);
        const lebarDok = await konteks.page.evaluate(() => document.documentElement.scrollWidth);
        if (lebarDok > lebar) cek(`Tanpa scroll horizontal: ${namaHalaman} ${namaUkuran} ${skema}`, false, `scrollWidth ${lebarDok} > ${lebar}`);
        if (skema === 'light' || namaHalaman === 'dasbor') await konteks.page.screenshot({ path: `${OUT}/adm-${namaHalaman}-${namaUkuran}-${skema}.png`, fullPage: true });
      }
      if (namaUkuran !== 'desktop' && skema === 'light') {
        await konteks.page.goto(BASE + '/admin', { waitUntil: 'networkidle' });
        await konteks.page.getByRole('button', { name: 'Buka menu admin' }).click();
        await konteks.page.waitForTimeout(350);
        await konteks.page.screenshot({ path: `${OUT}/adm-menu-${namaUkuran}.png` });
      }
      await konteks.ctx.close();
    }
  }
  cek('Tidak ada galat horizontal-overflow di 7 halaman admin x 3 ukuran x 2 tema', !hasil.some((h) => h.nama.startsWith('Tanpa scroll horizontal') && !h.ok));
  const galatPenting = galatKonsol.filter((g) => !/favicon|Failed to load resource: the server responded with a status of (403|419)/.test(g));
  cek('Tidak ada galat JavaScript di konsol browser', galatPenting.length === 0, galatPenting.slice(0, 3).join(' | '));

  await browser.close();
  const gagal = hasil.filter((h) => !h.ok);
  console.log(`\nRINGKASAN: ${hasil.length - gagal.length} lulus, ${gagal.length} gagal dari ${hasil.length} pemeriksaan`);
  fs.writeFileSync(`${OUT}/hasil.json`, JSON.stringify(hasil, null, 2));
  process.exit(gagal.length ? 1 : 0);
})().catch((e) => { console.error('KESALAHAN SKRIP:', e); process.exit(2); });
