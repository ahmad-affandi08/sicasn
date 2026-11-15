const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');

const OUT = process.argv[2];
const BASE = process.env.E2E_BASE || 'http://127.0.0.1:8000';
const REPO = process.cwd();
const AXE = fs.readFileSync(`${REPO}/node_modules/axe-core/axe.min.js`, 'utf8');
const hasil = [];

function cek(nama, kondisi, detail = '') {
  hasil.push({ nama, ok: Boolean(kondisi), detail });
  console.log(`${kondisi ? 'LULUS' : 'GAGAL '}  ${nama}${detail ? '  -> ' + detail : ''}`);
}

async function masuk(page, email, sandi, tujuan) {
  await page.goto(BASE + '/masuk');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill(sandi);
  await page.getByRole('button', { name: 'Masuk', exact: true }).click();
  await page.waitForURL(`**${tujuan}`);
  await page.waitForLoadState('networkidle');
}

async function periksaAksesibilitas(page) {
  await page.evaluate(AXE);
  const hasilAxe = await page.evaluate(() => axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'] } }));
  return hasilAxe.violations.map((v) => `${v.id}[${v.impact}] x${v.nodes.length}: ${v.nodes.slice(0, 2).map((n) => n.target.join(' ')).join(' | ')}`);
}

async function tanpaScrollHorizontal(page) {
  return page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1);
}

