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

function bantu(...argumen) {
  return execSync(`php tests/e2e/latihan_bantu.php ${argumen.join(' ')}`, { cwd: REPO }).toString().trim();
}

async function masuk(page, email, sandi, tujuan) {
  await page.goto(BASE + '/masuk');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill(sandi);
  await page.getByRole('button', { name: 'Masuk', exact: true }).click();
  await page.waitForURL(`**${tujuan}`);
  await page.waitForLoadState('networkidle');
}

async function axe(page) {
  await page.evaluate(AXE);
  const hasilAxe = await page.evaluate(() => axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'] } }));
  return hasilAxe.violations.map((v) => `${v.id}[${v.impact}] x${v.nodes.length}: ${v.nodes.slice(0, 2).map((n) => n.target.join(' ')).join(' | ')}`);
}

const tanpaScroll = (page) => page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1);

(async () => {
  execSync('php artisan cache:clear', { cwd: REPO, stdio: 'pipe' });
  bantu('bersihkan');
  const browser = await chromium.launch();
  const galatKonsol = [];
  const baru = async (lebar = 1366, tinggi = 900, skema = 'light') => {
    const ctx = await browser.newContext({ viewport: { width: lebar, height: tinggi }, colorScheme: skema });
    const page = await ctx.newPage();
    page.on('console', (m) => { if (m.type() === 'error' && !/status of (403|404)/.test(m.text())) galatKonsol.push(`${page.url()} :: ${m.text()}`); });
    page.on('pageerror', (e) => galatKonsol.push(String(e)));
    return { ctx, page };
  };

  const { page: m, ctx: ctxM } = await baru();
  await masuk(m, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  await m.getByRole('link', { name: 'Latihan' }).first().click();
  await m.waitForURL('**/latihan');
  await m.waitForLoadState('networkidle');
  const beranda = (await m.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Menu Latihan tersedia dan beranda menampilkan empat mode', /Simulasi SKD lengkap/.test(beranda) && /Latihan TWK/.test(beranda) && /Latihan TIU/.test(beranda) && /Latihan TKP/.test(beranda));
  cek('Beranda menampilkan ambang resmi dan jumlah bank soal', /166/.test(beranda) && /Bank soal: \d+ soal/.test(beranda));
  cek('Beranda menyatakan soal buatan SICASN, bukan soal asli', /bukan soal ujian asli/.test(beranda));
  await m.screenshot({ path: `${OUT}/80-latihan-beranda-desktop.png`, fullPage: true });

  const modeTiu = m.getByRole('heading', { name: 'Latihan TIU' }).locator('xpath=ancestor::li[1]');
  await modeTiu.getByRole('button', { name: 'Mulai' }).click();
  await m.waitForURL(/\/latihan\/\d+$/);
  await m.waitForLoadState('networkidle');
  const percobaanId = Number(m.url().split('/').pop());
  cek('Memulai latihan TIU membuka halaman mengerjakan dengan 20 soal dan timer 30 menit', (await m.locator('nav[aria-label="Daftar soal"] ol li').count()) === 20 && /^(29|30):\d\d$/.test((await m.getByRole('timer').innerText()).trim()), await m.getByRole('timer').innerText());
  const html = await m.content();
  cek('Halaman mengerjakan tidak memuat pembahasan atau skor opsi', !/pembahasan/i.test(html) && !/"skor"/.test(html));

  const kunci = JSON.parse(bantu('kunci', percobaanId));
  const kerjakan = async (urutan, teksOpsi) => {
    const peta = m.locator('nav[aria-label="Daftar soal"] ol li').nth(urutan - 1).getByRole('button');
    await peta.click();
    await m.getByText(`Soal ${urutan} dari 20`).waitFor();
    const respons = m.waitForResponse((r) => r.url().includes('/jawaban') && r.request().method() === 'PUT');
    await m.locator('fieldset label').filter({ has: m.getByText(teksOpsi, { exact: true }) }).first().click();
    return (await respons).status();
  };

  const status = [];
  for (let i = 1; i <= 10; i += 1) status.push(await kerjakan(i, kunci[i].benar));
  for (let i = 11; i <= 15; i += 1) status.push(await kerjakan(i, kunci[i].salah));
  cek('Setiap jawaban tersimpan ke server dengan status 200', status.every((s) => s === 200), status.join(','));

  await m.locator('nav[aria-label="Daftar soal"] ol li').nth(15).getByRole('button').click();
  await m.getByRole('checkbox', { name: 'Ragu-ragu' }).check();
  await m.waitForResponse((r) => r.url().includes('/jawaban'));
  cek('Daftar soal menandai dijawab, ragu-ragu, dan belum dijawab lewat teks aksesibel', (await m.getByRole('button', { name: 'Soal 1, dijawab', exact: true }).count()) === 1 && (await m.getByRole('button', { name: 'Soal 16, dijawab dan ragu-ragu' }).count()) === 1 && (await m.getByRole('button', { name: 'Soal 20, belum dijawab' }).count()) === 1);
  await m.screenshot({ path: `${OUT}/81-latihan-kerjakan-desktop.png` });

  await m.reload();
  await m.waitForLoadState('networkidle');
  cek('Jawaban tetap ada setelah halaman dimuat ulang', (await m.getByRole('button', { name: 'Soal 10, dijawab', exact: true }).count()) === 1 && (await m.getByRole('button', { name: 'Soal 16, dijawab dan ragu-ragu' }).count()) === 1);

  const bahasaHasil = await m.goto(`${BASE}/latihan/${percobaanId}/hasil`);
  await m.waitForLoadState('networkidle');
  cek('Hasil belum dibuka sebelum latihan diselesaikan (dialihkan kembali)', m.url().endsWith(`/latihan/${percobaanId}`) && bahasaHasil !== null);

  const { page: b, ctx: ctxB } = await baru();
  await masuk(b, 'baru@uji.test', 'MemberBaru12345', '/peluang-instansi');
  const orangLain = await b.goto(`${BASE}/latihan/${percobaanId}`);
  cek('Percobaan milik orang lain menjawab 404 ramah', orangLain.status() === 404);
  await ctxB.close();

  await m.goto(BASE + '/latihan');
  await m.waitForLoadState('networkidle');
  cek('Beranda mengajak melanjutkan latihan yang berjalan dan mengunci mode lain', (await m.getByRole('link', { name: 'Lanjutkan latihan' }).count()) === 1 && (await m.getByRole('button', { name: 'Mulai' }).first().isDisabled()));
  await m.getByRole('link', { name: 'Lanjutkan latihan' }).click();
  await m.waitForURL(/\/latihan\/\d+$/);
  await m.waitForLoadState('networkidle');

  const temuanKerjakan = await axe(m);
  cek('Axe pada halaman mengerjakan: tanpa pelanggaran serius/kritis', temuanKerjakan.filter((t) => /\[(serious|critical)\]/.test(t)).length === 0, temuanKerjakan.join(' || '));

  await m.getByRole('button', { name: 'Selesaikan latihan' }).first().click();
  await m.waitForSelector('[role=alertdialog]');
  const konfirmasi = (await m.locator('[role=alertdialog]').innerText()).replace(/\s+/g, ' ');
  cek('Konfirmasi menyebut 5 soal belum dijawab dan 1 ragu-ragu', /5 soal belum dijawab dan 1 soal ditandai ragu-ragu/.test(konfirmasi), konfirmasi);
  await m.getByRole('button', { name: 'Ya, selesaikan' }).click();
  await m.waitForURL(`**/latihan/${percobaanId}/hasil`);
  await m.waitForLoadState('networkidle');

  const teksHasil = (await m.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Skor TIU = 10 benar x 5 = 50 dari 100', /50 dari 100/.test(teksHasil) && /Benar \(10\)|Benar\s*\(10\)/.test(teksHasil + ' ' + (await m.getByRole('button', { name: /^Benar/ }).innerText())), teksHasil.slice(0, 200));
  const baris = (await m.locator('tbody tr').first().innerText()).replace(/\s+/g, ' ');
  cek('Tabel skor: 10 benar, 5 salah, 5 kosong', /TIU/.test(baris) && /50/.test(baris) && /10\s+5\s+5/.test(baris), baris);
  cek('Latihan per kategori tidak menyatakan lulus atau tidak lulus', !/Memenuhi ambang di ketiga tes|Belum memenuhi ambang pada/.test(teksHasil));
  cek('Pembahasan, kunci jawaban, dan sumber tampil', (await m.getByText('Kunci jawaban').count()) >= 20 && (await m.getByText(/Sumber:/).count()) === 20 && (await m.getByText('Pembahasan', { exact: true }).count()) >= 20);
  await m.getByRole('button', { name: /^Salah \(5\)$/ }).click();
  cek('Filter Salah menampilkan tepat 5 soal', (await m.locator('article').count()) === 5);
  await m.getByRole('button', { name: /^Kosong \(5\)$/ }).click();
  cek('Filter Kosong menampilkan tepat 5 soal', (await m.locator('article').count()) === 5);
  await m.getByRole('button', { name: /^Semua soal/ }).click();
  await m.screenshot({ path: `${OUT}/82-latihan-hasil-desktop.png`, fullPage: true });
  const temuanHasil = await axe(m);
  cek('Axe pada halaman hasil: tanpa pelanggaran serius/kritis', temuanHasil.filter((t) => /\[(serious|critical)\]/.test(t)).length === 0, temuanHasil.join(' || '));

  await m.goto(BASE + '/latihan');
  await m.waitForLoadState('networkidle');
  const riwayat = (await m.getByRole('list', { name: 'Riwayat latihan' }).innerText()).replace(/\s+/g, ' ');
  cek('Riwayat mencatat latihan yang selesai dengan skor', /Latihan TIU/.test(riwayat) && /TIU 50/.test(riwayat), riwayat);

  await m.getByRole('heading', { name: 'Simulasi SKD lengkap' }).locator('xpath=ancestor::li[1]').getByRole('button', { name: 'Mulai' }).click();
  await m.waitForURL(/\/latihan\/\d+$/);
  await m.waitForLoadState('networkidle');
  const simulasiId = Number(m.url().split('/').pop());
  cek('Simulasi lengkap: 110 soal dengan timer 100 menit', (await m.locator('nav[aria-label="Daftar soal"] ol li').count()) === 110 && /^(1:39:\d\d|1:40:00)$/.test((await m.getByRole('timer').innerText()).trim()), await m.getByRole('timer').innerText());
  cek('Soal pertama bertipe TWK dan soal ke-31 bertipe TIU', /TWK/.test(await m.locator('section').first().innerText()));
  await m.locator('nav[aria-label="Daftar soal"] ol li').nth(30).getByRole('button').click();
  await m.getByText('Soal 31 dari 110').waitFor();
  cek('Urutan seperti CAT: TWK, lalu TIU', /TIU/.test(await m.locator('section').first().innerText()));

  bantu('kedaluwarsa', simulasiId);
  await m.reload();
  await m.waitForURL(`**/latihan/${simulasiId}/hasil`);
  await m.waitForLoadState('networkidle');
  const hasilSim = (await m.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Waktu habis: latihan diselesaikan otomatis saat dibuka dan dinilai', /Simulasi SKD lengkap/.test(hasilSim) && /Belum memenuhi ambang pada TWK, TIU, TKP/.test(hasilSim), hasilSim.slice(0, 160));
  const skorSim = JSON.parse(bantu('skor', simulasiId));
  cek('Skor tersimpan di basis data dengan waktu selesai', skorSim.selesai_at !== null && skorSim.jumlah_twk === 30 && skorSim.jumlah_tiu === 35 && skorSim.jumlah_tkp === 45);
  await ctxM.close();

  bantu('bersihkan');
  const { page: hp, ctx: ctxHp } = await baru(390, 844);
  await masuk(hp, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  const tinggiNav = await hp.evaluate(() => Array.from(document.querySelectorAll('nav[aria-label] a')).map((a) => a.getBoundingClientRect().height));
  cek('Navigasi bawah HP punya lima menu dengan target sentuh minimal 44 px', (await hp.locator('nav[aria-label="Navigasi utama"] a, nav a').count()) >= 5 && tinggiNav.filter((t) => t > 0).every((t) => t >= 44), tinggiNav.join(','));
  await hp.goto(BASE + '/latihan');
  await hp.waitForLoadState('networkidle');
  cek('Beranda latihan di HP tanpa scroll horizontal', await tanpaScroll(hp));
  await hp.screenshot({ path: `${OUT}/83-latihan-beranda-hp.png`, fullPage: true });
  await hp.getByRole('heading', { name: 'Latihan TKP' }).locator('xpath=ancestor::li[1]').getByRole('button', { name: 'Mulai' }).click();
  await hp.waitForURL(/\/latihan\/\d+$/);
  await hp.waitForLoadState('networkidle');
  cek('Halaman mengerjakan di HP tanpa scroll horizontal', await tanpaScroll(hp));
  const tinggiOpsi = await hp.evaluate(() => Array.from(document.querySelectorAll('fieldset label')).map((l) => l.getBoundingClientRect().height));
  cek('Pilihan jawaban di HP berukuran sentuh minimal 44 px', tinggiOpsi.length === 5 && tinggiOpsi.every((t) => t >= 44), tinggiOpsi.join(','));
  const tinggiPeta = await hp.evaluate(() => Array.from(document.querySelectorAll('nav[aria-label="Daftar soal"] button')).map((b) => b.getBoundingClientRect().height));
  cek('Tombol daftar soal di HP berukuran sentuh minimal 44 px', tinggiPeta.length === 20 && tinggiPeta.every((t) => t >= 44), String(Math.min(...tinggiPeta)));
  await hp.screenshot({ path: `${OUT}/84-latihan-kerjakan-hp.png` });
  await hp.getByRole('button', { name: 'Selesaikan latihan' }).first().click();
  await hp.getByRole('button', { name: 'Ya, selesaikan' }).click();
  await hp.waitForURL(/\/hasil$/);
  await hp.waitForLoadState('networkidle');
  cek('Hasil di HP tanpa scroll horizontal', await tanpaScroll(hp));
  await hp.screenshot({ path: `${OUT}/85-latihan-hasil-hp.png`, fullPage: true });
  await ctxHp.close();

  bantu('bersihkan');
  const pelanggaran = [];
  const horizontal = [];
  for (const skema of ['light', 'dark']) {
    for (const [lebar, tinggi] of [[390, 844], [820, 1180], [1366, 900]]) {
      const { page, ctx } = await baru(lebar, tinggi, skema);
      await masuk(page, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
      await page.goto(BASE + '/latihan');
      await page.waitForLoadState('networkidle');
      if (!(await tanpaScroll(page))) horizontal.push(`beranda ${lebar} ${skema}`);
      (await axe(page)).forEach((t) => pelanggaran.push(`beranda ${lebar} ${skema}: ${t}`));
      await page.getByRole('heading', { name: 'Latihan TKP' }).locator('xpath=ancestor::li[1]').getByRole('button', { name: 'Mulai' }).click();
      await page.waitForURL(/\/latihan\/\d+$/);
      await page.waitForLoadState('networkidle');
      if (!(await tanpaScroll(page))) horizontal.push(`kerjakan ${lebar} ${skema}`);
      (await axe(page)).forEach((t) => pelanggaran.push(`kerjakan ${lebar} ${skema}: ${t}`));
      await page.getByRole('button', { name: 'Selesaikan latihan' }).first().click();
      await page.getByRole('button', { name: 'Ya, selesaikan' }).click();
      await page.waitForURL(/\/hasil$/);
      await page.waitForLoadState('networkidle');
      if (!(await tanpaScroll(page))) horizontal.push(`hasil ${lebar} ${skema}`);
      (await axe(page)).forEach((t) => pelanggaran.push(`hasil ${lebar} ${skema}: ${t}`));
      await ctx.close();
    }
  }
  cek('Tanpa scroll horizontal: 3 halaman latihan x 3 ukuran x 2 tema', horizontal.length === 0, horizontal.join('; '));
  const penting = pelanggaran.filter((p) => /\[(serious|critical)\]/.test(p));
  cek('Axe: 3 halaman x 3 ukuran x 2 tema tanpa pelanggaran serius/kritis', penting.length === 0, penting.slice(0, 6).join(' || '));
  if (pelanggaran.length > penting.length) console.log('Pelanggaran lain:\n  ' + pelanggaran.filter((p) => !penting.includes(p)).slice(0, 10).join('\n  '));
  cek('Tanpa galat konsol (termasuk CSP)', galatKonsol.length === 0, galatKonsol.slice(0, 4).join(' || '));
  await browser.close();

  const gagal = hasil.filter((x) => !x.ok);
  console.log(`\n${hasil.length - gagal.length}/${hasil.length} pemeriksaan lulus`);
  process.exit(gagal.length ? 1 : 0);
})();
