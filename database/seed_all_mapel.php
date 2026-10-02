<?php
// database/seed_all_mapel.php - Bank Soal Lengkap Semua Mapel SMA & SMK Standar Pusmendik TKA

function seedAllMapel(PDO $pdo) {
    // Array Master 18 Paket Ujian Pusmendik TKA (SMA & SMK Semua Rumpun)
    $paketData = [
        // -------------------------------------------------------------
        // SMA - SAINTEK (IPA)
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-SMA-MAT',
            'judul' => 'TKA Matematika Tingkat Lanjut (Kalkulus & Matriks)',
            'kategori' => 'SMA Saintek',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-MAT-01',
                'judul' => 'Model Laju Pertumbuhan Populasi dan Diferensial',
                'konten' => '<div class="space-y-3"><p class="font-bold">Model Pertumbuhan Bakteri dan Laju Perubahan Eksponensial</p><p>Jumlah koloni bakteri $N(t)$ dalam suatu cawan petri dinyatakan oleh fungsi $N(t) = 500 \cdot e^{0,4t}$, di mana $t$ adalah waktu dalam satuan jam. Laju pertambahan koloni bakteri pada saat $t$ dihitung menggunakan turunan pertama $N\'(t)$.</p><div class="p-3 bg-slate-100 border border-slate-300 rounded font-mono text-xs">Fungsi Laju: N\'(t) = dN/dt = 200 &times; e^(0,4t)</div></div>',
                'sumber' => 'Pusmendik - Penalaran Matematika Murni & Terapan'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Kalkulus Diferensial', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Berapakah laju pertambahan koloni bakteri tepat pada saat t = 0 jam (awal pengamatan)?',
                    'opsi' => [['key'=>'A','text'=>'100 bakteri per jam'],['key'=>'B','text'=>'200 bakteri per jam'],['key'=>'C','text'=>'500 bakteri per jam'],['key'=>'D','text'=>'700 bakteri per jam'],['key'=>'E','text'=>'1.000 bakteri per jam']],
                    'kunci' => 'B',
                    'pembahasan' => 'Substitusi t = 0 ke rumus laju N\'(0) = 200 x e^0 = 200 x 1 = 200 bakteri per jam.',
                    'bobot' => 1.0
                ],
                [
                    'nomor' => 2, 'subtes' => 'Analisis Pertumbuhan', 'tipe' => 'pg_kompleks',
                    'tanya' => 'Pernyataan matematis manakah yang BENAR terkait grafik fungsi N(t) di atas? (Pilih semua yang benar)',
                    'opsi' => [['key'=>'A','text'=>'Grafik N(t) selalu cekung ke atas (konkaf ke atas) untuk t >= 0.'],['key'=>'B','text'=>'Nilai limit N(t) mendekati tak hingga ketika t mendekati tak hingga.'],['key'=>'C','text'=>'Pada t = 2, nilai laju pertumbuhan lebih kecil daripada laju pada t = 1.'],['key'=>'D','text'=>'Jumlah populasi awal pada t = 0 adalah 500 individu.']],
                    'kunci' => ['A', 'B', 'D'],
                    'pembahasan' => 'Pilihan A benar (turunan kedua positif). Pilihan B benar (fungsi eksponensial tumbuh tak hingga). Pilihan D benar (N(0) = 500). Pilihan C salah (laju justru semakin membesar).',
                    'bobot' => 1.0
                ],
                [
                    'nomor' => 3, 'subtes' => 'Sifat Turunan', 'tipe' => 'matriks_bs',
                    'tanya' => 'Tentukan kebenaran sifat turunan kalkulus berikut!',
                    'opsi' => ['kolom'=>['Benar','Salah'],'pernyataan'=>[['id'=>'p1','teks'=>'Turunan dari fungsi f(x) = sin(x) adalah f\'(x) = cos(x).'],['id'=>'p2','teks'=>'Jika f\'(x) = 0 pada suatu titik dan f\'\'(x) < 0, maka titik tersebut adalah titik minimum lokal.'],['id'=>'p3','teks'=>'Integral tentu dari suatu fungsi menyatakan luas bidang di bawah kurva fungsi tersebut terhadap sumbu-x.']]],
                    'kunci' => ['p1'=>'Benar','p2'=>'Salah','p3'=>'Benar'],
                    'pembahasan' => 'p1 Benar. p2 Salah (f\'\'(x) < 0 menandakan titik maksimum lokal, bukan minimum). p3 Benar.',
                    'bobot' => 1.0
                ]
            ]
        ],

        [
            'kode' => 'TKA-SMA-FIS',
            'judul' => 'TKA Fisika (Mekanika, Gelombang & Elektromagnetisme)',
            'kategori' => 'SMA Saintek',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-FIS-01',
                'judul' => 'Prinsip Kekekalan Energi & Dinamika Gerak Harmonik Sederhana',
                'konten' => '<div class="space-y-3"><p class="font-bold">Osilasi Pegas dan Transformasi Energi Kinetik - Potensial</p><p>Sebuah balok bermassa $m = 0,5\text{ kg}$ dihubungkan ke pegas horizontal dengan konstanta pegas $k = 200\text{ N/m}$. Balok ditarik sejauh simpangan maksimum $A = 0,1\text{ m}$ dari posisi setimbang pada lantai licin tanpa gesekan ($g = 10\text{ m/s}^2$).</p><div class="p-3 bg-slate-100 border border-slate-300 rounded font-mono text-xs">Energi Mekanik Total: E_total = 1/2 &times; k &times; A&sup2; = 1/2 &times; 200 &times; (0,1)&sup2; = 1,0 Joule</div></div>',
                'sumber' => 'Pusmendik - Fisika Terapan & Asesmen Kompetensi'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Energi Mekanik Osilasi', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Berapakah kecepatan balok saat melintasi posisi setimbang (titik x = 0)?',
                    'opsi' => [['key'=>'A','text'=>'1 m/s'],['key'=>'B','text'=>'2 m/s'],['key'=>'C','text'=>'4 m/s'],['key'=>'D','text'=>'10 m/s'],['key'=>'E','text'=>'20 m/s']],
                    'kunci' => 'B',
                    'pembahasan' => 'Pada posisi setimbang (x = 0), seluruh energi mekanik berubah menjadi energi kinetik maksimum: 1/2 m v^2 = E_total -> 1/2 x 0,5 x v^2 = 1,0 -> 0,25 v^2 = 1,0 -> v^2 = 4 -> v = 2 m/s.',
                    'bobot' => 1.0
                ],
                [
                    'nomor' => 2, 'subtes' => 'Dinamika Getaran', 'tipe' => 'isian_singkat',
                    'tanya' => 'Berapa Joule energi potensial pegas saat simpangannya mencapai nilai maksimum A = 0,1 m? (Tuliskan angkanya saja)',
                    'opsi' => null,
                    'kunci' => ['1', '1.0', '1,0'],
                    'pembahasan' => 'Saat simpangan maksimum x = A, kecepatan v = 0 sehingga seluruh energi mekanik berupa energi potensial pegas yaitu Ep = 1/2 k A^2 = 1 Joule.',
                    'bobot' => 1.0
                ]
            ]
        ],

        [
            'kode' => 'TKA-SMA-KIM',
            'judul' => 'TKA Kimia (Stokiometri, Termokimia & Larutan Asam Basa)',
            'kategori' => 'SMA Saintek',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-KIM-01',
                'judul' => 'Titrasi Asam Basa dan Sistem Penyangga (Buffer) Tubuh',
                'konten' => '<div class="space-y-3"><p class="font-bold">Mekanisme Keseimbangan pH Darah oleh Buffer Karbonat</p><p>Darah manusia memiliki kisaran pH normal 7,35 - 7,45 yang dipertahankan oleh sistem larutan penyangga utama asam karbonat ($H_2CO_3$) dan ion bikarbonat ($HCO_3^-$). Reaksi kesetimbangan berlangsung sebagai berikut: $H_2CO_3 \rightleftharpoons H^+ + HCO_3^-$.</p></div>',
                'sumber' => 'Pusmendik - Kimia Analisis & Biokimia'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Larutan Penyangga', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Apa yang terjadi jika ke dalam darah masuk senyawa yang bersifat asam (ion H+ berlebih)?',
                    'opsi' => [['key'=>'A','text'=>'Ion H+ akan bereaksi dengan HCO3- membentuk H2CO3 sehingga pH tetap relatif konstan.'],['key'=>'B','text'=>'Ion H+ akan bereaksi dengan H2CO3 menghasilkan air.'],['key'=>'C','text'=>'pH darah akan turun drastis hingga di bawah 2.'],['key'=>'D','text'=>'Ion bikarbonat akan mengendap sebagai garam kalsium karbonat.'],['key'=>'E','text'=>'Reaksi kesetimbangan bergeser ke arah kanan secara permanen.']],
                    'kunci' => 'A',
                    'pembahasan' => 'Jawaban A benar. Basa konjugasi HCO3- mengikat ion H+ berlebih membentuk H2CO3, menjaga pH darah tetap stabil.',
                    'bobot' => 1.0
                ]
            ]
        ],

        [
            'kode' => 'TKA-SMA-BIO',
            'judul' => 'TKA Biologi (Genetika, Metabolisme Sel & Ekologi)',
            'kategori' => 'SMA Saintek',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-BIO-01',
                'judul' => 'Sintesis Protein & Regulasi Ekspresi Genetik',
                'konten' => '<div class="space-y-3"><p class="font-bold">Transkripsi dan Translasi Kode Genetik</p><p>Proses sintesis protein terdiri dari transkripsi pembentukan mRNA dari untai DNA cetakan (antisense) di dalam inti sel, dilanjutkan dengan translasi kodon triplet mRNA oleh tRNA pada ribosom untuk merangkai asam amino.</p></div>',
                'sumber' => 'Pusmendik - Biologi Sel & Genetika Molekuler'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Sintesis Protein', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Jika urutan basa nitrogen rantai antisense DNA adalah 5\'-TAC-GGC-TTA-3\', maka urutan kodon pada mRNA hasil transkripsi adalah ...',
                    'opsi' => [['key'=>'A','text'=>'5\'-AUG-CCG-AAU-3\''],['key'=>'B','text'=>'5\'-UAC-GGC-UUA-3\''],['key'=>'C','text'=>'5\'-AUG-GGC-UUA-3\''],['key'=>'D','text'=>'5\'-ATC-CCG-AAT-3\''],['key'=>'E','text'=>'5\'-CAU-GGC-AAU-3\'']],
                    'kunci' => 'A',
                    'pembahasan' => 'Kodon mRNA merupakan komplementer dari DNA antisense: T berpasangan dengan A, A dengan U, C dengan G, G dengan C. Jadi TAC -> AUG, GGC -> CCG, TTA -> AAU.',
                    'bobot' => 1.0
                ]
            ]
        ],

        // -------------------------------------------------------------
        // SMA - SOSHUM (IPS)
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-SMA-EKO',
            'judul' => 'TKA Ekonomi (Kebijakan Fiskal, Moneter & Perdagangan)',
            'kategori' => 'SMA Soshum',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-EKO-01',
                'judul' => 'Dilema Pengendalian Inflasi dan Suku Bunga Acuan Bank Sentral',
                'konten' => '<div class="space-y-3"><p class="font-bold">Kebijakan Moneter Kontraksioner Bank Indonesia</p><p>Ketika tingkat inflasi tahunan melampaui target sasaran 3% ± 1%, Bank Indonesia mengambil langkah pengetatan moneter dengan menaikkan suku bunga acuan (BI-Rate) dan menaikkan Giro Wajib Minimum (GWM) perbankan untuk menyerap kelebihan likuiditas.</p></div>',
                'sumber' => 'Pusmendik - Makroekonomi & Kebijakan Moneter'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Kebijakan Moneter', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Dampak langsung kenaikan suku bunga acuan (BI-Rate) terhadap perekonomian riil adalah ...',
                    'opsi' => [['key'=>'A','text'=>'Mendorong masyarakat untuk menambah pinjaman kredit konsumsi.'],['key'=>'B','text'=>'Menaikkan bunga simpanan dan pinjaman sehingga minat menabung meningkat dan laju inflasi dapat ditekan.'],['key'=>'C','text'=>'Menurunkan nilai tukar mata uang rupiah terhadap dolar AS.'],['key'=>'D','text'=>'Meningkatkan defisit neraca pembayaran secara drastis.'],['key'=>'E','text'=>'Menghapus kewajiban pembayaran pajak perusahaan dagang.']],
                    'kunci' => 'B',
                    'pembahasan' => 'Kenaikan suku bunga acuan menyebabkan suku bunga kredit dan deposito naik. Masyarakat terdorong menabung dan menunda konsumsi, peredaran uang berkurang, sehingga laju inflasi tertekan turun.',
                    'bobot' => 1.0
                ]
            ]
        ],

        [
            'kode' => 'TKA-SMA-SOS',
            'judul' => 'TKA Sosiologi (Struktur Sosial, Konflik & Perubahan Sosial)',
            'kategori' => 'SMA Soshum',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-SOS-01',
                'judul' => 'Transformasi Digital dan Perubahan Pola Hubungan Sosial Masyarakat',
                'konten' => '<div class="space-y-3"><p class="font-bold">Dampak Disrupsi Digital Terhadap Interaksi Sosial</p><p>Pergeseran aktivitas ekonomi menuju platform daring mengubah pola relasi sosial tradisional (Gemeinschaft/paguyuban) menjadi hubungan yang berorientasi kontraktual dan rasional (Gesellschaft/patembayan).</p></div>',
                'sumber' => 'Pusmendik - Dinamika Sosiologis Masyarakat Indonesia'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Perubahan Sosial', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Berdasarkan teori Ferdinand Tonnies, ciri utama dari kelompok patembayan (Gesellschaft) pada era digital adalah ...',
                    'opsi' => [['key'=>'A','text'=>'Hubungan bersifat batiniah, intim, dan eksklusif berdasarkan ikatan darah.'],['key'=>'B','text'=>'Ikatan bersifat sementara, didasarkan pada kepentingan bersama dan perjanjian formal/kontraktual.'],['key'=>'C','text'=>'Tradisi adat leluhur dijunjung tinggi tanpa adanya perubahan.'],['key'=>'D','text'=>'Mata pencaharian bersifat homogen di bidang agraris.'],['key'=>'E','text'=>'Kepemimpinan bersifat turun-temurun berdasarkan kasta.']],
                    'kunci' => 'B',
                    'pembahasan' => 'Masyarakat patembayan (Gesellschaft) dicirikan oleh relasi rasional, formal, berorientasi kepentingan dan tujuan jangka pendek yang diatur oleh kontrak hukum.',
                    'bobot' => 1.0
                ]
            ]
        ],

        [
            'kode' => 'TKA-SMA-GEO',
            'judul' => 'TKA Geografi (SIG, Penginderaan Jauh & Mitigasi Bencana)',
            'kategori' => 'SMA Soshum',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-GEO-01',
                'judul' => 'Pemanfaatan SIG untuk Pemetaan Zona Kerentanan Bencana Likuifaksi',
                'konten' => '<div class="space-y-3"><p class="font-bold">Analisis Tumpang Susun (Overlay) Peta Spasial</p><p>Sistem Informasi Geografis (SIG) menggabungkan peta permeabilitas tanah, kedalaman muka air tanah dangkal, dan percepatan gempa puncak (PGA) untuk menghasilkan peta zonasi rawan likuifaksi.</p></div>',
                'sumber' => 'Pusmendik - Geografi Kebencanaan & Penginderaan Jauh'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Sistem Informasi Geografis', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Metode analisis spasial pada SIG yang menumpuk beberapa lapisan data tematik untuk menghasilkan satuan pemetaan baru disebut ...',
                    'opsi' => [['key'=>'A','text'=>'Buffering'],['key'=>'B','text'=>'Overlay'],['key'=>'C','text'=>'Network Analysis'],['key'=>'D','text'=>'Geocoding'],['key'=>'E','text'=>'Interpolasi Spasial']],
                    'kunci' => 'B',
                    'pembahasan' => 'Metode Overlay (tumpang susun) mengintegrasikan beberapa peta tematik untuk menganalisis keterkaitan antar variabel spasial.',
                    'bobot' => 1.0
                ]
            ]
        ],

        [
            'kode' => 'TKA-SMA-SEJ',
            'judul' => 'TKA Sejarah (Pergerakan Nasional & Diplomasi Kemerdekaan)',
            'kategori' => 'SMA Soshum',
            'jenjang' => 'SMA / MA',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-SEJ-01',
                'judul' => 'Jalur Militer dan Diplomasi Mempertahankan Kedaulatan RI',
                'konten' => '<div class="space-y-3"><p class="font-bold">Kombinasi Strategi Perang Gerilya dan Perundingan Internasional (1945-1949)</p><p>Indonesia mempertahankan proklamasi kemerdekaan melalui strategi dwitunggal: perlawanan senjata fisik dan diplomasi internasional (Linggarjati, Renville, Roem-Royen, hingga Konferensi Meja Bundar).</p></div>',
                'sumber' => 'Pusmendik - Sejarah Nasional Indonesia'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Diplomasi Kemerdekaan', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Hasil utama Perundingan Roem-Royen (1949) yang membuka jalan menuju Konferensi Meja Bundar (KMB) adalah ...',
                    'opsi' => [['key'=>'A','text'=>'Penghentian permusuhan, kembalinya pemerintah RI ke Yogyakarta, dan persetujuan penyelenggaraan KMB di Den Haag.'],['key'=>'B','text'=>'Pengakuan kedaulatan de facto Belanda atas wilayah Jawa, Sumatra, dan Madura.'],['key'=>'C','text'=>'Pembentukan Garis Van Mook sebagai batas demarkasi wilayah kekuasaan militer.'],['key'=>'D','text'=>'Penyerahan Irian Barat seketika kepada pihak Indonesia.'],['key'=>'E','text'=>'Pembubaran Tentara Nasional Indonesia (TNI).']],
                    'kunci' => 'A',
                    'pembahasan' => 'Kesepakatan Roem-Royen memulihkan posisi pemerintah RI di Yogyakarta dan menyepakati penghentian tembak-menembak serta jadwal KMB.',
                    'bobot' => 1.0
                ]
            ]
        ],

        // -------------------------------------------------------------
        // SMA & SMK - MATA UJI UMUM (LITERASI & BAHASA)
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-UMUM-BINDO',
            'judul' => 'TKA Bahasa Indonesia (Literasi Membaca & Teks Kritis)',
            'kategori' => 'Umum (Wajib)',
            'jenjang' => 'SMA / SMK',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-BIN-01',
                'judul' => 'Teks Editorial: Tantangan Kemandirian Pangan dan Kebijakan Impor',
                'konten' => '<div class="space-y-3"><p class="font-bold">Paradoks Swasembada Pangan Nasional</p><p>Ketergantungan terhadap komoditas impor di tengah luasnya lahan pertanian menunjukkan adanya masalah hulu-ke-hilir: rantai pasok pupuk tersendat, konversi lahan pertanian, serta lemahnya regenerasi petani muda.</p></div>',
                'sumber' => 'Pusmendik - Asesmen Literasi Bahasa Indonesia'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Teks Editorial', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Gagasan pokok paragraf wacana editorial di atas adalah ...',
                    'opsi' => [['key'=>'A','text'=>'Kebutuhan impor pangan Indonesia mengalami penurunan drastis tahun ini.'],['key'=>'B','text'=>'Faktor-faktor sistemik dari hulu ke hilir yang menghambat pencapaian kemandirian pangan nasional.'],['key'=>'C','text'=>'Kualitas pupuk lokal lebih unggul dibanding pupuk luar negeri.'],['key'=>'D','text'=>'Tingginya minat generasi muda untuk berprofesi sebagai petani konvensional.'],['key'=>'E','text'=>'Pemerintah menghentikan sepenuhnya kerja sama perdagangan bahan pokok.']],
                    'kunci' => 'B',
                    'pembahasan' => 'Paragraf memaparkan berbagai masalah di hulu dan hilir (rantai pupuk, alih fungsi lahan, regenerasi) yang menjadi kendala swasembada.',
                    'bobot' => 1.0
                ]
            ]
        ],

        [
            'kode' => 'TKA-UMUM-BING',
            'judul' => 'TKA Bahasa Inggris (Reading Comprehension & Analytical Text)',
            'kategori' => 'Umum (Wajib)',
            'jenjang' => 'SMA / SMK',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-ENG-01',
                'judul' => 'Analytical Exposition: The Economic Shift Toward Green Jobs',
                'konten' => '<div class="space-y-3"><p class="font-bold">The Rise of Sustainable Career Pathways</p><p>The global transition to renewable energy sources has catalyzed an unprecedented surge in green occupations. Industries are rapidly pivoting toward decarbonization, demanding new skill sets ranging from smart-grid installation to environmental auditing.</p></div>',
                'sumber' => 'Pusmendik - English Literacy Assessment'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Reading Comprehension', 'tipe' => 'pg_tunggal',
                    'tanya' => 'According to the passage, what is the primary driver behind the surge in green jobs?',
                    'opsi' => [['key'=>'A','text'=>'A sudden decline in consumer demand for renewable energy technologies.'],['key'=>'B','text'=>'The worldwide economic transition toward renewable energy and decarbonization.'],['key'=>'C','text'=>'The total automation of manual labor in heavy manufacturing.'],['key'=>'D','text'=>'Governmental bans on corporate environmental auditing programs.'],['key'=>'E','text'=>'A severe decrease in vocational training programs worldwide.']],
                    'kunci' => 'B',
                    'pembahasan' => 'The passage states: "The global transition to renewable energy sources has catalyzed an unprecedented surge in green occupations." Therefore, option B is correct.',
                    'bobot' => 1.0
                ]
            ]
        ],

        // -------------------------------------------------------------
        // SMK - REKAYASA PERANGKAT LUNAK (RPL)
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-SMK-RPL',
            'judul' => 'TKA Kejuruan SMK: Rekayasa Perangkat Lunak (Database & Web API)',
            'kategori' => 'SMK TIK',
            'jenjang' => 'SMK / MAK',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-RPL-01',
                'judul' => 'Arsitektur RESTful API & Normalisasi Database Relasional',
                'konten' => '<div class="space-y-3"><p class="font-bold">Perancangan Backend E-Commerce Terdistribusi</p><p>Sistem backend menggunakan arsitektur REST API dengan autentikasi JSON Web Token (JWT). Database relasional dirancang memenuhi standar bentuk normal ketiga (3NF) guna mencegah anomali insersi, pembaruan, dan penghapusan data transaksi.</p></div>',
                'sumber' => 'Pusmendik - Standar Kompetensi Keahlian Rekayasa Perangkat Lunak'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Database Relasional (3NF)', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Sebuah tabel database dikatakan memenuhi Bentuk Normal Ketiga (3NF) apabila ...',
                    'opsi' => [['key'=>'A','text'=>'Telah memenuhi 2NF dan tidak memiliki ketergantungan transitif (transitive dependency) antaratribut non-kunci.'],['key'=>'B','text'=>'Setiap kolom hanya berisi nilai atomic tanpa pengulangan kelompok.'],['key'=>'C','text'=>'Semua kolom non-kunci bergantung sebagian pada primary key komposit.'],['key'=>'D','text'=>'Tabel tidak memiliki primary key sama sekali.'],['key'=>'E','text'=>'Seluruh tabel digabungkan menjadi satu tabel tunggal berukuran besar.']],
                    'kunci' => 'A',
                    'pembahasan' => 'Definisi 3NF: Tabel telah berada dalam 2NF dan tidak ada ketergantungan fungsional transitif (atribut non-primer tidak bergantung pada atribut non-primer lainnya).',
                    'bobot' => 1.0
                ]
            ]
        ],

        // -------------------------------------------------------------
        // SMK - TEKNIK KENDARAAN RINGAN OTOMOTIF (TKRO)
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-SMK-TKRO',
            'judul' => 'TKA Kejuruan SMK: Teknik Kendaraan Ringan Otomotif (EFI & Rem ABS)',
            'kategori' => 'SMK Rekayasa',
            'jenjang' => 'SMK / MAK',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-TKR-01',
                'judul' => 'Diagnostik Sistem Electronic Fuel Injection (EFI) via OBD-II Scanner',
                'konten' => '<div class="space-y-3"><p class="font-bold">Troubleshooting Mesin Bensin Menggunakan Diagnostic Trouble Code (DTC)</p><p>Saat lampu Check Engine menyala pada kendaraan EFI, scanner OBD-II mendeteksi kode DTC <strong>P0171 (System Too Lean - Bank 1)</strong>, menandakan campuran udara dan bahan bakar terlalu miskin (terlalu banyak udara atau terlalu sedikit bahan bakar disemprotkan injektor).</p></div>',
                'sumber' => 'Pusmendik - Standar Kompetensi Otomotif Kendaraan Ringan'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Sistem Injeksi Elektronik', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Penyebab mekanis manakah yang paling mungkin memicu munculnya kode DTC P0171 (System Too Lean)?',
                    'opsi' => [['key'=>'A','text'=>'Terjadi kebocoran vakum pada selang intake manifold atau sensor MAF kotor.'],['key'=>'B','text'=>'Injektor bensin menyemprotkan bahan bakar secara berlebihan tanpa henti.'],['key'=>'C','text'=>'Tekanan bahan bakar dari fuel pump terlalu tinggi melampaui batas standar.'],['key'=>'D','text'=>'Saringan udara (air filter) tersumbat debu sangat tebal.'],['key'=>'E','text'=>'Busi aus sehingga pengapian terlalu besar.']],
                    'kunci' => 'A',
                    'pembahasan' => 'Campuran miskin (too lean) terjadi jika ada udara palsu masuk tanpa terukur (kebocoran intake) atau sensor MAF kotor sehingga membaca aliran udara lebih rendah dari aslinya.',
                    'bobot' => 1.0
                ]
            ]
        ],

        // -------------------------------------------------------------
        // SMK - BISNIS DIGITAL & PEMASARAN
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-SMK-BISDIG',
            'judul' => 'TKA Kejuruan SMK: Bisnis Digital, E-Commerce & Pemasaran',
            'kategori' => 'SMK Bisnis',
            'jenjang' => 'SMK / MAK',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-BDG-01',
                'judul' => 'Strategi Funneling Konversi dan Optimasi Metrik Digital Marketing',
                'konten' => '<div class="space-y-3"><p class="font-bold">Analisis Return on Ad Spend (ROAS) dan Customer Acquisition Cost (CAC)</p><p>Sebuah toko daring menginvestasikan Rp10.000.000,- untuk kampanye iklan berbayar (Paid Ads) yang menghasilkan 200.000 impresi, 4.000 klik (CTR 2%), dan berhasil mendatangkan 100 transaksi pembeli baru dengan total omzet Rp35.000.000,-.</p></div>',
                'sumber' => 'Pusmendik - Standar Kompetensi Keahlian Bisnis Digital'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Metrik Pemasaran Digital', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Berapakah nilai Biaya Perolehan Pelanggan (Customer Acquisition Cost / CAC) pada kampanye iklan di atas?',
                    'opsi' => [['key'=>'A','text'=>'Rp50.000,- per pelanggan'],['key'=>'B','text'=>'Rp100.000,- per pelanggan'],['key'=>'C','text'=>'Rp250.000,- per pelanggan'],['key'=>'D','text'=>'Rp350.000,- per pelanggan'],['key'=>'E','text'=>'Rp500.000,- per pelanggan']],
                    'kunci' => 'B',
                    'pembahasan' => 'Rumus CAC: Total Biaya Iklan / Jumlah Pelanggan Baru = Rp10.000.000 / 100 pelanggan = Rp100.000,- per pelanggan.',
                    'bobot' => 1.0
                ]
            ]
        ],

        // -------------------------------------------------------------
        // SMK - FARMASI KLINIS & KOMUNITAS
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-SMK-FARMASI',
            'judul' => 'TKA Kejuruan SMK: Farmasi Klinis, Resep & Perhitungan Dosis',
            'kategori' => 'SMK Kesehatan',
            'jenjang' => 'SMK / MAK',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-FAR-01',
                'judul' => 'Skrining Kelayakan Resep dan Perhitungan Dosis Maksimum Pediatrik',
                'konten' => '<div class="space-y-3"><p class="font-bold">Penyesuaian Dosis Obat Anak Menggunakan Rumus Young</p><p>Seorang anak berusia 8 tahun mendapat resep puyer Parasetamol. Rumus Young untuk anak di bawah 12 tahun adalah: $Da = \\frac{n}{n + 12} \\times Dd$, di mana $n$ adalah usia anak dalam tahun dan $Dd$ adalah Dosis Dewasa standar ($500\\text{ mg}$).</p></div>',
                'sumber' => 'Pusmendik - Farmakologi & Pelayanan Resep Farmasi'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Perhitungan Farmasi', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Berdasarkan Rumus Young, berapakah dosis sekali minum Parasetamol yang tepat untuk anak usia 8 tahun tersebut?',
                    'opsi' => [['key'=>'A','text'=>'100 mg'],['key'=>'B','text'=>'150 mg'],['key'=>'C','text'=>'200 mg'],['key'=>'D','text'=>'250 mg'],['key'=>'E','text'=>'300 mg']],
                    'kunci' => 'C',
                    'pembahasan' => 'Da = (8 / (8 + 12)) x 500 mg = (8 / 20) x 500 mg = 0,4 x 500 mg = 200 mg.',
                    'bobot' => 1.0
                ]
            ]
        ],

        // -------------------------------------------------------------
        // SMK - AGRIBISNIS & PERTANIAN
        // -------------------------------------------------------------
        [
            'kode' => 'TKA-SMK-TANI',
            'judul' => 'TKA Kejuruan SMK: Agribisnis Tanaman & Teknologi Hidroponik',
            'kategori' => 'SMK Agribisnis',
            'jenjang' => 'SMK / MAK',
            'durasi' => 45,
            'stimulus' => [
                'kode' => 'STM-AGR-01',
                'judul' => 'Pengelolaan Larutan Nutrisi AB Mix pada Sistem Hidroponik NFT',
                'konten' => '<div class="space-y-3"><p class="font-bold">Parameter Electrical Conductivity (EC) dan Derajat Keasaman (pH)</p><p>Budidaya tanaman selada hidroponik dengan sistem Nutrient Film Technique (NFT) memerlukan pemantauan kepekatan nutrisi pada rentang 560 - 840 ppm (EC 1,2 - 1,8 mS/cm) dan pH larutan optimal 5,5 - 6,5.</p></div>',
                'sumber' => 'Pusmendik - Standar Kompetensi Agribisnis Tanaman'
            ],
            'soal' => [
                [
                    'nomor' => 1, 'subtes' => 'Kultur Tanaman Hidroponik', 'tipe' => 'pg_tunggal',
                    'tanya' => 'Mengapa larutan pekat nutrisi Stok A dan Stok B tidak boleh dicampurkan dalam kondisi pekat tanpa air pengencer?',
                    'opsi' => [['key'=>'A','text'=>'Karena akan terjadi reaksi pengendapan kimia kalsium sulfat atau kalsium fosfat yang tidak dapat diserap akar.'],['key'=>'B','text'=>'Karena larutan akan menguap seketika menjadi gas berbahaya.'],['key'=>'C','text'=>'Karena suhu larutan akan melonjak hingga mendidih.'],['key'=>'D','text'=>'Karena larutan akan berubah warna menjadi hitam pekat dan berbau busuk.'],['key'=>'E','text'=>'Karena nutrisi akan langsung kehilangan kandungan unsur nitrogennya.']],
                    'kunci' => 'A',
                    'pembahasan' => 'Unsur kalsium pada Stok A jika bertemu sulfat/fosfat pada Stok B dalam kondisi pekat akan bereaksi membentuk endapan kapur yang tak larut, sehingga tanaman mengalami defisiensi nutrisi.',
                    'bobot' => 1.0
                ]
            ]
        ]
    ];

    // Simpan ke database
    $existingCodes = $pdo->query("SELECT kode FROM paket_ujian")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($paketData as $p) {
        if (in_array($p['kode'], $existingCodes)) {
            continue; // Skip jika sudah ada
        }

        $stmtPaket = $pdo->prepare("INSERT INTO paket_ujian (kode, judul, kategori, jenjang, durasi_menit, petunjuk) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtPaket->execute([
            $p['kode'],
            $p['judul'],
            $p['kategori'],
            $p['jenjang'],
            $p['durasi'],
            "Petunjuk Asesmen:\n1. Kerjakan dengan cermat sesuai wacana kejuruan/mapel.\n2. Waktu pengerjaan {$p['durasi']} menit."
        ]);
        $paketId = (int)$pdo->lastInsertId();

        // Stimulus
        $s = $p['stimulus'];
        $stmtStim = $pdo->prepare("INSERT INTO stimulus (kode, judul, konten_html, sumber) VALUES (?, ?, ?, ?)");
        $stmtStim->execute([$s['kode'], $s['judul'], $s['konten'], $s['sumber']]);
        $stimId = (int)$pdo->lastInsertId();

        // Soal-soal
        $stmtSoal = $pdo->prepare("INSERT INTO soal (paket_id, stimulus_id, nomor_urut, subtes, tipe_soal, pertanyaan, opsi_jawaban, kunci_jawaban, pembahasan, bobot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($p['soal'] as $soal) {
            $stmtSoal->execute([
                $paketId,
                $stimId,
                $soal['nomor'],
                $soal['subtes'],
                $soal['tipe'],
                $soal['tanya'],
                $soal['opsi'] ? json_encode($soal['opsi'], JSON_UNESCAPED_UNICODE) : null,
                json_encode($soal['kunci'], JSON_UNESCAPED_UNICODE),
                $soal['pembahasan'],
                $soal['bobot']
            ]);
        }
    }
}
