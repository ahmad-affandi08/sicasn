const { chromium } = require('playwright');
const fs = require('fs');
const os = require('os');
const path = require('path');

const OUT = process.argv[2];
const BASE = process.env.E2E_BASE || 'http://127.0.0.1:8000';
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

async function tutup(page, tombol = 'Mengerti') {
  await page.getByRole('button', { name: tombol, exact: true }).click();
  await page.waitForSelector('[role=alertdialog]', { state: 'detached' });
}

async function masuk(page, email, sandi, tujuan) {
  await page.goto(BASE + '/masuk');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill(sandi);
  await page.getByRole('button', { name: 'Masuk', exact: true }).click();
  await page.waitForURL(`**${tujuan}`);
}

(async () => {
  const browser = await chromium.launch();
  const galatKonsol = [];
  const baru = async (lebar = 1366, tinggi = 900) => {
    const ctx = await browser.newContext({ viewport: { width: lebar, height: tinggi } });
    const page = await ctx.newPage();
    page.on('console', (m) => { if (m.type() === 'error') galatKonsol.push(m.text()); });
    page.on('pageerror', (e) => galatKonsol.push(String(e)));
    return { ctx, page };
  };

  const { page: m } = await baru();
  await masuk(m, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  await m.goto(BASE + '/formasi?cari=Perawat');
  await m.waitForLoadState('networkidle');
  const kartu = m.locator('ul[aria-label="Daftar formasi"] > li').first();
  const instansi = (await kartu.locator('p').first().innerText()).trim();
  const jabatan = (await kartu.locator('h3').innerText()).trim();
  cek('Sebelum impor, kartu memakai rata-rata instansi', /bukan per jabatan/.test(await kartu.innerText()), `${instansi} / ${jabatan}`);
  cek('Tidak ada blok per jabatan sebelum impor', !/Per jabatan 2024/.test(await m.locator('main').innerText()));

  const csv = [
    'tahun,jenis_pengadaan,cakupan,instansi,kode_jabatan,nama_jabatan,lokasi,jumlah_formasi,jumlah_pelamar,jumlah_submit,jumlah_lolos_administrasi,tanggal_snapshot,status_angka,tingkat_data,keyakinan,sumber_nama,sumber_url,catatan',
    `2024,cpns,jabatan,"${instansi}",,"${jabatan}",,20,,,700,2024-09-19,akhir,B,turunan,Pengumuman Seleksi Administrasi E2E,https://contoh.id/e2e,`,
    `2024,cpns,jabatan,"${instansi}",,"${jabatan}",,20,,,650,2024-09-10,sementara,B,turunan,Rekap Awal E2E,,`,
    `2024,cpns,jabatan,"${instansi}",,,,5,,,10,2024-09-19,akhir,B,turunan,Baris rusak E2E,,`,
  ].join('\n');
  const berkas = path.join(os.tmpdir(), 'jabatan-e2e.csv');
  fs.writeFileSync(berkas, csv);

  const { page: a } = await baru();
  await masuk(a, 'admin@uji.test', 'AdminUji12345', '/admin');
  await a.goto(BASE + '/admin/statistik');
  await a.locator('#berkas').setInputFiles(berkas);
  await a.getByRole('button', { name: 'Lihat pratinjau' }).click();
  await a.waitForURL('**/admin/statistik/pratinjau/**');
  const pratinjau = (await a.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Pratinjau mengenali 2 baris valid dan 1 baris jabatan tanpa nama', /3\s*Total baris/.test(pratinjau) && /2\s*Siap diimpor/.test(pratinjau) && /1\s*Bermasalah/.test(pratinjau));
  cek('Pratinjau menjelaskan nama jabatan wajib', /Nama jabatan wajib diisi/.test(pratinjau));
  cek('Pratinjau menyebut cakupan jabatan', /jabatan/.test(pratinjau));
  await a.getByRole('button', { name: 'Impor sekarang' }).click();
  await dialogTeks(a);
  await tutup(a, 'Ya, simpan');
  cek('Impor jabatan berhasil dengan 2 baris baru', /2 baris baru/.test(await dialogTeks(a)));
  await tutup(a);

  await m.reload();
  await m.waitForLoadState('networkidle');
  const kartuSesudah = m.locator('ul[aria-label="Daftar formasi"] > li').filter({ hasText: jabatan }).first();
  const teksKartu = (await kartuSesudah.innerText()).replace(/\s+/g, ' ');
  cek('Kartu menampilkan blok Per jabatan 2024', /Per jabatan 2024/.test(teksKartu));
  cek('Kartu menyebut peserta lolos administrasi 700 dan rasio 35,0', /Lolos administrasi\s*700/.test(teksKartu) && /35,0/.test(teksKartu), teksKartu.slice(0, 160));
  cek('Kartu menegaskan lolos administrasi bukan total pendaftar', /bukan total pendaftar/.test(teksKartu));
  cek('Kartu tetap menyebut rata-rata instansi sebagai konteks', /Rata-rata instansi/.test(teksKartu));
  cek('Kartu mencantumkan tingkat data B dan sumbernya', /Data tingkat B · Pengumuman Seleksi Administrasi E2E/.test(teksKartu));
  await m.screenshot({ path: `${OUT}/50-formasi-per-jabatan-desktop.png`, fullPage: false });

  await kartuSesudah.getByRole('link', { name: /Lihat detail/ }).click();
  await m.waitForURL(/\/formasi\/\d+$/);
  await m.waitForLoadState('networkidle');
  const detail = (await m.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Detail memuat riwayat angka jabatan dengan dua snapshot', /Riwayat angka jabatan ini/.test(detail) && /Rekap Awal E2E/.test(detail) && /Pengumuman Seleksi Administrasi E2E/.test(detail));
  await m.screenshot({ path: `${OUT}/51-detail-per-jabatan-desktop.png`, fullPage: true });

  const { page: hp } = await baru(390, 844);
  await masuk(hp, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
  await hp.goto(BASE + '/formasi?cari=Perawat');
  await hp.waitForLoadState('networkidle');
  const lebarDok = await hp.evaluate(() => document.documentElement.scrollWidth);
  cek('HP: tanpa scroll horizontal dengan blok per jabatan', lebarDok <= 390, `scrollWidth ${lebarDok}`);
  await hp.screenshot({ path: `${OUT}/52-formasi-per-jabatan-hp.png`, fullPage: false });

  await a.goto(BASE + '/admin/statistik');
  await a.getByRole('button', { name: 'Batalkan impor' }).first().click();
  await dialogTeks(a);
  await tutup(a, 'Ya, batalkan impor');
  cek('Rollback impor jabatan sukses', /Impor dibatalkan/.test(await dialogTeks(a)));
  await tutup(a);

  await m.goto(BASE + '/formasi?cari=Perawat');
  await m.waitForLoadState('networkidle');
  cek('Setelah rollback kembali ke rata-rata instansi', !/Per jabatan 2024/.test(await m.locator('main').innerText()));

  const penting = galatKonsol.filter((g) => !/favicon|status of (403|419|404)/.test(g));
  cek('Tidak ada galat JavaScript di konsol browser', penting.length === 0, penting.slice(0, 3).join(' | '));

  await browser.close();
  const gagal = hasil.filter((h) => !h.ok);
  console.log(`\nRINGKASAN: ${hasil.length - gagal.length} lulus, ${gagal.length} gagal dari ${hasil.length} pemeriksaan`);
  fs.writeFileSync(`${OUT}/hasil-fase5.json`, JSON.stringify(hasil, null, 2));
  process.exit(gagal.length ? 1 : 0);
})().catch((e) => { console.error('KESALAHAN SKRIP:', e); process.exit(2); });
