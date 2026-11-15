const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');

const OUT = process.argv[2];
const BASE = process.env.E2E_BASE || 'http://127.0.0.1:8000';
const REPO = process.cwd();
const hasil = [];

function cek(nama, kondisi, detail = '') {
  hasil.push({ nama, ok: Boolean(kondisi), detail });
  console.log(`${kondisi ? 'LULUS' : 'GAGAL '}  ${nama}${detail ? '  -> ' + detail : ''}`);
}

async function dialogTeks(page) {
  await page.waitForSelector('[role=alertdialog]');
  await page.waitForTimeout(250);
  return (await page.locator('[role=alertdialog]').innerText()).replace(/\s+/g, ' ');
}

async function masuk(page, email, sandi, tujuan) {
  await page.goto(BASE + '/masuk');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill(sandi);
  await page.getByRole('button', { name: 'Masuk', exact: true }).click();
  await page.waitForURL(`**${tujuan}`);
}

const daftarFormasi = (page) => page.locator('ul[aria-label="Daftar formasi"] > li');

async function klikDanTunggu(page, tombol, pola = '/pantauan') {
  await Promise.all([page.waitForResponse((r) => r.url().includes(pola) || r.url().includes('/formasi')), tombol.click()]);
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(150);
}

(async () => {
  execSync('php tests/e2e/bersihkan_pantauan.php', { cwd: REPO, stdio: 'pipe' });
  const browser = await chromium.launch();
  const galatKonsol = [];
  const baru = async (lebar = 1366, tinggi = 900, skema = 'light') => {
    const ctx = await browser.newContext({ viewport: { width: lebar, height: tinggi }, colorScheme: skema });
    const page = await ctx.newPage();
    page.on('console', (m) => { if (m.type() === 'error') galatKonsol.push(m.text()); });
    page.on('pageerror', (e) => galatKonsol.push(String(e)));
    return { ctx, page };
  };

  const { page: m } = await baru();
  await masuk(m, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  await m.goto(BASE + '/pantauan');
  cek('Daftar pantauan kosong menampilkan ajakan menjelajah formasi', /Belum ada formasi yang dipantau/.test(await m.locator('main').innerText()) && (await m.getByRole('link', { name: 'Jelajahi formasi' }).count()) === 1);
  await m.screenshot({ path: `${OUT}/60-pantauan-kosong-desktop.png` });

  await m.goto(BASE + '/formasi');
  await m.waitForLoadState('networkidle');
  const jabatan = [];

  for (let i = 0; i < 5; i += 1) {
    const kartu = daftarFormasi(m).nth(i);
    jabatan.push((await kartu.locator('h3').innerText()).trim());
    await klikDanTunggu(m, kartu.getByRole('button', { name: /^Pantau /, exact: false }));
    cek(`Formasi ke-${i + 1} berstatus Dipantau tanpa dialog`, (await daftarFormasi(m).nth(i).getByRole('button', { name: /^Dipantau / }).getAttribute('aria-pressed')) === 'true' && (await m.locator('[role=alertdialog]').count()) === 0);
  }

  await m.reload();
  await m.waitForLoadState('networkidle');
  cek('Status dipantau bertahan setelah muat ulang', (await m.getByRole('button', { name: /^Dipantau / }).count()) === 5);

  await m.goto(BASE + '/pantauan');
  await m.waitForLoadState('networkidle');
  cek('Daftar pantauan memuat 5 formasi', (await m.locator('ul[aria-label="Daftar Pantauan"] > li').count()) === 5 && /5 dari 100 formasi dipantau/.test(await m.locator('main').innerText()));
  cek('Setiap formasi tetap menampilkan persaingan dan sumber', /Persaingan 2024/.test(await m.locator('main').innerText()));

  const area = m.locator('ul[aria-label="Daftar Pantauan"] > li').first().getByLabel('Catatan pribadi');
  await area.fill('Cek syarat usia dan tanya teman');
  await klikDanTunggu(m, m.locator('ul[aria-label="Daftar Pantauan"] > li').first().getByRole('button', { name: 'Simpan catatan' }));
  cek('Catatan tersimpan dengan penanda', /Catatan tersimpan/.test(await m.locator('main').innerText()));
  await m.reload();
  await m.waitForLoadState('networkidle');
  cek('Catatan bertahan setelah muat ulang', (await m.getByLabel('Catatan pribadi').first().inputValue()) === 'Cek syarat usia dan tanya teman');

  const tombolBandingkan = m.getByRole('button', { name: /^Bandingkan \(/ });
  cek('Tombol bandingkan nonaktif tanpa pilihan', await tombolBandingkan.isDisabled());
  const kotakPilih = m.locator('input[type=checkbox]');
  await kotakPilih.nth(0).check();
  cek('Satu pilihan belum cukup untuk membandingkan', await m.getByRole('button', { name: 'Bandingkan (1)' }).isDisabled());
  await kotakPilih.nth(1).check();
  await kotakPilih.nth(2).check();
  await kotakPilih.nth(3).check();
  cek('Pilihan kelima dinonaktifkan setelah empat dipilih', await kotakPilih.nth(4).isDisabled() && /Maksimal 4 formasi/.test(await m.locator('main').innerText()));

  await Promise.all([m.waitForURL('**/pantauan/bandingkan**'), m.getByRole('button', { name: 'Bandingkan (4)' }).click()]);
  await m.waitForLoadState('networkidle');
  const tabel = m.getByRole('table', { name: 'Perbandingan Formasi' });
  cek('Perbandingan menampilkan 4 kolom formasi', (await tabel.getByRole('columnheader').count()) === 5);
  const teksTabel = (await tabel.innerText()).replace(/\s+/g, ' ');
  cek('Perbandingan memuat baris lokasi, kuota, persaingan, dan catatan', /Lokasi/.test(teksTabel) && /Kuota/.test(teksTabel) && /Persaingan instansi/.test(teksTabel) && /Cek syarat usia dan tanya teman/.test(teksTabel));
  cek('Perbandingan menyebut rata-rata instansi dan sumber', /Rata-rata instansi/.test(teksTabel) && /Data|detikEdu|Liputan6|Kompas|BKN/.test(teksTabel));
  await m.screenshot({ path: `${OUT}/61-bandingkan-desktop.png`, fullPage: true });

  const { page: hp } = await baru(390, 844);
  await masuk(hp, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  await hp.goto(m.url());
  await hp.waitForLoadState('networkidle');
  cek('HP: halaman perbandingan tanpa scroll horizontal di tingkat halaman', (await hp.evaluate(() => document.documentElement.scrollWidth)) <= 390);
  cek('HP: tabel perbandingan bisa digeser di dalam wadahnya', await hp.evaluate(() => { const w = document.querySelector('table')?.parentElement; return Boolean(w) && w.scrollWidth > w.clientWidth; }));
  await hp.screenshot({ path: `${OUT}/62-bandingkan-hp.png` });
  await hp.goto(BASE + '/pantauan');
  await hp.waitForLoadState('networkidle');
  cek('HP: daftar pantauan tanpa scroll horizontal', (await hp.evaluate(() => document.documentElement.scrollWidth)) <= 390);
  cek('HP: navigasi bawah memuat tautan Pantauan', (await hp.locator('nav[aria-label="Navigasi utama"]').last().getByRole('link', { name: 'Pantauan' }).count()) === 1);
  await hp.screenshot({ path: `${OUT}/63-pantauan-hp.png` });

  await m.goto(BASE + '/pantauan');
  await m.waitForURL('**/masuk');
  await m.getByRole('button', { name: 'Mengerti', exact: true }).click();
  await masuk(m, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  await m.goto(BASE + '/pantauan/bandingkan?id[]=1');
  cek('Perbandingan dengan satu formasi ditolak dengan dialog dan kembali ke daftar', /Perbandingan belum bisa dibuka/.test(await dialogTeks(m)) && m.url().endsWith('/pantauan'));
  await m.getByRole('button', { name: 'Mengerti', exact: true }).click();
  await m.waitForSelector('[role=alertdialog]', { state: 'detached' });

  await m.goto(BASE + '/pantauan');
  await m.waitForLoadState('networkidle');
  await m.locator('ul[aria-label="Daftar Pantauan"] > li').first().getByRole('button', { name: /^Dipantau / }).click();
  cek('Menghentikan pantauan meminta konfirmasi modal', /Hentikan pantauan\?/.test(await dialogTeks(m)));
  await m.screenshot({ path: `${OUT}/64-konfirmasi-hentikan-desktop.png` });
  await klikDanTunggu(m, m.getByRole('button', { name: 'Ya, hentikan', exact: true }));
  await m.waitForSelector('[role=alertdialog]', { state: 'detached' }).catch(() => undefined);
  cek('Setelah dihentikan daftar menyusut menjadi 4', (await m.locator('ul[aria-label="Daftar Pantauan"] > li').count()) === 4 && /4 dari 100 formasi dipantau/.test(await m.locator('main').innerText()));

  await m.goto(BASE + '/formasi');
  await m.waitForLoadState('networkidle');
  cek('Daftar formasi menandai 4 formasi yang masih dipantau', (await m.getByRole('button', { name: /^Dipantau / }).count()) === 4);

  const tautan = await m.locator('ul[aria-label="Daftar formasi"] a[href^="/formasi/"]').nth(1).getAttribute('href');
  await m.goto(BASE + tautan);
  await m.waitForLoadState('networkidle');
  cek('Halaman detail menampilkan tombol pantau dengan status yang benar', (await m.getByRole('button', { name: /^(Pantau|Dipantau) / }).count()) === 1);

  const ukuran = [['hp', 390, 844], ['tablet', 820, 1180], ['desktop', 1366, 900]];

  for (const [nama, lebar, tinggi] of ukuran) {
    for (const skema of ['light', 'dark']) {
      const k = await baru(lebar, tinggi, skema);
      await masuk(k.page, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
      await k.page.goto(BASE + '/pantauan', { waitUntil: 'networkidle' });
      const ids = (await k.page.locator('ul[aria-label="Daftar Pantauan"] a[href^="/formasi/"]').evaluateAll((a) => a.map((x) => x.getAttribute('href')))).map((h) => h.split('/').pop());

      for (const [berkas, jalur] of [['pantauan', '/pantauan'], ['bandingkan', `/pantauan/bandingkan?${ids.slice(0, 3).map((i) => `id[]=${i}`).join('&')}`]]) {
        await k.page.goto(BASE + jalur, { waitUntil: 'networkidle' });
        await k.page.waitForTimeout(150);
        const lebarDok = await k.page.evaluate(() => document.documentElement.scrollWidth);
        if (lebarDok > lebar) cek(`Tanpa scroll horizontal: ${berkas} ${nama} ${skema}`, false, `scrollWidth ${lebarDok} > ${lebar}`);
        if (skema === 'dark' && nama === 'desktop') await k.page.screenshot({ path: `${OUT}/pantauan6-${berkas}-${nama}-${skema}.png`, fullPage: true });
      }

      await k.ctx.close();
    }
  }

  cek('Tanpa scroll horizontal di 2 halaman pada 3 ukuran x 2 tema', !hasil.some((h) => h.nama.startsWith('Tanpa scroll horizontal:') && !h.ok));
  const penting = galatKonsol.filter((g) => !/favicon|status of (403|419|404)/.test(g));
  cek('Tidak ada galat JavaScript di konsol browser', penting.length === 0, penting.slice(0, 3).join(' | '));

  await browser.close();
  const gagal = hasil.filter((h) => !h.ok);
  console.log(`\nRINGKASAN: ${hasil.length - gagal.length} lulus, ${gagal.length} gagal dari ${hasil.length} pemeriksaan`);
  fs.writeFileSync(`${OUT}/hasil-fase6.json`, JSON.stringify(hasil, null, 2));
  process.exit(gagal.length ? 1 : 0);
})().catch((e) => { console.error('KESALAHAN SKRIP:', e); process.exit(2); });
