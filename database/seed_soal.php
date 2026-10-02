<?php
// database/seed_soal.php - Seeder Bank Soal Lengkap Standar Pusmendik & SMK Kejuruan

function seedPusmendikData(PDO $pdo) {
    // Panggil seeder seluruh mapel SMA & SMK
    require_once __DIR__ . '/seed_all_mapel.php';
    seedAllMapel($pdo);

    // 1. Cek paket ujian yang sudah ada
    $existing = $pdo->query("SELECT kode FROM paket_ujian")->fetchAll(PDO::FETCH_COLUMN);

    // =========================================================================
    // PAKET 1: UMUM - Literasi Membaca & Numerasi Pusmendik
    // =========================================================================
    if (!in_array('TKA-PUSMENDIK-01', $existing)) {
        $stmt = $pdo->prepare("INSERT INTO paket_ujian (kode, judul, kategori, jenjang, durasi_menit, petunjuk) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'TKA-PUSMENDIK-01',
            'Simulasi Asesmen Nasional: Literasi Sains & Penalaran Numerasi',
            'Literasi & Numerasi Umum',
            'SMA / SMK / MA',
            45,
            'Petunjuk Umum:\n1. Ujian terdiri dari berbagai jenis soal terstandar Pusmendik.\n2. Cermati wacana stimulus di sisi kiri sebelum menjawab.\n3. Jawaban otomatis tersimpan setiap kali Anda memilih opsi.'
        ]);
        $paket1Id = (int)$pdo->lastInsertId();

        // Stimulus 1: Literasi Energi
        $stmtStim = $pdo->prepare("INSERT INTO stimulus (kode, judul, konten_html, sumber) VALUES (?, ?, ?, ?)");
        $stim1_html = <<<HTML
<div class="space-y-4 text-slate-800 leading-relaxed text-sm md:text-base">
    <p class="font-bold text-slate-900 text-base md:text-lg">
        Dilema Transisi Energi Terbarukan: Peluang dan Tantangan Panel Surya di Wilayah Tropis
    </p>
    <p>
        Indonesia memiliki potensi energi surya yang luar biasa besar berkat posisinya di garis khatulistiwa, dengan rata-rata radiasi harian mencapai <strong>4,80 kWh/m²</strong>. Pemerintah menargetkan bauran Energi Baru Terbarukan (EBT) dapat mencapai minimal 23% dalam sistem kelistrikan nasional. Salah satu teknologi yang paling cepat diadopsi oleh masyarakat dan sektor industri adalah Pembangkit Listrik Tenaga Surya (PLTS) atap (*rooftop solar*).
    </p>
    
    <div class="my-4 p-4 bg-slate-50 border-l-4 border-blue-900 rounded-r">
        <h4 class="font-bold text-blue-950 mb-2">Tabel 1: Perbandingan Karakteristik Teknologi Modul Panel Surya Komersial</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs md:text-sm text-left border-collapse bg-white border border-slate-300">
                <thead>
                    <tr class="bg-slate-200 text-slate-900 border-b border-slate-300">
                        <th class="p-2.5 border border-slate-300">Jenis Modul</th>
                        <th class="p-2.5 border border-slate-300">Efisiensi Rata-rata (%)</th>
                        <th class="p-2.5 border border-slate-300">Koefisien Suhu (%/°C)</th>
                        <th class="p-2.5 border border-slate-300">Daya Tahan Operasional</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-200">
                        <td class="p-2.5 border border-slate-300 font-semibold">Monokristalin (Mono-Si)</td>
                        <td class="p-2.5 border border-slate-300">20% – 22%</td>
                        <td class="p-2.5 border border-slate-300">-0,35</td>
                        <td class="p-2.5 border border-slate-300">25 – 30 tahun</td>
                    </tr>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <td class="p-2.5 border border-slate-300 font-semibold">Polikristalin (Poly-Si)</td>
                        <td class="p-2.5 border border-slate-300">15% – 17%</td>
                        <td class="p-2.5 border border-slate-300">-0,42</td>
                        <td class="p-2.5 border border-slate-300">20 – 25 tahun</td>
                    </tr>
                    <tr>
                        <td class="p-2.5 border border-slate-300 font-semibold">Perovskit Hibrida (R&D)</td>
                        <td class="p-2.5 border border-slate-300">25% – 28%</td>
                        <td class="p-2.5 border border-slate-300">-0,28</td>
                        <td class="p-2.5 border border-slate-300">5 – 10 tahun (degradasi cepat)</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-slate-500 mt-2 italic">*Koefisien suhu menunjukkan penurunan efisiensi panel untuk setiap kenaikan suhu 1°C di atas suhu uji standar (25°C).</p>
    </div>

    <p>
        Meskipun memiliki potensi surya melimpah, iklim tropis lembap Indonesia menyimpan tantangan teknis tersendiri. Kenaikan suhu permukaan panel akibat terik matahari justru menurunkan efisiensi konversi daya, sesuai koefisien suhu bahan. Selain itu, faktor tutupan awan (*intermittency*) dan debu/polusi membutuhkan sistem penyimpanan energi baterai (*Battery Energy Storage System* / BESS) yang andal agar stabilitas tegangan jaringan PLN tetap terjaga.
    </p>
</div>
HTML;
        $stmtStim->execute(['STM-01', 'Dilema Transisi Energi Terbarukan di Wilayah Tropis', $stim1_html, 'Pusmendik & Kementerian ESDM RI']);
        $stim1Id = (int)$pdo->lastInsertId();

        // Soal 1
        $stmtSoal = $pdo->prepare("INSERT INTO soal (paket_id, stimulus_id, nomor_urut, subtes, tipe_soal, pertanyaan, opsi_jawaban, kunci_jawaban, pembahasan, bobot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtSoal->execute([
            $paket1Id, $stim1Id, 1, 'Literasi Membaca', 'pg_tunggal',
            'Berdasarkan Tabel 1 pada wacana, mengapa panel surya jenis Monokristalin lebih direkomendasikan untuk proyek jangka panjang di wilayah perkotaan beriklim tropis dibandingkan jenis Polikristalin?',
            json_encode([
                ['key' => 'A', 'text' => 'Karena panel Monokristalin memiliki biaya manufaktur yang paling murah di pasaran.'],
                ['key' => 'B', 'text' => 'Karena efisiensi konversinya lebih tinggi dan koefisien suhunya lebih kecil sehingga penurunan daya akibat panas terik lebih minim.'],
                ['key' => 'C', 'text' => 'Karena dapat menyerap energi surya secara maksimal hanya ketika cuaca mendung dan tertutup awan tebal.'],
                ['key' => 'D', 'text' => 'Karena tidak membutuhkan perawatan berkala serta tidak terpengaruh oleh debu dan polusi udara.'],
                ['key' => 'E', 'text' => 'Karena memiliki masa operasional yang identik dengan panel Perovskit hibrida.']
            ], JSON_UNESCAPED_UNICODE),
            json_encode('B'),
            'Jawaban B benar. Berdasarkan Tabel 1, Monokristalin memiliki efisiensi tinggi (20-22%) dan koefisien suhu -0,35%/°C (lebih kecil daripada Polikristalin -0,42%/°C) sehingga lebih tahan terhadap penurunan daya akibat cuaca terik tropis.',
            1.0
        ]);

        // Soal 2
        $stmtSoal->execute([
            $paket1Id, $stim1Id, 2, 'Literasi Membaca', 'pg_kompleks',
            'Faktor apa sajakah yang menjadi tantangan teknis dalam pengoperasian pembangkit listrik tenaga surya (PLTS) di Indonesia berdasarkan teks wacana? (Pilih semua jawaban yang benar)',
            json_encode([
                ['key' => 'A', 'text' => 'Kenaikan suhu permukaan panel yang dapat menurunkan efisiensi konversi daya.'],
                ['key' => 'B', 'text' => 'Ketiadaan potensi radiasi matahari harian di sebagian besar pulau Indonesia.'],
                ['key' => 'C', 'text' => 'Sifat intermittency (fluktuasi daya) akibat tutupan awan yang dinamis.'],
                ['key' => 'D', 'text' => 'Penumpukan debu dan polusi udara pada permukaan modul panel.'],
                ['key' => 'E', 'text' => 'Larangan pemerintah terkait pemanfaatan atap bangunan untuk energi surya.']
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['A', 'C', 'D']),
            'Jawaban A, C, dan D benar. Paragraf terakhir menyebutkan kenaikan suhu ekstrem, fluktuasi akibat tutupan awan (intermittency), dan kotoran debu/polusi sebagai tantangan nyata di iklim tropis.',
            1.0
        ]);

        // Soal 3
        $stmtSoal->execute([
            $paket1Id, $stim1Id, 3, 'Literasi Membaca', 'matriks_bs',
            'Tentukan kebenaran pernyataan-pernyataan berikut berdasarkan isi teks wacana dan data Tabel 1!',
            json_encode([
                'kolom' => ['Benar', 'Salah'],
                'pernyataan' => [
                    ['id' => 'p1', 'teks' => 'Panel surya Perovskit Hibrida saat ini telah menjadi standar modul komersial paling awet dengan usia pakai lebih dari 30 tahun.'],
                    ['id' => 'p2', 'teks' => 'Rata-rata radiasi harian sinar matahari di wilayah Indonesia mencapai sekitar 4,80 kWh/m².'],
                    ['id' => 'p3', 'teks' => 'Kenaikan suhu lingkungan kerja panel surya sebesar 10°C di atas standar akan menyebabkan efisiensi panel Polikristalin turun sebesar 4,2%.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['p1' => 'Salah', 'p2' => 'Benar', 'p3' => 'Benar']),
            'Pernyataan 1 Salah (Perovskit masih R&D dengan usia 5-10 tahun). Pernyataan 2 Benar. Pernyataan 3 Benar (10 x 0,42% = 4,2%).',
            1.0
        ]);
    }

    // =========================================================================
    // PAKET 2: SMK - TEKNOLOGI INFORMASI & KOMPUTER (TKJ / RPL / SIJA)
    // =========================================================================
    if (!in_array('TKA-SMK-TKJ', $existing)) {
        $stmt = $pdo->prepare("INSERT INTO paket_ujian (kode, judul, kategori, jenjang, durasi_menit, petunjuk) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'TKA-SMK-TKJ',
            'TKA Kejuruan SMK: Teknik Jaringan Komputer, Server & Keamanan Siber',
            'Teknologi Informasi & Komunikasi',
            'SMK / MAK',
            45,
            'Petunjuk Khusus SMK TIK:\n1. Soal menguji kompetensi jaringan komputer, subnetting, arsitektur server, dan keamanan data.\n2. Cermati skema topologi dan tabel konfigurasi pada panel wacana sebelum menjawab.'
        ]);
        $paketTkjId = (int)$pdo->lastInsertId();

        $stimTkj_html = <<<HTML
<div class="space-y-4 text-slate-800 leading-relaxed text-sm md:text-base">
    <p class="font-bold text-slate-900 text-base md:text-lg">
        Studi Kasus: Optimalisasi Jaringan Kampus & Keamanan Server Cloud Perusahaan
    </p>
    <p>
        Sebuah perusahaan logistik memiliki kantor pusat dengan subnet IP Private kelas C: <strong>192.168.10.0/24</strong>. Administrator jaringan diminta membagi jaringan ini ke dalam 4 divisi kerja independen menggunakan teknik <em>Classless Inter-Domain Routing (CIDR) Subnetting</em> dengan alokasi sebagai berikut:
    </p>

    <div class="my-3 p-4 bg-slate-50 border border-slate-300 rounded">
        <h4 class="font-bold text-slate-900 mb-2">Tabel Alokasi Kebutuhan Host Per Divisi:</h4>
        <ul class="list-disc pl-5 space-y-1 text-xs md:text-sm">
            <li><strong>Divisi IT & Server:</strong> Membutuhkan 50 host (menggunakan subnet prefix <code>/26</code>).</li>
            <li><strong>Divisi Operasional & Kasir:</strong> Membutuhkan 25 host (menggunakan subnet prefix <code>/27</code>).</li>
            <li><strong>Divisi Administrasi & HR:</strong> Membutuhkan 25 host (menggunakan subnet prefix <code>/27</code>).</li>
            <li><strong>VLAN Manajemen Perangkat (Router/Switch):</strong> Membutuhkan 10 host (menggunakan subnet prefix <code>/28</code>).</li>
        </ul>
    </div>

    <p>
        Untuk menjaga ketersediaan layanan (*high availability*), server basis data menggunakan konfigurasi <strong>RAID 10 (Mirroring + Striping)</strong> dengan 4 unit drive SSD NVMe Enterprise, serta diproteksi oleh <em>Next-Generation Firewall (NGFW)</em> yang menerapkan inspeksi paket mendalam (*Stateful Packet Inspection*) dan proteksi serangan brute-force SSH.
    </p>
</div>
HTML;
        $stmtStim = $pdo->prepare("INSERT INTO stimulus (kode, judul, konten_html, sumber) VALUES (?, ?, ?, ?)");
        $stmtStim->execute(['STM-TKJ-01', 'Optimalisasi Jaringan & Keamanan Server Cloud', $stimTkj_html, 'Pusmendik - Standar Kompetensi Keahlian SMK TIK']);
        $stimTkjId = (int)$pdo->lastInsertId();

        // Soal 1: Subnetting
        $stmtSoal = $pdo->prepare("INSERT INTO soal (paket_id, stimulus_id, nomor_urut, subtes, tipe_soal, pertanyaan, opsi_jawaban, kunci_jawaban, pembahasan, bobot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtSoal->execute([
            $paketTkjId, $stimTkjId, 1, 'Teknik Jaringan Komputer', 'pg_tunggal',
            'Berdasarkan wacana di atas, berapakah Netmask desimal dan rentang usable IP host yang dapat dipakai pada subnet Divisi IT & Server dengan prefix 192.168.10.0/26?',
            json_encode([
                ['key' => 'A', 'text' => 'Netmask 255.255.255.0 dengan rentang IP 192.168.10.1 sampai 192.168.10.254'],
                ['key' => 'B', 'text' => 'Netmask 255.255.255.192 dengan rentang IP 192.168.10.1 sampai 192.168.10.62'],
                ['key' => 'C', 'text' => 'Netmask 255.255.255.224 dengan rentang IP 192.168.10.1 sampai 192.168.10.30'],
                ['key' => 'D', 'text' => 'Netmask 255.255.255.240 dengan rentang IP 192.168.10.1 sampai 192.168.10.14'],
                ['key' => 'E', 'text' => 'Netmask 255.255.255.128 dengan rentang IP 192.168.10.1 sampai 192.168.10.126']
            ], JSON_UNESCAPED_UNICODE),
            json_encode('B'),
            'Jawaban B benar. Prefix /26 memiliki netmask 255.255.255.192. Total host adalah 2^(32-26) = 64 IP. Network ID: 192.168.10.0, Broadcast ID: 192.168.10.63, sehingga rentang IP yang bisa dipakai oleh komputer (usable IP) adalah 192.168.10.1 s/d 192.168.10.62.',
            1.0
        ]);

        // Soal 2: Keamanan Jaringan
        $stmtSoal->execute([
            $paketTkjId, $stimTkjId, 2, 'Keamanan Jaringan & Sistem', 'pg_kompleks',
            'Langkah pengerasan keamanan (*hardening*) manakah yang paling efektif untuk mencegah serangan login paksa (*brute force*) pada port default SSH server Linux? (Pilih semua yang benar)',
            json_encode([
                ['key' => 'A', 'text' => 'Mengubah port standar SSH dari port 22 ke port non-standar (misal port 2222 atau 22222).'],
                ['key' => 'B', 'text' => 'Menonaktifkan login langsung menggunakan akun root (PermitRootLogin no).'],
                ['key' => 'C', 'text' => 'Menggunakan autentikasi berbasis kunci kriptografi (SSH Key-Pair) dan mematikan PasswordAuthentication.'],
                ['key' => 'D', 'text' => 'Memasang perangkat lunak Fail2Ban untuk memblokir IP penyerang secara otomatis setelah 3 kali gagal login.'],
                ['key' => 'E', 'text' => 'Membuka seluruh rentang port TCP di firewall agar koneksi tidak terhambat.']
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['A', 'B', 'C', 'D']),
            'Jawaban A, B, C, dan D benar. Mengganti port, mendisabel root login, menggunakan SSH Key, dan memasang Fail2Ban adalah standar industri server hardening. Opsi E justru membuka kerentanan fatal.',
            1.0
        ]);

        // Soal 3: Matriks Benar Salah
        $stmtSoal->execute([
            $paketTkjId, $stimTkjId, 3, 'Infrastruktur Server & Penyimpanan', 'matriks_bs',
            'Evaluasi kebenaran pernyataan teknis berikut terkait konfigurasi penyimpanan RAID pada server!',
            json_encode([
                'kolom' => ['Benar', 'Salah'],
                'pernyataan' => [
                    ['id' => 'p1', 'teks' => 'RAID 10 membutuhkan minimal 4 unit disk dan mampu mentolerir kerusakan hingga 1 disk per sub-array mirror tanpa kehilangan data.'],
                    ['id' => 'p2', 'teks' => 'Kapasitas efektif total RAID 10 dari 4 unit disk berukuran masing-masing 1 TB adalah 4 TB.'],
                    ['id' => 'p3', 'teks' => 'RAID 0 menawarkan kecepatan baca/tulis tercepat tetapi memiliki toleransi kesalahan (fault tolerance) nol persen.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['p1' => 'Benar', 'p2' => 'Salah', 'p3' => 'Benar']),
            'Pernyataan 1 Benar. Pernyataan 2 Salah (RAID 10 menyalin data/mirroring, sehingga 4 x 1TB hanya menghasilkan 2 TB kapasitas efektif). Pernyataan 3 Benar (RAID 0 murni striping tanpa redundansi).',
            1.0
        ]);

        // Soal 4: Menjodohkan Protokol
        $stmtSoal->execute([
            $paketTkjId, $stimTkjId, 4, 'Protokol & Layanan Jaringan', 'menjodohkan',
            'Pasangkanlah nama protokol internet berikut dengan port standar dan fungsinya!',
            json_encode([
                'premis' => [
                    ['id' => 'm1', 'teks' => 'DNS (Domain Name System)'],
                    ['id' => 'm2', 'teks' => 'HTTPS (Hypertext Transfer Protocol Secure)'],
                    ['id' => 'm3', 'teks' => 'DHCP (Dynamic Host Configuration Protocol)']
                ],
                'target' => [
                    ['id' => 't1', 'teks' => 'Port UDP 67/68 untuk pendistribusian IP otomatis ke perangkat klien.'],
                    ['id' => 't2', 'teks' => 'Port TCP 443 untuk transfer data web terenkripsi SSL/TLS.'],
                    ['id' => 't3', 'teks' => 'Port UDP/TCP 53 untuk menerjemahkan nama domain menjadi alamat IP.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['m1' => 't3', 'm2' => 't2', 'm3' => 't1']),
            'DNS beroperasi pada port 53 (t3), HTTPS pada port 443 (t2), dan DHCP pada port 67/68 (t1).',
            1.0
        ]);

        // Soal 5: Isian Singkat
        $stmtSoal->execute([
            $paketTkjId, $stimTkjId, 5, 'Perhitungan Subnetting', 'isian_singkat',
            'Berapakah jumlah maksimal host komputer yang dapat digunakan (usable host IP) pada subnet VLAN Manajemen dengan notasi prefix /28? (Tuliskan angkanya saja)',
            null,
            json_encode(['14']),
            'Rumus host: 2^(32-prefix) - 2. Untuk /28: 2^(32-28) - 2 = 2^4 - 2 = 16 - 2 = 14 host.',
            1.0
        ]);
    }

    // =========================================================================
    // PAKET 3: SMK - TEKNOLOGI & REKAYASA (MESIN / ELEKTRO / OTOMASI)
    // =========================================================================
    if (!in_array('TKA-SMK-OTOMASI', $existing)) {
        $stmt = $pdo->prepare("INSERT INTO paket_ujian (kode, judul, kategori, jenjang, durasi_menit, petunjuk) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'TKA-SMK-OTOMASI',
            'TKA Kejuruan SMK: Teknik Mesin, Kelistrikan & Otomasi Industri',
            'Teknologi & Rekayasa',
            'SMK / MAK',
            45,
            'Petunjuk Khusus SMK Rekayasa:\n1. Soal menguji kompetensi instalasi motor 3 fasa, pneumatik, rangkaian kendali relay, dan PLC industri.\n2. Perhatikan parameter kelistrikan dan standar K3 industri dengan teliti.'
        ]);
        $paketOtoId = (int)$pdo->lastInsertId();

        $stimOto_html = <<<'HTML'
<div class="space-y-4 text-slate-800 leading-relaxed text-sm md:text-base">
    <p class="font-bold text-slate-900 text-base md:text-lg">
        Lini Produksi Manufaktur: Pengendalian Motor Induksi 3 Fasa & Pemrograman PLC
    </p>
    <p>
        Sebuah pabrik perakitan otomotif mengoperasikan konveyor utama yang digerakkan oleh <strong>motor induksi 3 fasa sangkar tupai</strong> dengan spesifikasi nameplate: Tegangan $V = 380\text{ V}$, Arus nominal $I = 10\text{ A}$, Frekuensi $f = 50\text{ Hz}$, Faktor daya $\cos\phi = 0,85$, dan kecepatan putar nominal $1.440\text{ RPM}$.
    </p>

    <div class="my-3 p-4 bg-slate-50 border border-slate-300 rounded">
        <h4 class="font-bold text-slate-900 mb-2">Komponen Pengaman & Kendali:</h4>
        <ul class="list-disc pl-5 space-y-1 text-xs md:text-sm">
            <li><strong>Thermal Overload Relay (TOR):</strong> Pengaman beban lebih berbasis bimetal yang diset pada $10,5\text{ A}$.</li>
            <li><strong>Magnetic Contactor:</strong> Dilengkapi kontak utama (Line) dan kontak bantu (NO / NC) untuk sistem pengunci (*self-holding/latching*).</li>
            <li><strong>Programmable Logic Controller (PLC):</strong> Mengatur siklus maju-mundur (*forward-reverse*) dengan interlock listrik (*electrical interlock*) untuk mencegah hubung singkat antarfasa.</li>
        </ul>
    </div>

    <p>
        Rumus daya listrik aktif pada sistem 3 fasa dihitung dengan formula:<br>
        <span class="inline-block bg-slate-100 p-2 font-mono text-sm border border-slate-300 my-1">
            P = &radic;3 &times; V &times; I &times; cos&phi; &nbsp; (di mana &radic;3 &approx; 1,732)
        </span>
    </p>
</div>
HTML;
        $stmtStim = $pdo->prepare("INSERT INTO stimulus (kode, judul, konten_html, sumber) VALUES (?, ?, ?, ?)");
        $stmtStim->execute(['STM-OTO-01', 'Kendali Motor 3 Fasa & Otomasi Industri', $stimOto_html, 'Pusmendik - Standar Kompetensi Keahlian Teknik Mekatronika & Otomasi']);
        $stimOtoId = (int)$pdo->lastInsertId();

        // Soal 1
        $stmtSoal = $pdo->prepare("INSERT INTO soal (paket_id, stimulus_id, nomor_urut, subtes, tipe_soal, pertanyaan, opsi_jawaban, kunci_jawaban, pembahasan, bobot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtSoal->execute([
            $paketOtoId, $stimOtoId, 1, 'Sistem Kendali Elektromagnetik', 'pg_tunggal',
            'Mengapa pada rangkaian kendali motor 3 fasa putar maju-mundur (Forward-Reverse) mutlak diperlukan sistem Electrical Interlock pada koil kontaktor?',
            json_encode([
                ['key' => 'A', 'text' => 'Untuk menghemat konsumsi energi listrik PLN saat motor berputar dengan kecepatan tinggi.'],
                ['key' => 'B', 'text' => 'Untuk mencegah kontaktor Forward dan Reverse bekerja bersamaan yang akan menyebabkan hubung singkat (short circuit) antarfasa.'],
                ['key' => 'C', 'text' => 'Agar motor dapat langsung berhenti seketika tanpa memerlukan sistem pengereman mekanik.'],
                ['key' => 'D', 'text' => 'Untuk menaikkan nilai faktor daya (cos phi) mendekati angka 1.'],
                ['key' => 'E', 'text' => 'Agar kabel fasa Netral tidak mengalami kelebihan beban tegangan bocor.']
            ], JSON_UNESCAPED_UNICODE),
            json_encode('B'),
            'Jawaban B benar. Pada putaran forward-reverse, dua fasa ditukar posisinya (misal fasa R dan T). Jika kontaktor forward dan reverse menyala secara bersamaan, akan terjadi tabrakan fasa atau hubungan singkat antar fasa (short circuit fatal).',
            1.0
        ]);

        // Soal 2
        $stmtSoal->execute([
            $paketOtoId, $stimOtoId, 2, 'Proteksi & Keselamatan Kerja Listrik', 'pg_kompleks',
            'Penyebab manakah di bawah ini yang dapat memicu Thermal Overload Relay (TOR) memutus rangkaian (*trip*) pada motor konveyor industri? (Pilih semua yang benar)',
            json_encode([
                ['key' => 'A', 'text' => 'Beban mekanik konveyor macet (*jammed*) sehingga rotor motor tertahan dan menarik arus sangat tinggi.'],
                ['key' => 'B', 'text' => 'Terjadi hilangnya salah satu fasa (*single-phasing*) saat motor sedang berjalan dengan beban.'],
                ['key' => 'C', 'text' => 'Tegangan jala-jala listrik turun drastis di bawah standar nominal operasi motor.'],
                ['key' => 'D', 'text' => 'Tombol Emergency Stop ditekan oleh operator saat terjadi situasi darurat.']
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['A', 'B', 'C']),
            'Jawaban A, B, dan C benar. Beban macet, hilangnya satu fasa (single phasing), dan undervoltage akan menaikkan arus motor yang memanaskan bimetal TOR hingga trip. Emergency stop memutus kendali melalui kontak NC tombol, bukan lewat panas TOR (D salah).',
            1.0
        ]);

        // Soal 3
        $stmtSoal->execute([
            $paketOtoId, $stimOtoId, 3, 'Sensor & Komponen Otomasi', 'matriks_bs',
            'Tentukan kebenaran karakteristik teknis sensor otomasi industri berikut!',
            json_encode([
                'kolom' => ['Benar', 'Salah'],
                'pernyataan' => [
                    ['id' => 'p1', 'teks' => 'Sensor Proximity Induktif hanya dapat mendeteksi benda atau objek yang terbuat dari bahan logam (konduktor).'],
                    ['id' => 'p2', 'teks' => 'Sensor Proximity Kapasitif dapat digunakan untuk mendeteksi cairan, serbuk kayu, maupun benda non-logam.'],
                    ['id' => 'p3', 'teks' => 'Kontak Normally Closed (NC) akan mengalirkan arus listrik hanya ketika koil relay dalam kondisi mendapat suplai tegangan (energized).']
                ]
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['p1' => 'Benar', 'p2' => 'Benar', 'p3' => 'Salah']),
            'Pernyataan 1 Benar (Induktif khusus logam). Pernyataan 2 Benar (Kapasitif mendeteksi dielektrik non-logam). Pernyataan 3 Salah (NC justru mengalirkan arus saat relay TIDAK diberi tegangan, dan terbuka saat energized).',
            1.0
        ]);

        // Soal 4
        $stmtSoal->execute([
            $paketOtoId, $stimOtoId, 4, 'Komponen Panel Industri', 'menjodohkan',
            'Jodohkanlah komponen panel kelistrikan industri berikut dengan fungsinya!',
            json_encode([
                'premis' => [
                    ['id' => 'k1', 'teks' => 'Variable Frequency Drive (Inverter / VFD)'],
                    ['id' => 'k2', 'teks' => 'Molded Case Circuit Breaker (MCCB)'],
                    ['id' => 'k3', 'teks' => 'Push Button Latching (Self-Lock)']
                ],
                'target' => [
                    ['id' => 't1', 'teks' => 'Pengaman hubung singkat dan beban lebih arus besar pada jalur utama suplai daya.'],
                    ['id' => 't2', 'teks' => 'Mengatur kecepatan putaran motor AC dengan memvariasikan frekuensi dan tegangan listrik.'],
                    ['id' => 't3', 'teks' => 'Saklar mekanik yang mempertahankan posisinya (on/off) setelah ditekan satu kali.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['k1' => 't2', 'k2' => 't1', 'k3' => 't3']),
            'VFD mengatur kecepatan putar motor (t2); MCCB pengaman arus besar (t1); Push button latching mempertahankan posisi kontak (t3).',
            1.0
        ]);

        // Soal 5
        $stmtSoal->execute([
            $paketOtoId, $stimOtoId, 5, 'Kalkulasi Daya Listrik 3 Fasa', 'isian_singkat',
            'Hitunglah estimasi daya listrik aktif (P) yang diserap motor dalam satuan Watt (dibulatkan tanpa koma) jika V = 380V, I = 10A, cos phi = 0.85, dan akar 3 = 1.732!',
            null,
            json_encode(['5594', '5594.3', '5595']),
            'Rumus: P = 1.732 x 380 x 10 x 0.85 = 5.594,36 Watt (sekitar 5.594 Watt atau 5,6 kW).',
            1.0
        ]);
    }

    // =========================================================================
    // PAKET 4: SMK - BISNIS & AKUNTANSI (AKL / PERBANKAN / PAJAK)
    // =========================================================================
    if (!in_array('TKA-SMK-AKUNTANSI', $existing)) {
        $stmt = $pdo->prepare("INSERT INTO paket_ujian (kode, judul, kategori, jenjang, durasi_menit, petunjuk) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'TKA-SMK-AKUNTANSI',
            'TKA Kejuruan SMK: Akuntansi Keuangan Lembaga & Perpajakan',
            'Bisnis & Manajemen',
            'SMK / MAK',
            45,
            'Petunjuk Khusus SMK Akuntansi:\n1. Soal menguji kompetensi siklus akuntansi, rekonsiliasi kas, jurnal penyesuaian, dan pajak pertambahan nilai (PPN).\n2. Teliti angka mutasi debit-kredit pada wacana transaksi keuangan.'
        ]);
        $paketAklId = (int)$pdo->lastInsertId();

        $stimAkl_html = <<<HTML
<div class="space-y-4 text-slate-800 leading-relaxed text-sm md:text-base">
    <p class="font-bold text-slate-900 text-base md:text-lg">
        Siklus Akuntansi Akhir Periode & Rekonsiliasi Bank PT Mandiri Niaga
    </p>
    <p>
        Pada tanggal 31 Desember 2025, bagian keuangan PT Mandiri Niaga menerima rekening koran dari Bank Mandiri yang menunjukkan saldo kas sebesar <strong>Rp82.500.000,-</strong>. Namun, catatan buku kas bank perusahaan menunjukkan saldo sebesar <strong>Rp74.200.000,-</strong>.
    </p>

    <div class="my-3 p-4 bg-slate-50 border border-slate-300 rounded">
        <h4 class="font-bold text-slate-900 mb-2">Hasil Pemeriksaan Bukti Transaksi:</h4>
        <ul class="list-disc pl-5 space-y-1 text-xs md:text-sm">
            <li><strong>Setoran Dalam Perjalanan (*Deposit in Transit*):</strong> Setoran tanggal 31 Desember sebesar Rp15.000.000,- belum tercatat di rekening koran bank.</li>
            <li><strong>Cek Beredar (*Outstanding Check*):</strong> Cek nomor CK-8902 senilai Rp24.500.000,- yang telah diserahkan kepada pemasok belum dicairkan ke bank.</li>
            <li><strong>Jasa Giro & Beban Administrasi:</strong> Bank mengkreditkan jasa giro Rp1.200.000,- dan mendebitkan biaya administrasi buku cek Rp400.000,- yang belum dicatat di buku kas perusahaan.</li>
            <li><strong>Cek Kosong (*Non-Sufficient Fund*):</strong> Cek piutang dari pelanggan CV Berkah sebesar Rp2.000.000,- ditolak bank karena saldo tidak mencukupi.</li>
        </ul>
    </div>

    <p>
        Selain rekonsiliasi bank, perusahaan memiliki persediaan barang dagang yang dihitung menggunakan metode <strong>FIFO (First In, First Out) Fisik</strong> serta kewajiban pemungutan <strong>PPN 11%</strong> atas penjualan barang kena pajak kepada Pengusaha Kena Pajak (PKP).
    </p>
</div>
HTML;
        $stmtStim = $pdo->prepare("INSERT INTO stimulus (kode, judul, konten_html, sumber) VALUES (?, ?, ?, ?)");
        $stmtStim->execute(['STM-AKL-01', 'Rekonsiliasi Bank & Akuntansi Pajak', $stimAkl_html, 'Pusmendik - Standar Kompetensi Keahlian Akuntansi']);
        $stimAklId = (int)$pdo->lastInsertId();

        // Soal 1
        $stmtSoal = $pdo->prepare("INSERT INTO soal (paket_id, stimulus_id, nomor_urut, subtes, tipe_soal, pertanyaan, opsi_jawaban, kunci_jawaban, pembahasan, bobot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtSoal->execute([
            $paketAklId, $stimAklId, 1, 'Rekonsiliasi Kas Bank', 'pg_tunggal',
            'Dalam penyusunan laporan rekonsiliasi bank per 31 Desember 2025, perlakuan yang tepat atas transaksi Cek Beredar (Outstanding Check) sebesar Rp24.500.000,- adalah ...',
            json_encode([
                ['key' => 'A', 'text' => 'Menambah saldo kas pada catatan buku perusahaan.'],
                ['key' => 'B', 'text' => 'Mengurangi saldo kas pada catatan buku perusahaan.'],
                ['key' => 'C', 'text' => 'Mengurangi saldo kas menurut catatan rekening koran bank.'],
                ['key' => 'D', 'text' => 'Menambah saldo kas menurut catatan rekening koran bank.'],
                ['key' => 'E', 'text' => 'Tidak perlu disesuaikan karena transaksi belum selesai di kasir.']
            ], JSON_UNESCAPED_UNICODE),
            json_encode('C'),
            'Jawaban C benar. Cek beredar (outstanding check) sudah dicatat mengurangi kas perusahaan, tetapi bank belum mendebitnya. Maka pada rekonsiliasi, posisi saldo bank harus dikurangi (saldo bank - cek beredar).',
            1.0
        ]);

        // Soal 2
        $stmtSoal->execute([
            $paketAklId, $stimAklId, 2, 'Jurnal Penyesuaian Perusahaan', 'pg_kompleks',
            'Manakah dari pos-pos rekonsiliasi bank di atas yang MEMERLUKAN pembuatan Jurnal Penyesuaian (*Adjusting Entries*) pada pembukuan PT Mandiri Niaga? (Pilih semua yang benar)',
            json_encode([
                ['key' => 'A', 'text' => 'Penerimaan Jasa Giro bank sebesar Rp1.200.000,-.'],
                ['key' => 'B', 'text' => 'Beban administrasi bank sebesar Rp400.000,-.'],
                ['key' => 'C', 'text' => 'Cek kosong (NSF check) dari CV Berkah sebesar Rp2.000.000,-.'],
                ['key' => 'D', 'text' => 'Setoran dalam perjalanan (Deposit in transit) sebesar Rp15.000.000,-.']
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['A', 'B', 'C']),
            'Jawaban A, B, dan C benar. Pos yang perlu dibuat jurnal penyesuaian oleh perusahaan adalah pos-pos yang belum dicatat di buku kas perusahaan (jasa giro, adm bank, cek kosong). Setoran dalam perjalanan (D) sudah dicatat oleh perusahaan, hanya bank yang belum mencatat sehingga tidak perlu jurnal penyesuaian.',
            1.0
        ]);

        // Soal 3
        $stmtSoal->execute([
            $paketAklId, $stimAklId, 3, 'Persediaan & Pelaporan Pajak', 'matriks_bs',
            'Tentukan kebenaran prinsip akuntansi dan perpajakan berikut!',
            json_encode([
                'kolom' => ['Benar', 'Salah'],
                'pernyataan' => [
                    ['id' => 'p1', 'teks' => 'Dalam metode FIFO, persediaan akhir dinilai berdasarkan harga perolehan barang yang paling akhir dibeli.'],
                    ['id' => 'p2', 'teks' => 'PPN Masukan yang dibayarkan saat membeli barang dagang dapat dikreditkan terhadap PPN Keluaran yang dipungut dari pembeli.'],
                    ['id' => 'p3', 'teks' => 'Akun Pendapatan Jasa Giro di jurnal pada posisi Debet dan Kas di posisi Kredit.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['p1' => 'Benar', 'p2' => 'Benar', 'p3' => 'Salah']),
            'Pernyataan 1 Benar (FIFO mengasumsikan barang lama terjual lebih dulu, sisa adalah barang akhir). Pernyataan 2 Benar (mekanisme PPN). Pernyataan 3 Salah (Penerimaan kas dicatat Kas di Debet, Pendapatan Jasa Giro di Kredit).',
            1.0
        ]);

        // Soal 4
        $stmtSoal->execute([
            $paketAklId, $stimAklId, 4, 'Klasifikasi Akun Keuangan', 'menjodohkan',
            'Pasangkanlah kelompok akun berikut dengan saldo normal akuntansinya!',
            json_encode([
                'premis' => [
                    ['id' => 'a1', 'teks' => 'Kas di Bank, Piutang Usaha, Beban Gaji'],
                    ['id' => 'a2', 'teks' => 'Utang Usaha, Pendapatan Jasa, Modal Pemilik'],
                    ['id' => 'a3', 'teks' => 'Akumulasi Penyusutan Peralatan (Kontra Akun)']
                ],
                'target' => [
                    ['id' => 't1', 'teks' => 'Saldo Normal bertambah di posisi KREDIT.'],
                    ['id' => 't2', 'teks' => 'Saldo Normal bertambah di posisi DEBET.'],
                    ['id' => 't3', 'teks' => 'Saldo Normal berada di posisi KREDIT meskipun berada pada kelompok Aset.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            json_encode(['a1' => 't2', 'a2' => 't1', 'a3' => 't3']),
            'Aset dan beban bertambah di debet (t2); Kewajiban, ekuitas, pendapatan di kredit (t1); Kontra akun aset di kredit (t3).',
            1.0
        ]);

        // Soal 5
        $stmtSoal->execute([
            $paketAklId, $stimAklId, 5, 'Perhitungan PPN', 'isian_singkat',
            'Jika PT Mandiri Niaga menjual barang kena pajak dengan Dasar Pengenaan Pajak (DPP) sebesar Rp50.000.000,-, berapakah nominal rupiah PPN (11%) yang harus dipungut? (Tuliskan angkanya saja)',
            null,
            json_encode(['5500000', '5.500.000', '5,500,000']),
            'Perhitungan PPN: 11% x Rp50.000.000 = Rp5.500.000,-.',
            1.0
        ]);
    }
}
