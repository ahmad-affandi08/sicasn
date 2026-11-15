export const teks = {
    aplikasi: 'SICASN',
    tagline: 'Peluang formasi CPNS',
    nav: {
        manfaat: 'Manfaat',
        caraAkses: 'Cara Akses',
        menu: 'Menu',
        tutupMenu: 'Tutup menu',
        bukaMenu: 'Buka menu',
        modeGelap: 'Aktifkan mode gelap',
        modeTerang: 'Aktifkan mode terang',
    },
    hero: {
        lencana: 'Untuk calon CPNS',
        judul: 'Pilih formasi CPNS yang peluangnya paling masuk akal',
        sub: 'Cari formasi lewat filter pendidikan, jurusan, dan lokasi. Lihat tingkat persaingannya berdasarkan jumlah pelamar CPNS 2024, tanpa mengisi data apa pun.',
        ctaWhatsapp: 'Chat Admin via WhatsApp',
        ctaCaraAkses: 'Lihat cara akses',
        catatan: 'Akun dibuat admin setelah pembayaran dikonfirmasi.',
        ilustrasi: 'Contoh tampilan daftar instansi dengan skala persaingan, angka disamarkan',
    },
    manfaat: {
        judul: 'Cara kerja',
        sub: 'Satu tempat untuk membandingkan peluang, bukan menebak-nebak.',
        daftar: [
            {
                judul: 'Cari lewat filter',
                isi: 'Saring formasi berdasarkan jenjang pendidikan, jurusan, provinsi, dan instansi. Tidak ada formulir panjang.',
            },
            {
                judul: 'Lihat persaingan 2024',
                isi: 'Setiap data pelamar mencantumkan sumber, tanggal, dan tingkat kerincian, jadi Anda tahu seberapa jauh angkanya bisa dipercaya.',
            },
            {
                judul: 'Pantau dan bandingkan',
                isi: 'Simpan formasi incaran dan bandingkan beberapa formasi berdampingan sebelum Anda mendaftar.',
            },
        ],
    },
    data: {
        judul: 'Angka yang bisa ditelusuri',
        sub: 'Setiap angka pelamar tampil bersama sumber, tanggal, dan tingkat datanya. Tidak ada angka tanpa asal-usul.',
        poin: [
            { judul: 'Ada sumber dan tanggal', isi: 'Rilis BKN dan pemberitaan yang mengutipnya dicatat lengkap dengan tautan, supaya Anda bisa memeriksanya sendiri.' },
            { judul: 'Angka sementara ditandai', isi: 'Data yang masih berjalan diberi tanda ≥ dan tidak pernah disebut longgar, karena jumlah pelamar akhirnya bisa lebih besar.' },
            { judul: 'Bukan prediksi', isi: 'Yang ditampilkan adalah riwayat persaingan 2024. Itu gambaran, bukan jaminan untuk tahun ini.' },
        ],
        skala: {
            judul: 'Cara membaca skala',
            isi: 'Rasio adalah jumlah pelamar dibagi jumlah formasi. Penanda bergeser ke kanan seiring ketatnya persaingan.',
            contoh: 'Contoh penanda',
            catatan: 'Ambang awal, dapat disesuaikan admin dan bukan klaim resmi.',
            tingkat: ['Longgar: rasio di bawah 5', 'Sedang: rasio 5 sampai 20', 'Ketat: rasio di atas 20'],
        },
    },
    caraAkses: {
        judul: 'Cara mendapatkan akses',
        sub: 'Empat langkah singkat. Tidak ada pendaftaran otomatis, semuanya lewat admin.',
        langkah: [
            { judul: 'Chat admin', isi: 'Hubungi admin lewat WhatsApp untuk menanyakan paket dan nomor rekening.' },
            { judul: 'Transfer', isi: 'Lakukan transfer sesuai nominal, lalu kirim bukti transfer ke admin di WhatsApp.' },
            { judul: 'Terima akun', isi: 'Admin membuat akun Anda. Email login dan kata sandi sementara dikirim otomatis ke email Anda.' },
            { judul: 'Masuk', isi: 'Masuk dengan kata sandi sementara, lalu ganti dengan kata sandi baru milik Anda.' },
        ],
        ilustrasiLangkah: 'Ilustrasi langkah',
    },
    pembayaran: {
        judul: 'Info paket',
        atasNama: (nama: string) => `a.n. ${nama}`,
        catatanKonfirmasi: 'Kirim bukti transfer ke admin lewat WhatsApp agar akun Anda segera dibuat.',
    },
    penutup: {
        judul: 'Siap melihat peluang Anda?',
        sub: 'Hubungi admin sekarang. Biasanya akun dibuat segera setelah bukti transfer diterima.',
    },
    footer: {
        disclaimer: 'SICASN bukan situs resmi pemerintah dan tidak berafiliasi dengan BKN. Data bersumber dari rilis resmi dan sumber publik, selalu dicantumkan sumber dan tanggalnya. Untuk pendaftaran, gunakan portal resmi.',
        portalResmi: 'Portal resmi SSCASN',
        hakCipta: (tahun: number) => `© ${tahun} SICASN`,
    },
    dialog: {
        mengerti: 'Mengerti',
        lanjutkan: 'Ya, lanjutkan',
        batal: 'Batal',
        ketikUntukLanjut: (kataKunci: string) => `Ketik "${kataKunci}" untuk melanjutkan`,
        kontakBelumAdaJudul: 'Kontak admin belum diatur',
        kontakBelumAdaPesan: 'Nomor WhatsApp admin belum diisi. Silakan coba lagi nanti.',
    },
} as const;
