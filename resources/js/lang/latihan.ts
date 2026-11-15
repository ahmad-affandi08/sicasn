export const teksLatihan = {
    nav: 'Latihan',
    judul: 'Latihan SKD',
    sub: 'Latihan Tes Kompetensi Dasar (TWK, TIU, TKP) dengan soal buatan SICASN. Setiap soal mencantumkan sumber, pembahasan, dan lisensinya.',
    kategori: {
        twk: 'TWK',
        tiu: 'TIU',
        tkp: 'TKP',
    },
    kategoriLengkap: {
        twk: 'Tes Wawasan Kebangsaan',
        tiu: 'Tes Intelegensia Umum',
        tkp: 'Tes Karakteristik Pribadi',
    },
    mode: {
        simulasi: { judul: 'Simulasi SKD lengkap', deskripsi: (jumlah: string, menit: number) => `${jumlah} · ${menit} menit, urutan seperti CAT` },
        twk: { judul: 'Latihan TWK', deskripsi: (jumlah: number, menit: number) => `${jumlah} soal wawasan kebangsaan · ${menit} menit` },
        tiu: { judul: 'Latihan TIU', deskripsi: (jumlah: number, menit: number) => `${jumlah} soal intelegensia umum · ${menit} menit` },
        tkp: { judul: 'Latihan TKP', deskripsi: (jumlah: number, menit: number) => `${jumlah} soal karakteristik pribadi · ${menit} menit` },
    },
    mulai: 'Mulai',
    tidakTersedia: 'Soal belum tersedia',
    tersedia: (jumlah: number) => `${jumlah.toLocaleString('id-ID')} soal`,
    lanjutkan: {
        judul: 'Anda masih punya latihan yang berjalan',
        pesan: 'Selesaikan dulu latihan itu sebelum memulai yang baru. Waktunya tetap berjalan.',
        tombol: 'Lanjutkan latihan',
    },
    bank: 'Bank soal',
    ambang: {
        judul: 'Ambang lolos SKD CPNS 2024',
        sub: 'Skor minimum per tes menurut pemberitaan tentang Kepmenpan RB 321/2024. Periksa pengumuman resmi instansi Anda untuk tahun seleksi yang Anda ikuti.',
        tes: 'Tes',
        minimum: 'Minimum',
        maksimum: 'Maksimum',
        jumlah: 'Jumlah soal',
    },
    catatan: 'Soal latihan disusun SICASN, bukan soal ujian asli, dan skornya bukan prediksi hasil ujian sebenarnya.',
    riwayat: {
        judul: 'Riwayat latihan',
        kosong: 'Belum ada latihan yang selesai. Mulai satu latihan di atas, hasilnya tersimpan di sini.',
        lihat: 'Lihat hasil',
        lulus: 'Memenuhi ambang',
        belumLulus: 'Belum memenuhi ambang',
        skor: (kategori: string, skor: number) => `${kategori} ${skor}`,
    },
    kerjakan: {
        soalKe: (nomor: number, total: number) => `Soal ${nomor} dari ${total}`,
        opsi: 'Pilihan jawaban',
        ragu: 'Ragu-ragu',
        kosongkan: 'Kosongkan jawaban',
        sebelumnya: 'Sebelumnya',
        berikutnya: 'Berikutnya',
        selesai: 'Selesaikan latihan',
        waktu: 'Sisa waktu',
        waktuHampirHabis: (menit: number) => `Sisa waktu ${menit} menit.`,
        peta: 'Daftar soal',
        petaKeterangan: { dijawab: 'Dijawab', ragu: 'Ragu-ragu', kosong: 'Belum dijawab', sekarang: 'Sedang dibuka' },
        petaAria: (nomor: number, status: string) => `Soal ${nomor}, ${status}`,
        status: { dijawab: 'dijawab', ragu: 'dijawab dan ragu-ragu', kosong: 'belum dijawab' },
        ringkasan: (dijawab: number, total: number) => `${dijawab} dari ${total} soal dijawab`,
        konfirmasi: {
            judul: 'Selesaikan latihan?',
            pesan: (kosong: number, ragu: number) => {
                const bagian: string[] = [];

                if (kosong > 0) {
                    bagian.push(`${kosong} soal belum dijawab`);
                }

                if (ragu > 0) {
                    bagian.push(`${ragu} soal ditandai ragu-ragu`);
                }

                const catatan = bagian.length > 0 ? `${bagian.join(' dan ')}. ` : '';

                return `${catatan}Setelah diselesaikan, jawaban tidak bisa diubah lagi.`;
            },
            tombol: 'Ya, selesaikan',
            batal: 'Lanjut mengerjakan',
        },
        gagalSimpan: {
            judul: 'Jawaban belum tersimpan',
            pesan: 'Koneksi Anda bermasalah sehingga jawaban terakhir belum sampai ke server. Periksa koneksi, lalu pilih jawabannya lagi.',
            tombol: 'Mengerti',
        },
        waktuHabis: {
            judul: 'Waktu habis',
            pesan: 'Latihan diselesaikan otomatis. Jawaban yang sudah tersimpan dinilai.',
            tombol: 'Lihat hasil',
        },
        menyimpan: 'Menyimpan jawaban…',
        tersimpan: 'Jawaban tersimpan',
    },
    hasil: {
        judul: 'Hasil latihan',
        total: 'Skor total',
        dari: (maks: number) => `dari ${maks.toLocaleString('id-ID')}`,
        lulus: 'Memenuhi ambang di ketiga tes',
        belumLulus: (daftar: string) => `Belum memenuhi ambang pada ${daftar}`,
        setara: 'Ambang setara dihitung proporsional dari jumlah soal pada latihan ini.',
        tes: 'Tes',
        skor: 'Skor',
        ambang: 'Ambang',
        benar: 'Benar',
        terbaik: 'Jawaban terbaik',
        salah: 'Salah',
        kurangTepat: 'Kurang tepat',
        kosong: 'Kosong',
        tercapai: 'Tercapai',
        belumTercapai: 'Belum tercapai',
        pembahasan: 'Pembahasan',
        filter: { semua: 'Semua soal', salah: 'Salah', kosong: 'Kosong', benar: 'Benar' },
        filterAria: 'Tampilkan soal',
        tidakAda: 'Tidak ada soal pada pilihan ini.',
        kunci: 'Kunci jawaban',
        pilihanAnda: 'Pilihan Anda',
        skorOpsi: (skor: number) => `skor ${skor}`,
        status: { benar: 'Benar', salah: 'Salah', kosong: 'Tidak dijawab' },
        statusTkp: { benar: 'Jawaban terbaik', salah: 'Kurang tepat', kosong: 'Tidak dijawab' },
        sumber: 'Sumber',
        lisensi: 'Lisensi',
        ragu: 'Ditandai ragu-ragu',
        latihanLagi: 'Latihan lagi',
        riwayat: 'Riwayat latihan',
        waktuSelesai: 'Selesai',
        disclaimer: 'Skor ini hasil latihan dengan soal buatan SICASN, bukan prediksi hasil ujian sebenarnya.',
    },
} as const;
