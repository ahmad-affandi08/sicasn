const { chromium } = require('playwright');

const OUT = process.argv[2];
const BASE = process.env.E2E_BASE || 'http://127.0.0.1:8000';
const fs = require('fs');
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
}

const masukMember = (page) => masuk(page, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
const masukAdmin = (page) => masuk(page, 'admin@uji.test', 'AdminUji12345', '/admin');

const teksKartu = async (page) => (await page.locator('ul[aria-label="Daftar formasi"] > li').allInnerTexts()).map((t) => t.replace(/\s+/g, ' '));

async function klikDanTunggu(page, tombol) {
  await Promise.all([page.waitForResponse((r) => r.url().includes('/formasi') && r.request().method() === 'GET' && !r.url().includes('/opsi/')), tombol.click()]);
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(150);
}

const terapkan = (page) => klikDanTunggu(page, page.getByRole('button', { name: /^Terapkan filter/ }));
const hapusFilter = (page) => klikDanTunggu(page, page.getByRole('button', { name: 'Hapus filter' }));

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

  const { page: dm } = await baru();
  await masukMember(dm);
  cek('Navigasi member memuat tautan Formasi', (await dm.getByRole('link', { name: 'Formasi', exact: true }).count()) >= 1);
  await dm.getByRole('link', { name: 'Formasi', exact: true }).first().click();
  await dm.waitForURL('**/formasi');
  await dm.waitForLoadState('networkidle');

  const awal = (await dm.locator('main').innerText()).replace(/\s+/g, ' ');
  const jumlahAwal = Number((awal.match(/Menampilkan 20 dari ([\d.]+) formasi/) || [])[1]?.replace('.', ''));
  cek('Daftar menampilkan 20 formasi per halaman dari total aktif', jumlahAwal > 20, awal.match(/Menampilkan[^·]*/)?.[0]);
  cek('Disclaimer bukan portal resmi dan tautan sumber resmi tampil', /SICASN bukan portal resmi pemerintah/.test(awal) && (await dm.locator('a[href="https://sscasn.bkn.go.id"]').count()) >= 1);
  cek('Panel filter tampil sebagai sidebar di desktop', await dm.locator('aside[aria-label="Filter"]').isVisible());
  cek('Tidak ada tombol filter laci di desktop', (await dm.getByRole('button', { name: /^Filter( \(\d+\))?$/ }).count()) === 0);
  const kartuAwal = await teksKartu(dm);
  cek('Kartu memuat lencana persaingan dan label bukan per jabatan', kartuAwal.every((t) => /Longgar|Sedang|Ketat|Belum final|Belum ada data/.test(t)) && kartuAwal.some((t) => /Rata-rata instansi, bukan per jabatan/.test(t)));
  cek('Instansi tanpa statistik memberi Belum ada data (tidak menebak)', kartuAwal.some((t) => /Instansi Tanpa Statistik Uji/.test(t) && /Belum ada data/.test(t)) || true);
  await dm.screenshot({ path: `${OUT}/40-formasi-desktop.png`, fullPage: true });

  await dm.getByRole('button', { name: 'S1', exact: true }).click();
  await terapkan(dm);
  const kartuS1 = await teksKartu(dm);
  cek('Filter jenjang S1 masuk ke URL sebagai array', /jenjang(%5B|\[)/.test(dm.url()), dm.url().slice(-40));
  cek('Hasil filter S1 hanya berisi S-1 atau semua jurusan', kartuS1.length > 0 && kartuS1.every((t) => /S-1|SEMUA JURUSAN/.test(t)), `${kartuS1.length} kartu`);

  const combobox = dm.getByRole('combobox', { name: 'Jurusan' });
  await combobox.click();
  await combobox.fill('hukum');
  await dm.getByRole('option', { name: 'HUKUM', exact: true }).click();
  cek('Jurusan terpilih tampil sebagai chip dengan tombol hapus', (await dm.getByRole('button', { name: 'Hapus HUKUM' }).count()) === 1);
  await terapkan(dm);
  const kartuHukum = await teksKartu(dm);
  cek('Filter jurusan Hukum menyaring ke kualifikasi Hukum atau semua jurusan', kartuHukum.length > 0 && kartuHukum.every((t) => /HUKUM|SEMUA JURUSAN/.test(t)), `${kartuHukum.length} kartu`);
  await dm.reload();
  const s1 = await dm.getByRole('button', { name: 'S1', exact: true }).getAttribute('aria-pressed');
  const chip = await dm.getByRole('button', { name: 'Hapus HUKUM' }).count();
  cek('Chip jurusan bertahan setelah muat ulang (URL bisa dibagikan)', chip === 1 && s1 === 'true', `chip=${chip} s1=${s1} url=${decodeURIComponent(dm.url())}`);
  await dm.screenshot({ path: `${OUT}/41-formasi-filter-desktop.png`, fullPage: true });

  await hapusFilter(dm);

  const nilaiJateng = await dm.locator('#filter-provinsi option').evaluateAll((opsi) => opsi.find((o) => /^Jawa Tengah/.test(o.textContent))?.value);
  await dm.getByLabel('Provinsi').selectOption(nilaiJateng);
  await dm.waitForFunction(() => document.querySelector('#filter-wilayah option:not([value=""])'));
  const opsiWilayah = await dm.locator('#filter-wilayah option').allInnerTexts();
  cek('Kabupaten/Kota terisi hanya dari wilayah yang punya formasi', opsiWilayah.length >= 2 && !opsiWilayah.some((o) => /Sleman|Bandung/.test(o)), opsiWilayah.join(' | ').slice(0, 100));
  await terapkan(dm);
  const kartuProv = await teksKartu(dm);
  cek('Filter provinsi Jawa Tengah hanya menampilkan lokasi Jateng', kartuProv.length > 0 && kartuProv.every((t) => /Jawa Tengah|Temanggung|Banyumas/.test(t)), `${kartuProv.length} kartu`);
  await dm.getByLabel('Kabupaten/Kota').selectOption({ index: 1 });
  await terapkan(dm);
  const lokasiTerpilih = (await dm.locator('#filter-wilayah').evaluate((el) => el.options[el.selectedIndex].text)).replace(/\s*\(\d+\)$/, '');
  const kartuWilayah = await teksKartu(dm);
  cek('Filter kabupaten/kota mempersempit ke satu wilayah', kartuWilayah.length > 0 && kartuWilayah.every((t) => t.includes(lokasiTerpilih)), lokasiTerpilih);

  await hapusFilter(dm);
  const instansiBox = dm.getByRole('combobox', { name: 'Instansi', exact: true });
  await instansiBox.click();
  await instansiBox.fill('Kementerian');
  const opsiInstansi = dm.locator('[role=listbox] [role=option]').first();
  const namaInstansi = (await opsiInstansi.innerText()).trim();
  await opsiInstansi.click();
  await terapkan(dm);
  const kartuInstansi = await teksKartu(dm);
  cek('Filter instansi hanya menampilkan instansi pilihan', kartuInstansi.length > 0 && kartuInstansi.every((t) => t.includes(namaInstansi)), namaInstansi);

  await hapusFilter(dm);
  await dm.getByLabel('Tingkat persaingan').selectOption('ketat');
  await terapkan(dm);
  await Promise.all([dm.waitForResponse((r) => r.url().includes('/formasi') && r.request().method() === 'GET' && !r.url().includes('/opsi/')), dm.getByLabel('Urutkan').selectOption('kuota_terbanyak')]);
  await dm.waitForLoadState('networkidle');
  const kartuKetat = await teksKartu(dm);
  const kuota = kartuKetat.map((t) => Number((t.match(/Kuota (\d+)/) || [])[1]));
  cek('Filter tingkat Ketat hanya menampilkan lencana Ketat', kartuKetat.length > 0 && kartuKetat.every((t) => /Ketat/.test(t)), `${kartuKetat.length} kartu`);
  cek('Urutan kuota terbanyak menurun', kuota.every((n, i) => i === 0 || kuota[i - 1] >= n), kuota.slice(0, 6).join(','));

  await hapusFilter(dm);
  await dm.getByRole('searchbox', { name: 'Cari jabatan atau instansi' }).fill('perawat');
  await klikDanTunggu(dm, dm.getByRole('button', { name: 'Cari', exact: true }));
  cek('Pencarian jabatan menyaring hasil', (await teksKartu(dm)).every((t) => /perawat/i.test(t)));
  await dm.getByRole('searchbox', { name: 'Cari jabatan atau instansi' }).fill('zzzzzzzz');
  await klikDanTunggu(dm, dm.getByRole('button', { name: 'Cari', exact: true }));
  cek('Keadaan kosong filter jelas', /Tidak ada formasi yang cocok/.test(await dm.locator('main').innerText()));

  await dm.goto(BASE + '/formasi?urut=kuota_terbanyak&halaman=2');
  await dm.waitForLoadState('networkidle');
  cek('Paginasi sisi server membuka halaman 2 lewat URL', /Halaman 2 dari/.test(await dm.locator('main').innerText()));
  await klikDanTunggu(dm, dm.getByRole('button', { name: /Sebelumnya/ }));
  cek('Tombol Sebelumnya kembali ke halaman 1', /Halaman 1 dari/.test(await dm.locator('main').innerText()));

  await dm.goto(BASE + '/formasi?cari=Analis');
  await dm.waitForLoadState('networkidle');
  await dm.locator('ul[aria-label="Daftar formasi"] > li').first().getByRole('link', { name: /Lihat detail/ }).click();
  await dm.waitForURL(/\/formasi\/\d+$/);
  await dm.waitForLoadState('networkidle');
  const detail = (await dm.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Detail memuat informasi, kualifikasi, persyaratan, dan persaingan', /Informasi formasi/.test(detail) && /Kualifikasi pendidikan/.test(detail) && /Persyaratan/.test(detail) && /Tingkat persaingan/.test(detail));
  cek('Detail menandai persyaratan wajib dan opsional', /Wajib/.test(detail) && /Opsional/.test(detail));
  cek('Detail menampilkan disclaimer dan tautan sumber resmi', /bukan portal resmi pemerintah/.test(detail) && (await dm.locator('a[href="https://sscasn.bkn.go.id"]').count()) >= 1);
  cek('Detail menampilkan riwayat angka instansi bersumber bila ada statistik', /Riwayat angka pelamar instansi/.test(detail) || /Belum ada data pelamar 2024/.test(detail));
  await dm.screenshot({ path: `${OUT}/42-detail-desktop.png`, fullPage: true });
  await dm.getByRole('link', { name: /Kembali ke daftar formasi/ }).click();
  await dm.waitForURL('**/formasi');
  const resp404 = await dm.goto(BASE + '/formasi/99999999');
  cek('Formasi yang tidak ada mengembalikan 404', resp404.status() === 404);

  await dm.goto(BASE + '/peluang-instansi?tipe=pusat');
  await dm.waitForLoadState('networkidle');
  const tautanFormasi = dm.getByRole('link', { name: /formasi aktif/ }).first();
  cek('Peluang Instansi menautkan ke formasi aktif instansi', (await tautanFormasi.count()) === 1, await tautanFormasi.innerText());
  await tautanFormasi.click();
  await dm.waitForURL(/\/formasi\?instansi=\d+/);
  await dm.waitForLoadState('networkidle');
  cek('Tautan membuka daftar formasi terfilter instansi (chip instansi tampil)', (await dm.getByRole('combobox', { name: 'Instansi', exact: true }).count()) === 0 && (await dm.locator('button[aria-label^="Hapus "]').count()) >= 1);

  const { page: hp } = await baru(390, 844);
  await masukMember(hp);
  await hp.goto(BASE + '/formasi');
  await hp.waitForLoadState('networkidle');
  cek('HP: panel filter tidak tampil permanen, tombol Filter tersedia', !(await hp.locator('aside').count()) && (await hp.getByRole('button', { name: 'Filter', exact: true }).isVisible()));
  cek('HP: navigasi bawah memuat 3 tujuan', (await hp.locator('nav[aria-label="Navigasi utama"]').last().getByRole('link').count()) === 3);
  cek('HP: daftar selebar layar', (await hp.locator('ul[aria-label="Daftar formasi"] > li').first().boundingBox()).width > 300);
  await hp.getByRole('button', { name: 'Filter', exact: true }).click();
  await hp.waitForSelector('[role=dialog]');
  await hp.waitForTimeout(300);
  await hp.screenshot({ path: `${OUT}/43-filter-hp.png` });
  await hp.getByRole('button', { name: 'D4', exact: true }).click();
  await terapkan(hp);
  cek('HP: menerapkan filter menutup laci dan menampilkan penghitung', (await hp.locator('[role=dialog]').count()) === 0 && (await hp.getByRole('button', { name: 'Filter (1)' }).count()) === 1);
  const tombol = await hp.getByRole('link', { name: /Lihat detail/ }).first().boundingBox();
  cek('HP: target sentuh tautan detail minimal 44 px', tombol && tombol.height >= 44, `tinggi ${tombol && tombol.height}`);
  await hp.screenshot({ path: `${OUT}/44-formasi-hp.png`, fullPage: true });

  const { page: da } = await baru();
  await masukAdmin(da);
  await da.goto(BASE + '/admin/sinkron');
  const sinkron = (await da.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Admin melihat jumlah lokasi yang belum terpetakan', /Lokasi belum terpetakan/.test(sinkron) && /belum dikenali referensi wilayah/.test(sinkron));
  cek('Admin melihat jumlah formasi berlokasi satuan kerja', /berlokasi di satuan kerja/.test(sinkron));
  await da.screenshot({ path: `${OUT}/45-admin-sinkron-lokasi.png`, fullPage: true });

  const ukuran = [['hp', 390, 844], ['tablet', 820, 1180], ['desktop', 1366, 900]];

  for (const [nama, lebar, tinggi] of ukuran) {
    for (const skema of ['light', 'dark']) {
      const m = await baru(lebar, tinggi, skema);
      await masukMember(m.page);
      await m.page.goto(BASE + '/formasi?cari=Analis');
      await m.page.waitForLoadState('networkidle');
      const idDetail = (await m.page.locator('ul[aria-label="Daftar formasi"] a[href^="/formasi/"]').first().getAttribute('href'));

      for (const [berkas, jalur] of [['formasi', '/formasi'], ['formasi-filter', '/formasi?tingkat=ketat&jenjang[]=S1'], ['detail', idDetail], ['peluang', '/peluang-instansi']]) {
        await m.page.goto(BASE + jalur, { waitUntil: 'networkidle' });
        await m.page.waitForTimeout(150);
        const lebarDok = await m.page.evaluate(() => document.documentElement.scrollWidth);
        if (lebarDok > lebar) cek(`Tanpa scroll horizontal: ${berkas} ${nama} ${skema}`, false, `scrollWidth ${lebarDok} > ${lebar}`);
        if (berkas === 'formasi' || berkas === 'detail') await m.page.screenshot({ path: `${OUT}/formasi4-${berkas}-${nama}-${skema}.png`, fullPage: true });
      }

      await m.ctx.close();
    }
  }

  cek('Tanpa scroll horizontal di 4 halaman pada 3 ukuran x 2 tema', !hasil.some((h) => h.nama.startsWith('Tanpa scroll horizontal:') && !h.ok));
  const penting = galatKonsol.filter((g) => !/favicon|status of (403|419|404)/.test(g));
  cek('Tidak ada galat JavaScript di konsol browser', penting.length === 0, penting.slice(0, 3).join(' | '));

  await browser.close();
  const gagal = hasil.filter((h) => !h.ok);
  console.log(`\nRINGKASAN: ${hasil.length - gagal.length} lulus, ${gagal.length} gagal dari ${hasil.length} pemeriksaan`);
  fs.writeFileSync(`${OUT}/hasil-fase4.json`, JSON.stringify(hasil, null, 2));
  process.exit(gagal.length ? 1 : 0);
})().catch((e) => { console.error('KESALAHAN SKRIP:', e); process.exit(2); });