(async () => {
  execSync('php artisan cache:clear', { cwd: REPO, stdio: 'pipe' });
  execSync('php tests/e2e/buat_member_baru.php', { cwd: REPO, stdio: 'pipe' });
  const browser = await chromium.launch();
  const galatKonsol = [];
  const baru = async (lebar = 1366, tinggi = 900, skema = 'light') => {
    const ctx = await browser.newContext({ viewport: { width: lebar, height: tinggi }, colorScheme: skema });
    const page = await ctx.newPage();
    page.on('console', (m) => { if (m.type() === 'error' && !/status of (403|404)/.test(m.text())) galatKonsol.push(`${page.url()} :: ${m.text()}`); });
    page.on('pageerror', (e) => galatKonsol.push(String(e)));
    return { ctx, page };
  };

  const { page: tamu, ctx: ctxTamu } = await baru();
  const respons = await tamu.goto(BASE + '/');
  const h = respons.headers();
  cek('Beranda mengirim CSP, X-Frame-Options, nosniff, Referrer-Policy', h['content-security-policy'] && h['x-frame-options'] === 'DENY' && h['x-content-type-options'] === 'nosniff' && h['referrer-policy']);
  const nonce = /'nonce-([A-Za-z0-9]+)'/.exec(h['content-security-policy'] || '')?.[1];
  const nonceHtml = await tamu.evaluate(() => Array.from(document.scripts).map((s) => s.nonce).filter(Boolean));
  cek('Nonce pada CSP sama dengan nonce skrip di halaman', nonce && nonceHtml.length >= 2 && nonceHtml.every((n) => n === nonce), `nonce=${nonce} skrip=${nonceHtml.length}`);
  await tamu.waitForLoadState('networkidle');
  cek('Beranda terender penuh di bawah CSP tanpa galat konsol', (await tamu.getByRole('heading', { level: 1 }).count()) === 1 && galatKonsol.length === 0, galatKonsol.join(' || '));

  await ctxTamu.addInitScript(() => { try { localStorage.setItem('tema', 'gelap'); } catch (e) { void e; } });
  await tamu.reload();
  await tamu.waitForLoadState('networkidle');
  cek('Skrip tema inline (bernonce) berjalan: mode gelap diterapkan', await tamu.evaluate(() => document.documentElement.classList.contains('dark')));
  await ctxTamu.close();

  const { page: g } = await baru();
  const r404 = await g.goto(BASE + '/halaman-yang-tidak-ada');
  await g.waitForLoadState('networkidle');
  cek('Alamat tidak ada menjawab 404 dengan halaman ramah', r404.status() === 404 && (await g.getByRole('heading', { level: 1, name: 'Halaman tidak ditemukan' }).count()) === 1 && (await g.getByRole('link', { name: 'Kembali ke beranda' }).count()) === 1);
  await g.screenshot({ path: `${OUT}/70-404-desktop.png` });
  await g.getByRole('link', { name: 'Kembali ke beranda' }).click();
  await g.waitForURL(`${BASE}/`);

  await g.getByRole('link', { name: 'Kebijakan privasi' }).click();
  await g.waitForURL('**/privasi');
  const teksPrivasi = await g.locator('main').innerText();
  cek('Kebijakan privasi terbuka dari footer dan menyebut data yang disimpan', /nama, alamat email, dan nomor WhatsApp/.test(teksPrivasi) && /tidak meminta atau menyimpan data pribadi pelamar/i.test(teksPrivasi));
  await g.screenshot({ path: `${OUT}/71-privasi-desktop.png`, fullPage: true });

  const { page: m, ctx: ctxM } = await baru();
  await masuk(m, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  const r403 = await m.goto(BASE + '/admin');
  await m.waitForLoadState('networkidle');
  cek('Member yang membuka /admin melihat halaman 403 ramah dan bukan teks mentah', r403.status() === 403 && (await m.getByRole('heading', { level: 1, name: 'Halaman ini bukan untuk akun Anda' }).count()) === 1 && (await m.getByRole('link', { name: 'Kembali ke halaman utama' }).getAttribute('href')) === '/peluang-instansi');
  cek('Member tanpa panduan tertunda tidak diganggu modal panduan', (await m.locator('[role=alertdialog]').count()) === 0);
  await m.goto(BASE + '/masuk');
  cek('Member yang sudah masuk dialihkan dari /masuk ke peluang instansi', m.url().endsWith('/peluang-instansi'));
  await ctxM.close();

  const { page: b, ctx: ctxB } = await baru();
  await masuk(b, 'baru@uji.test', 'MemberBaru12345', '/peluang-instansi');
  await b.waitForSelector('[role=alertdialog]');
  let dialog = (await b.locator('[role=alertdialog]').innerText()).replace(/\s+/g, ' ');
  cek('Login pertama membuka Panduan Singkat di langkah 1 dari 3', /Langkah 1 dari 3/.test(dialog) && /Mulai dari peluang instansi/.test(dialog));
  await b.screenshot({ path: `${OUT}/72-panduan-desktop.png` });
  await b.waitForTimeout(300);
  const fokusDalam = await b.evaluate(() => document.querySelector('[role=alertdialog]').contains(document.activeElement));
  cek('Fokus keyboard terkunci di dalam panduan', fokusDalam);
  await b.getByRole('button', { name: 'Berikutnya' }).click();
  await b.getByRole('button', { name: 'Berikutnya' }).click();
  dialog = (await b.locator('[role=alertdialog]').innerText()).replace(/\s+/g, ' ');
  cek('Langkah terakhir memuat Pantau dan bandingkan dengan tombol selesai', /Langkah 3 dari 3/.test(dialog) && (await b.getByRole('button', { name: 'Mulai menjelajah' }).count()) === 1);
  await Promise.all([b.waitForResponse((r) => r.url().includes('/akun/panduan-selesai')), b.getByRole('button', { name: 'Mulai menjelajah' }).click()]);
  await b.waitForTimeout(300);
  cek('Selesai menutup panduan', (await b.locator('[role=alertdialog]').count()) === 0);
  await b.reload();
  await b.waitForLoadState('networkidle');
  cek('Setelah selesai, panduan tidak muncul lagi saat muat ulang', (await b.locator('[role=alertdialog]').count()) === 0);
  await b.getByRole('button', { name: 'Buka panduan singkat' }).click();
  await b.waitForSelector('[role=alertdialog]');
  cek('Panduan dapat dibuka lagi dari tombol bantuan di menu', /Langkah 1 dari 3/.test(await b.locator('[role=alertdialog]').innerText()));
  await b.keyboard.press('Escape');
  await b.waitForTimeout(250);
  cek('Esc menutup panduan', (await b.locator('[role=alertdialog]').count()) === 0);
  await ctxB.close();

  execSync('php tests/e2e/buat_member_baru.php', { cwd: REPO, stdio: 'pipe' });
  const { page: hp, ctx: ctxHp } = await baru(390, 844);
  await masuk(hp, 'baru@uji.test', 'MemberBaru12345', '/peluang-instansi');
  await hp.waitForSelector('[role=alertdialog]');
  const kotak = await hp.locator('[role=alertdialog]').boundingBox();
  cek('Panduan di HP muat di layar tanpa terpotong', kotak && kotak.x >= 0 && kotak.x + kotak.width <= 390 && kotak.y >= 0 && kotak.y + kotak.height <= 844);
  await hp.screenshot({ path: `${OUT}/73-panduan-hp.png` });
  const tombolUkuran = await hp.getByRole('button', { name: 'Berikutnya' }).boundingBox();
  cek('Tombol panduan di HP berukuran sentuh minimal 44 px', tombolUkuran.height >= 44, `${tombolUkuran.height}px`);
  await hp.getByRole('button', { name: 'Lewati' }).click();
  await ctxHp.close();

  const { page: a, ctx: ctxA } = await baru();
  await masuk(a, 'admin@uji.test', 'AdminUji12345', '/admin');
  await a.waitForLoadState('networkidle');
  const kartu = (await a.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Dasbor admin menampilkan Kesehatan sistem dengan penjadwal tidak terdeteksi', /Kesehatan sistem/.test(kartu) && /Tidak terdeteksi/.test(kartu) && /Penjadwal belum terdeteksi/.test(kartu));
  await a.screenshot({ path: `${OUT}/74-dasbor-kesehatan-desktop.png`, fullPage: true });
  execSync('php artisan schedule:run', { cwd: REPO, stdio: 'pipe' });
  await a.reload();
  await a.waitForLoadState('networkidle');
  const kartu2 = (await a.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Setelah schedule:run, penjadwal berstatus Berjalan dan peringatan hilang', /Berjalan/.test(kartu2) && !/Penjadwal belum terdeteksi/.test(kartu2));

  await a.goto(BASE + '/admin/pengaturan/situs');
  await a.getByLabel('Alamat situs').fill('bukan alamat');
  await a.getByRole('button', { name: 'Simpan pengaturan situs' }).click();
  await a.waitForSelector('#url-galat');
  cek('Alamat situs tidak valid ditolak dengan galat inline', /url/i.test(await a.locator('#url-galat').innerText()) || (await a.locator('#url-galat').count()) === 1);
  await a.getByLabel('Alamat situs').fill('https://sicasn.contoh.id');
  await a.getByRole('button', { name: 'Simpan pengaturan situs' }).click();
  dialog = await (async () => { await a.waitForSelector('[role=alertdialog]'); return (await a.locator('[role=alertdialog]').innerText()).replace(/\s+/g, ' '); })();
  cek('Alamat situs valid tersimpan dengan dialog sukses', /Pengaturan situs tersimpan/.test(dialog));
  await a.getByRole('button', { name: 'Mengerti' }).click();
  await a.reload();
  cek('Alamat situs bertahan setelah muat ulang', (await a.getByLabel('Alamat situs').inputValue()) === 'https://sicasn.contoh.id');
  await ctxA.close();

  const halamanUji = [
    ['Beranda', '/', null],
    ['Masuk', '/masuk', null],
    ['Privasi', '/privasi', null],
    ['404', '/halaman-yang-tidak-ada', null],
    ['Peluang Instansi', '/peluang-instansi', 'member'],
    ['Formasi', '/formasi', 'member'],
    ['Pantauan', '/pantauan', 'member'],
    ['Akun', '/akun', 'member'],
    ['Admin Dasbor', '/admin', 'admin'],
    ['Admin Pengguna', '/admin/pengguna', 'admin'],
    ['Admin Email', '/admin/pengaturan/email', 'admin'],
    ['Admin Situs', '/admin/pengaturan/situs', 'admin'],
  ];
  const pelanggaran = [];
  const horizontal = [];

  for (const skema of ['light', 'dark']) {
    for (const [lebar, tinggi] of [[390, 844], [820, 1180], [1366, 900]]) {
      for (const peran of [null, 'member', 'admin']) {
        const { page, ctx } = await baru(lebar, tinggi, skema);
        if (peran === 'member') await masuk(page, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
        if (peran === 'admin') await masuk(page, 'admin@uji.test', 'AdminUji12345', '/admin');

        for (const [nama, jalur] of halamanUji.filter((x) => x[2] === peran)) {
          await page.goto(BASE + jalur);
          await page.waitForLoadState('networkidle');
          await page.waitForTimeout(200);
          if (!(await tanpaScrollHorizontal(page))) horizontal.push(`${nama} ${lebar}px ${skema}`);

          if (lebar === 1366 || (lebar === 390 && skema === 'light')) {
            const temuan = await periksaAksesibilitas(page);
            temuan.forEach((t) => pelanggaran.push(`${nama} ${lebar}px ${skema}: ${t}`));
          }
        }
        await ctx.close();
      }
    }
  }

  cek('Tanpa scroll horizontal pada 12 halaman x 3 ukuran x 2 tema', horizontal.length === 0, horizontal.join('; '));
  const penting = pelanggaran.filter((p) => /\[(serious|critical)\]/.test(p));
  cek('Axe (WCAG 2.1 A/AA): tanpa pelanggaran serius atau kritis', penting.length === 0, penting.slice(0, 8).join(' || '));
  if (pelanggaran.length > penting.length) console.log('Pelanggaran lain (moderat/minor):\n  ' + pelanggaran.filter((p) => !penting.includes(p)).slice(0, 12).join('\n  '));

  cek('Tanpa galat konsol (termasuk pelanggaran CSP)', galatKonsol.length === 0, galatKonsol.slice(0, 5).join(' || '));
  await browser.close();

  const gagal = hasil.filter((x) => !x.ok);
  console.log(`\n${hasil.length - gagal.length}/${hasil.length} pemeriksaan lulus`);
  process.exit(gagal.length ? 1 : 0);
})();
