export const teksSistem = {
    galat: {
        kembali: 'Kembali ke beranda',
        kembaliAkun: 'Kembali ke halaman utama',
        muatUlang: 'Muat ulang halaman',
        halaman: {
            403: { judul: 'Halaman ini bukan untuk akun Anda', pesan: 'Anda tidak memiliki izin membuka halaman ini. Bila merasa ini keliru, hubungi admin.' },
            404: { judul: 'Halaman tidak ditemukan', pesan: 'Alamat yang Anda buka tidak ada atau sudah dipindahkan. Periksa kembali alamatnya.' },
            429: { judul: 'Terlalu banyak permintaan', pesan: 'Anda terlalu sering mengirim permintaan. Tunggu sebentar lalu coba lagi.' },
            500: { judul: 'Terjadi gangguan di server', pesan: 'Kami sedang mengalami kendala. Coba beberapa saat lagi; bila berlanjut, hubungi admin.' },
            503: { judul: 'Sedang dalam perawatan', pesan: 'Situs sedang diperbarui. Silakan kembali beberapa menit lagi.' },
        },
    },
    panduan: {
        judul: 'Panduan singkat',
        tombolBuka: 'Buka panduan singkat',
        langkah: (nomor: number, total: number) => `Langkah ${nomor} dari ${total}`,
        sebelumnya: 'Sebelumnya',
        berikutnya: 'Berikutnya',
        selesai: 'Mulai menjelajah',
        lewati: 'Lewati',
        daftar: [
            {
                judul: 'Mulai dari peluang instansi',
                isi: 'Lihat instansi mana yang pada 2024 pelamarnya relatif sedikit atau banyak. Angka selalu disertai sumber dan tanggalnya.',
            },
            {
                judul: 'Saring formasi dengan filter',
                isi: 'Pilih jenjang, jurusan, lokasi, dan instansi. Tidak perlu mengisi profil; hasil langsung menyesuaikan.',
            },
            {
                judul: 'Pantau dan bandingkan',
                isi: 'Simpan formasi incaran, tulis catatan, lalu bandingkan sampai empat formasi berdampingan.',
            },
        ],
    },
    privasi: {
        judul: 'Kebijakan privasi',
        sub: 'Ringkas dan apa adanya: apa yang kami simpan, untuk apa, dan apa yang tidak kami lakukan.',
        diperbarui: 'Terakhir diperbarui 15 November 2026',
        bagian: [
            {
                judul: 'Data yang kami simpan',
                isi: [
                    'Data pribadi Anda hanya nama, alamat email, dan nomor WhatsApp (bila admin mengisinya saat membuat akun). Kata sandi disimpan dalam bentuk hash, bukan teks asli.',
                    'Formasi yang Anda pantau beserta catatan pribadi Anda disimpan agar daftar pantauan tetap ada saat Anda kembali. Catatan itu hanya terlihat oleh Anda.',
                ],
            },
            {
                judul: 'Data yang tidak kami simpan',
                isi: [
                    'SICASN tidak meminta atau menyimpan data pribadi pelamar CASN seperti NIK, nama peserta seleksi, atau berkas pendaftaran. Angka persaingan yang tampil hanya berupa agregat jumlah.',
                    'Tidak ada pelacak iklan dan tidak ada pembagian data ke pihak ketiga.',
                ],
            },
            {
                judul: 'Untuk apa data dipakai',
                isi: [
                    'Email dipakai untuk mengirim kredensial login dan pemberitahuan akun. Nomor WhatsApp dipakai admin untuk menghubungi Anda terkait akun dan pembayaran.',
                ],
            },
            {
                judul: 'Cookie',
                isi: ['Kami memakai cookie sesi agar Anda tetap masuk dan untuk perlindungan keamanan formulir. Preferensi tema terang atau gelap disimpan di peramban Anda.'],
            },
            {
                judul: 'Hak Anda',
                isi: ['Anda dapat meminta admin memperbaiki data atau menonaktifkan akun Anda kapan saja lewat WhatsApp admin.'],
            },
        ],
        kembali: 'Kembali ke beranda',
    },
    footer: {
        privasi: 'Kebijakan privasi',
    },
} as const;
