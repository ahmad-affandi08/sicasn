const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const OUT = process.argv[2];
const REPO = process.cwd();
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

const masukMember = (page) => masuk(page, 'member@uji.test', 'MemberUji12345', '/peluang-instansi');
const masukAdmin = (page) => masuk(page, 'admin@uji.test', 'AdminUji12345', '/admin');

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

  cek('Member diarahkan ke Peluang Instansi setelah login', dm.url().endsWith('/peluang-instansi'));
  const teks = (await dm.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Disclaimer bukan prediksi dan bukan portal resmi tampil', /bukan prediksi dan bukan jaminan/.test(teks) && /bukan portal resmi pemerintah/.test(teks));
  cek('Tautan sumber resmi sscasn.bkn.go.id ada', (await dm.locator('a[href="https://sscasn.bkn.go.id"]').count()) === 1);
  cek('Total pelamar nasional 3.963.832 tampil dengan sumber', /3\.963\.832/.test(teks) && /Resmi/.test(teks));
  cek('Jumlah instansi 70 tercatat', /dari 70 instansi/.test(teks), (teks.match(/Menampilkan [\d.]+ dari [\d.]+ instansi/) || [''])[0]);
  cek('Ambang persaingan dijelaskan', /Longgar bila rasio di bawah 5, Ketat bila di atas 20/.test(teks));
  cek('Tabel padat tampil di desktop dan kartu tersembunyi', (await dm.locator('table').isVisible()) && !(await dm.locator('ul.grid').isVisible()));

  const barisPertama = (await dm.locator('tbody tr').first().innerText()).replace(/\s+/g, ' ');
  cek('Peringkat awal tidak dimulai dari angka sementara (Belum final)', !/Belum final/.test(barisPertama), barisPertama.slice(0, 90));
  await dm.screenshot({ path: `${OUT}/30-peluang-desktop.png`, fullPage: true });

  await dm.getByRole('button', { name: /^Formasi$/ }).click();
  await dm.waitForURL('**urut=formasi_terbanyak');
  const pertamaFormasi = (await dm.locator('tbody tr').first().innerText()).replace(/\s+/g, ' ');
  cek('Mengurutkan menurut formasi terbanyak lewat kepala kolom', /Agama|Hukum/.test(pertamaFormasi), pertamaFormasi.slice(0, 80));

  await dm.getByRole('searchbox', { name: 'Cari nama instansi' }).fill('Agama');
  await dm.getByRole('button', { name: 'Cari', exact: true }).click();
  await dm.waitForURL('**cari=Agama**');
  const hasilCari = (await dm.locator('tbody tr').allInnerTexts()).join(' ');
  cek('Pencarian nama menyaring daftar dan masuk ke URL', /Agama/.test(hasilCari) && (await dm.locator('tbody tr').count()) <= 3);
  const urlCari = dm.url();
  await dm.reload();
  cek('URL hasil pencarian bisa dimuat ulang dan tetap terisi', (await dm.getByRole('searchbox', { name: 'Cari nama instansi' }).inputValue()) === 'Agama' && dm.url() === urlCari);

  await dm.goto(BASE + '/peluang-instansi?tingkat=ketat');
  const semuaKetat = (await dm.locator('tbody tr').allInnerTexts()).every((t) => /Ketat/.test(t));
  cek('Filter tingkat Ketat hanya menampilkan lencana Ketat', semuaKetat && (await dm.locator('tbody tr').count()) > 5);

  await dm.goto(BASE + '/peluang-instansi?tipe=pusat&cari=Hak%20Asasi');
  await dm.locator('tbody tr').first().getByRole('button', { name: 'Lihat rincian' }).click();
  await dm.waitForSelector('[role=dialog]');
  await dm.waitForTimeout(300);
  const rincian = (await dm.locator('[role=dialog]').innerText()).replace(/\s+/g, ' ');
  cek('Rincian menampilkan riwayat snapshot, tingkat data, dan sumber', /Riwayat angka/.test(rincian) && /Tingkat data A/.test(rincian) && /Sumber/.test(rincian), rincian.slice(0, 120));
  cek('Rincian menyebut 3 snapshot Kemenkumham', (rincian.match(/Tingkat data A/g) || []).length >= 3);
  await dm.screenshot({ path: `${OUT}/31-rincian-desktop.png` });
  await dm.keyboard.press('Escape');

  await dm.goto(BASE + '/peluang-instansi?cari=zzzzzz');
  cek('Keadaan kosong jelas saat pencarian tak cocok', /Tidak ada instansi yang cocok/.test(await dm.locator('main').innerText()));
  await dm.goto(BASE + '/peluang-instansi?tingkat=asal');
  cek('Parameter tidak valid diarahkan ke halaman bersih', dm.url().endsWith('/peluang-instansi'));

  const { page: hp } = await baru(390, 844);
  await masukMember(hp);
  cek('HP menampilkan kartu, bukan tabel', (await hp.locator('ul.grid').isVisible()) && !(await hp.locator('table').isVisible()));
  cek('HP menampilkan navigasi bawah', await hp.locator('nav[aria-label="Navigasi utama"]').last().isVisible());
  cek('Opsi filter tersembunyi di HP sampai tombol Filter ditekan', !(await hp.locator('#opsi-filter').isVisible()));
  await hp.getByRole('button', { name: 'Filter', exact: true }).click();
  cek('Tombol Filter membuka opsi di HP', await hp.locator('#opsi-filter').isVisible());
  await hp.getByLabel('Tingkat persaingan').selectOption('longgar');
  await hp.waitForURL('**tingkat=longgar');
  cek('Tombol Filter menampilkan jumlah filter aktif setelah memilih', (await hp.getByRole('button', { name: /^Filter \(1\)$/ }).count()) === 1);
  const tombolRincian = hp.getByRole('button', { name: /Lihat rincian/ }).first();
  const kotak = await tombolRincian.boundingBox();
  cek('Target sentuh tombol rincian di HP minimal 44 px', kotak && kotak.height >= 44, `tinggi ${kotak && kotak.height}`);
  await hp.screenshot({ path: `${OUT}/32-peluang-hp.png`, fullPage: true });

  const adminKonteks = await baru();
  const da = adminKonteks.page;
  await masukAdmin(da);
  await da.goto(BASE + '/admin/statistik');
  const awalTeks = (await da.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Halaman Statistik menampilkan 95 baris seed', /95\s*Baris statistik tersimpan/.test(awalTeks));
  cek('Riwayat menampilkan impor seed', /uji|statistik_instansi_2024|Sukses/.test(awalTeks));
  await da.screenshot({ path: `${OUT}/33-admin-statistik-desktop.png`, fullPage: true });

  const csv = [
    'tahun,jenis_pengadaan,cakupan,instansi,jumlah_formasi,jumlah_pelamar,jumlah_submit,jumlah_lolos_administrasi,tanggal_snapshot,status_angka,tingkat_data,keyakinan,sumber_nama,sumber_url,catatan',
    '2024,cpns,instansi,Instansi E2E Satu,10,1000,,,2024-09-17,akhir,A,turunan,Sumber E2E,https://contoh.id/e2e,',
    '2024,cpns,instansi,Instansi E2E Dua,20,50,,,2024-09-17,akhir,A,turunan,Sumber E2E,,',
    '2024,cpns,instansi,,5,10,,,2024-09-17,akhir,A,turunan,Sumber E2E,,',
    '2024,cpns,instansi,Instansi E2E Tiga,5,abc,,,2024-09-17,akhir,A,turunan,Sumber E2E,,',
  ].join('\n');
  const berkas = path.join(os.tmpdir(), 'statistik-e2e.csv');
  fs.writeFileSync(berkas, csv);

  await da.locator('#berkas').setInputFiles(berkas);
  await da.getByRole('button', { name: 'Lihat pratinjau' }).click();
  await da.waitForURL('**/admin/statistik/pratinjau/**');
  const pratinjau = (await da.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Pratinjau menghitung 4 baris, 2 valid, 2 bermasalah', /4\s*Total baris/.test(pratinjau) && /2\s*Siap diimpor/.test(pratinjau) && /2\s*Bermasalah/.test(pratinjau));
  cek('Pratinjau menjelaskan galat per baris', /Nama instansi wajib diisi/.test(pratinjau) && /bilangan bulat/.test(pratinjau));
  await da.screenshot({ path: `${OUT}/34-pratinjau-desktop.png`, fullPage: true });

  await da.getByRole('button', { name: 'Impor sekarang' }).click();
  cek('Impor meminta konfirmasi modal', /Simpan data statistik ini\?/.test(await dialogTeks(da)));
  await tutup(da, 'Ya, simpan');
  const sukses = await dialogTeks(da);
  cek('Impor selesai dengan dialog sukses berisi hitungan', /Impor selesai/.test(sukses) && /2 baris baru/.test(sukses) && /2 baris dilewati/.test(sukses), sukses.slice(0, 120));
  await tutup(da);
  const sesudah = (await da.locator('main').innerText()).replace(/\s+/g, ' ');
  cek('Total baris menjadi 97', /97\s*Baris statistik tersimpan/.test(sesudah));
  cek('Riwayat menyediakan unduhan baris gagal', (await da.getByRole('link', { name: 'Unduh baris gagal' }).count()) >= 1);

  const [unduhan] = await Promise.all([da.waitForEvent('download'), da.getByRole('link', { name: 'Unduh baris gagal' }).first().click()]);
  const isiGalat = fs.readFileSync(await unduhan.path(), 'utf8');
  cek('CSV baris gagal berisi baris dan alasan', /baris,kolom,pesan,nilai/.test(isiGalat) && /Nama instansi wajib diisi/.test(isiGalat));

  await da.goto(BASE + '/admin/statistik');
  await da.getByRole('button', { name: 'Batalkan impor' }).first().click();
  cek('Rollback meminta konfirmasi modal berisiko', /Batalkan impor ini\?/.test(await dialogTeks(da)));
  await tutup(da, 'Ya, batalkan impor');
  cek('Rollback sukses menghapus 2 baris', /Impor dibatalkan/.test(await dialogTeks(da)) && /2 baris/.test(await dialogTeks(da)));
  await tutup(da);
  cek('Total baris kembali 95 dan status Dibatalkan tampil', /95\s*Baris statistik tersimpan/.test((await da.locator('main').innerText()).replace(/\s+/g, ' ')) && /Dibatalkan/.test(await da.locator('main').innerText()));

  await da.locator('#berkas').setInputFiles({ name: 'rusak.csv', mimeType: 'text/csv', buffer: Buffer.from('tahun,instansi\n2024,A\n') });
  await da.getByRole('button', { name: 'Lihat pratinjau' }).click();
  cek('Berkas tanpa kolom wajib ditolak dengan dialog gagal', /Kolom wajib tidak ditemukan/.test(await dialogTeks(da)));
  await tutup(da);

  await da.goto(BASE + '/admin/pengaturan/persaingan');
  await da.locator('#ketat_min').fill('3');
  await da.locator('#longgar_maks').fill('8');
  await da.getByRole('button', { name: 'Simpan ambang persaingan' }).click();
  await da.waitForSelector('#ketat_min-galat');
  cek('Ambang tidak valid ditolak dengan galat inline di bawah isian', /lebih besar dari batas Longgar/.test(await da.locator('#ketat_min-galat').innerText()));
  await da.locator('#longgar_maks').fill('2');
  await da.locator('#ketat_min').fill('4');
  await da.getByRole('button', { name: 'Simpan ambang persaingan' }).click();
  cek('Ambang valid tersimpan', /Ambang persaingan tersimpan/.test(await dialogTeks(da)));
  await tutup(da);
  await da.screenshot({ path: `${OUT}/35-admin-persaingan-desktop.png`, fullPage: true });

  const { page: pemantau } = await baru();
  await masukMember(pemantau);
  cek('Member langsung melihat ambang baru', /di bawah 2, Ketat bila di atas 4/.test(await pemantau.locator('main').innerText()));
  await da.goto(BASE + '/admin/pengaturan/persaingan');
  await da.locator('#longgar_maks').fill('5');
  await da.locator('#ketat_min').fill('20');
  await da.getByRole('button', { name: 'Simpan ambang persaingan' }).click();
  await dialogTeks(da);
  await tutup(da);

  const status = await pemantau.goto(BASE + '/admin/statistik');
  cek('Member ditolak dari halaman statistik admin', status && status.status() === 403);

  const ukuran = [['hp', 390, 844], ['tablet', 820, 1180], ['desktop', 1366, 900]];

  for (const [nama, lebar, tinggi] of ukuran) {
    for (const skema of ['light', 'dark']) {
      const member = await baru(lebar, tinggi, skema);
      await masukMember(member.page);

      for (const [berkasNama, jalur] of [['peluang', '/peluang-instansi'], ['peluang-ketat', '/peluang-instansi?tingkat=ketat'], ['akun', '/akun']]) {
        await member.page.goto(BASE + jalur, { waitUntil: 'networkidle' });
        await member.page.waitForTimeout(150);
        const lebarDok = await member.page.evaluate(() => document.documentElement.scrollWidth);
        if (lebarDok > lebar) cek(`Tanpa scroll horizontal: ${berkasNama} ${nama} ${skema}`, false, `scrollWidth ${lebarDok} > ${lebar}`);
        if (berkasNama === 'peluang') await member.page.screenshot({ path: `${OUT}/peluang-${nama}-${skema}.png`, fullPage: true });
      }

      await member.ctx.close();

      const admin = await baru(lebar, tinggi, skema);
      await masukAdmin(admin.page);

      for (const [berkasNama, jalur] of [['statistik', '/admin/statistik'], ['persaingan', '/admin/pengaturan/persaingan']]) {
        await admin.page.goto(BASE + jalur, { waitUntil: 'networkidle' });
        await admin.page.waitForTimeout(150);
        const lebarDok = await admin.page.evaluate(() => document.documentElement.scrollWidth);
        if (lebarDok > lebar) cek(`Tanpa scroll horizontal: ${berkasNama} ${nama} ${skema}`, false, `scrollWidth ${lebarDok} > ${lebar}`);
        if (skema === 'light') await admin.page.screenshot({ path: `${OUT}/admin3-${berkasNama}-${nama}-${skema}.png`, fullPage: true });
      }

      await admin.ctx.close();
    }
  }

  cek('Tanpa scroll horizontal di 5 halaman pada 3 ukuran x 2 tema', !hasil.some((h) => h.nama.startsWith('Tanpa scroll horizontal:') && !h.ok));
  const penting = galatKonsol.filter((g) => !/favicon|status of (403|419)/.test(g));
  cek('Tidak ada galat JavaScript di konsol browser', penting.length === 0, penting.slice(0, 3).join(' | '));

  await browser.close();
  const gagal = hasil.filter((h) => !h.ok);
  console.log(`\nRINGKASAN: ${hasil.length - gagal.length} lulus, ${gagal.length} gagal dari ${hasil.length} pemeriksaan`);
  fs.writeFileSync(`${OUT}/hasil-fase3.json`, JSON.stringify(hasil, null, 2));
  process.exit(gagal.length ? 1 : 0);
})().catch((e) => { console.error('KESALAHAN SKRIP:', e); process.exit(2); });
