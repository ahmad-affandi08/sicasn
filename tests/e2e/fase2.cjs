const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');

const OUT = process.argv[2];
const REPO = process.cwd();
const BASE = process.env.E2E_BASE || 'http://127.0.0.1:8000';
const hasil = [];

function cek(nama, kondisi, detail = '') {
  hasil.push({ nama, ok: Boolean(kondisi), detail });
  console.log(`${kondisi ? 'LULUS' : 'GAGAL '}  ${nama}${detail ? '  -> ' + detail : ''}`);
}

function artisan(perintah) {
  return execSync(`php artisan ${perintah}`, { cwd: REPO, stdio: 'pipe' }).toString();
}

async function dialogTeks(page) {
  await page.waitForSelector('[role=alertdialog]');
  await page.waitForTimeout(250);
  return (await page.locator('[role=alertdialog]').innerText()).replace(/\s+/g, ' ');
}

async function tutup(page, tombol = 'Mengerti') {
  await page.getByRole('button', { name: tombol, exact: true }).click();
  await page.waitForSelector('[role=alertdialog]', { state: 'detached' });
}

async function masuk(page) {
  await page.goto(BASE + '/masuk');
  await page.getByLabel('Email').fill('admin@uji.test');
  await page.getByLabel('Kata sandi', { exact: true }).fill('AdminUji12345');
  await page.getByRole('button', { name: 'Masuk', exact: true }).click();
  await page.waitForURL('**/admin');
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

  artisan('sicasn:sync-formasi');

  const { page } = await baru();
  await masuk(page);

  const dasbor = await page.locator('main').innerText();
  const butirSinkron = await page.locator('li:has-text("Sinkron data BKN")').innerText();
  cek('Dasbor menandai sinkron siap setelah sinkron nyata berhasil', /Siap/.test(butirSinkron) && !/Belum siap/.test(butirSinkron), butirSinkron.replace(/\s+/g, ' ').slice(0, 80));
  cek('Dasbor menjelaskan belum ada formasi CPNS', /Belum ada formasi CPNS/.test(dasbor));
  cek('Tidak ada modal event karena seleksi nyata saat ini hanya PPPK', (await page.locator('[role=alertdialog]').count()) === 0);
  await page.screenshot({ path: `${OUT}/20-dasbor-sinkron-nyata-desktop.png`, fullPage: true });

  await page.goto(BASE + '/admin/sinkron');
  const halaman = await page.locator('main').innerText();
  cek('Halaman sinkron menyebut cakupan CPNS', /hanya seleksi CPNS/.test(halaman));
  cek('Event nyata terdeteksi dan ditandai diabaikan', (halaman.match(/Diabaikan/g) || []).length >= 2 && /Sekolah Rakyat/.test(halaman));
  cek('Ringkasan menunjukkan 2.012 baris diabaikan', /2\.012/.test(halaman));
  await page.screenshot({ path: `${OUT}/21-sinkron-nyata-desktop.png`, fullPage: true });

  await page.getByRole('button', { name: 'Sinkron sekarang' }).click();
  cek('Sinkron manual meminta konfirmasi modal', /Jalankan sinkron sekarang\?/.test(await dialogTeks(page)));
  await page.screenshot({ path: `${OUT}/22-dialog-konfirmasi-sinkron-desktop.png` });
  await tutup(page, 'Ya, sinkronkan');
  cek('Sinkron manual dijadwalkan lewat antrean', /Sinkron dijadwalkan/.test(await dialogTeks(page)));
  await tutup(page);

  artisan('queue:work --stop-when-empty --max-time=60');
  await page.reload();
  const riwayat = await page.locator('main').innerText();
  cek('Antrean memproses sinkron manual dan riwayat bertambah', (await page.locator('li:has-text("Mulai")').count()) >= 2);
  cek('Sinkron manual berikutnya dilewati karena respons identik', /Tidak ada perubahan pada data BKN/.test(riwayat));

  await page.locator('#url').fill('http://tidak-aman.contoh/api');
  await page.getByRole('button', { name: 'Simpan pengaturan sinkron' }).click();
  await page.waitForSelector('#url-galat');
  cek('URL non-https ditolak dengan galat inline', /https/.test(await page.locator('#url-galat').innerText()));
  await page.locator('#url').fill('https://api-rekrutmen.bkn.go.id/portal/api/spf');
  await page.locator('#frekuensi').selectOption('enam_jam');
  await page.getByRole('button', { name: 'Simpan pengaturan sinkron' }).click();
  cek('Pengaturan sinkron valid tersimpan', /Pengaturan sinkron tersimpan/.test(await dialogTeks(page)));
  await tutup(page);
  await page.reload();
  cek('Frekuensi tersimpan setelah muat ulang', (await page.locator('#frekuensi').inputValue()) === 'enam_jam');
  await page.locator('#frekuensi').selectOption('harian');
  await page.getByRole('button', { name: 'Simpan pengaturan sinkron' }).click();
  await dialogTeks(page);
  await tutup(page);

  const keluaran = execSync('php tests/e2e/sinkron_fixture.php', { cwd: REPO, stdio: 'pipe' }).toString();
  cek('Sinkron fixture CPNS memproses 5 formasi', /5 formasi CPNS diproses, 5 baru/.test(keluaran), keluaran.trim());

  await page.goto(BASE + '/admin');
  const modal = await dialogTeks(page);
  cek('Modal event CPNS baru muncul di dasbor', /Event CPNS baru terdeteksi/.test(modal) && /Seleksi CPNS Sintetis/.test(modal));
  await page.screenshot({ path: `${OUT}/23-modal-event-cpns-desktop.png` });
  await tutup(page, 'Lihat sinkron');
  await page.waitForLoadState('networkidle');
  await page.reload();
  await page.waitForTimeout(500);
  cek('Modal event tidak muncul lagi setelah ditandai diberitahukan', (await page.locator('[role=alertdialog]').count()) === 0);
  const dasborSesudah = await page.locator('main').innerText();
  cek('Dasbor menampilkan 5 formasi aktif', /5\s*Formasi CPNS aktif/.test(dasborSesudah.replace(/\n/g, ' ')));
  await page.screenshot({ path: `${OUT}/24-dasbor-setelah-cpns-desktop.png`, fullPage: true });

  await page.goto(BASE + '/admin/sinkron');
  const setelah = await page.locator('main').innerText();
  cek('Event CPNS tampil sebagai Diproses', /Seleksi CPNS Sintetis[\s\S]*Diproses/.test(setelah));

  const ukuran = [['hp', 390, 844], ['tablet', 820, 1180], ['desktop', 1366, 900]];

  for (const [nama, lebar, tinggi] of ukuran) {
    for (const skema of ['light', 'dark']) {
      const konteks = await baru(lebar, tinggi, skema);
      await masuk(konteks.page);

      for (const [berkas, jalur] of [['sinkron', '/admin/sinkron'], ['dasbor', '/admin']]) {
        await konteks.page.goto(BASE + jalur, { waitUntil: 'networkidle' });
        await konteks.page.waitForTimeout(150);
        const lebarDok = await konteks.page.evaluate(() => document.documentElement.scrollWidth);
        if (lebarDok > lebar) cek(`Tanpa scroll horizontal: ${berkas} ${nama} ${skema}`, false, `scrollWidth ${lebarDok} > ${lebar}`);
        if (skema === 'light' || berkas === 'sinkron') await konteks.page.screenshot({ path: `${OUT}/adm2-${berkas}-${nama}-${skema}.png`, fullPage: true });
      }

      await konteks.ctx.close();
    }
  }

  cek('Tanpa scroll horizontal di halaman sinkron dan dasbor pada 3 ukuran x 2 tema', !hasil.some((h) => h.nama.startsWith('Tanpa scroll horizontal:') && !h.ok));
  const penting = galatKonsol.filter((g) => !/favicon|status of (403|419)/.test(g));
  cek('Tidak ada galat JavaScript di konsol browser', penting.length === 0, penting.slice(0, 3).join(' | '));

  await browser.close();
  const gagal = hasil.filter((h) => !h.ok);
  console.log(`\nRINGKASAN: ${hasil.length - gagal.length} lulus, ${gagal.length} gagal dari ${hasil.length} pemeriksaan`);
  fs.writeFileSync(`${OUT}/hasil-fase2.json`, JSON.stringify(hasil, null, 2));
  process.exit(gagal.length ? 1 : 0);
})().catch((e) => { console.error('KESALAHAN SKRIP:', e); process.exit(2); });
