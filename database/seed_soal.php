<?php
// database/seed_soal.php - Seeder Soal Resmi Standar Pusmendik (TKA / AKM)

function seedPusmendikData(PDO $pdo) {
    // 1. Masukkan Paket Ujian
    $stmt = $pdo->prepare("INSERT INTO paket_ujian (kode, judul, kategori, jenjang, durasi_menit, petunjuk) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'TKA-PUSMENDIK-01',
        'Simulasi Resmi TKA: Literasi Membaca & Penalaran Numerasi',
        'Tes Kemampuan Akademik (TKA)',
        'SMA/MA/SMK',
        45,
        'Petunjuk Umum:\n1. Ujian terdiri dari berbagai jenis soal: Pilihan Ganda Tunggal, Pilihan Ganda Kompleks, Pernyataan Benar/Salah, Menjodohkan, dan Isian Singkat.\n2. Baca wacana/stimulus pada panel kiri dengan cermat sebelum menjawab pertanyaan pada panel kanan.\n3. Gunakan tombol "Ragu-ragu" jika belum yakin dengan jawaban.\n4. Jawaban akan otomatis tersimpan setiap kali Anda memilih opsi.'
    ]);
    $paketId = (int)$pdo->lastInsertId();

    // 2. Masukkan Peserta Default untuk Simulasi Cepat
    $stmtPeserta = $pdo->prepare("INSERT INTO peserta (nomor_peserta, nama_lengkap, sekolah_asal) VALUES (?, ?, ?)");
    $stmtPeserta->execute([
        'TKA-2026-0819',
        'Peserta Simulasi Mandiri',
        'Pusat Asesmen Pendidikan (Pusmendik)'
    ]);

    // 3. Masukkan Stimulus 1: Literasi Sains & Energi
    $stmtStimulus = $pdo->prepare("INSERT INTO stimulus (kode, judul, konten_html, sumber) VALUES (?, ?, ?, ?)");
    $stimulus1_html = <<<HTML
<div class="space-y-4 text-slate-700 leading-relaxed text-sm md:text-base">
    <p class="font-semibold text-slate-900 text-base md:text-lg">
        Dilema Transisi Energi Terbarukan: Peluang dan Tantangan Panel Surya di Wilayah Tropis
    </p>
    <p>
        Indonesia memiliki potensi energi surya yang luar biasa besar berkat posisinya di garis khatulistiwa, dengan rata-rata radiasi harian mencapai <strong>4,80 kWh/m²</strong>. Pemerintah menargetkan bauran Energi Baru Terbarukan (EBT) dapat mencapai minimal 23% dalam sistem kelistrikan nasional. Salah satu teknologi yang paling cepat diadopsi oleh masyarakat dan sektor industri adalah Pembangkit Listrik Tenaga Surya (PLTS) atap (*rooftop solar*).
    </p>
    
    <div class="my-4 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-r-lg">
        <h4 class="font-bold text-blue-900 mb-2">Tabel 1: Perbandingan Karakteristik Teknologi Modul Panel Surya Komersial</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs md:text-sm text-left border-collapse bg-white shadow-sm rounded">
                <thead>
                    <tr class="bg-blue-100 text-blue-900 border-b">
                        <th class="p-2 border">Jenis Modul</th>
                        <th class="p-2 border">Efisiensi Rata-rata (%)</th>
                        <th class="p-2 border">Koefisien Suhu (%/°C)</th>
                        <th class="p-2 border">Daya Tahan Operasional</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="p-2 border font-medium">Monokristalin (Mono-Si)</td>
                        <td class="p-2 border">20% – 22%</td>
                        <td class="p-2 border">-0,35</td>
                        <td class="p-2 border">25 – 30 tahun</td>
                    </tr>
                    <tr class="border-b bg-slate-50">
                        <td class="p-2 border font-medium">Polikristalin (Poly-Si)</td>
                        <td class="p-2 border">15% – 17%</td>
                        <td class="p-2 border">-0,42</td>
                        <td class="p-2 border">20 – 25 tahun</td>
                    </tr>
                    <tr>
                        <td class="p-2 border font-medium">Perovskit Hibrida (R&D)</td>
                        <td class="p-2 border">25% – 28%</td>
                        <td class="p-2 border">-0,28</td>
                        <td class="p-2 border">5 – 10 tahun (degradasi cepat)</td>
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
    $stmtStimulus->execute([
        'STM-01',
        'Dilema Transisi Energi Terbarukan di Wilayah Tropis',
        $stimulus1_html,
        'Diadaptasi dari Pusmendik & Publikasi Kementerian ESDM RI'
    ]);
    $stimulus1Id = (int)$pdo->lastInsertId();

    // 4. Masukkan Stimulus 2: Numerasi & Efisiensi Energi Rumah Tangga
    $stimulus2_html = <<<HTML
<div class="space-y-4 text-slate-700 leading-relaxed text-sm md:text-base">
    <p class="font-semibold text-slate-900 text-base md:text-lg">
        Kalkulasi Mandiri: Pemasangan PLTS On-Grid Rumah Tangga
    </p>
    <p>
        Keluarga Budi memasang sistem PLTS atap tipe <em>On-Grid</em> berkapasitas <strong>2.000 Watt-peak (2 kWp)</strong> pada rumah tinggalnya dengan batas daya PLN 2.200 VA. Sistem ini tidak memakai baterai cadangan, melainkan langsung menyalurkan kelebihan daya ke jaringan PLN melalui meteran ekspor-impor (Net Metering).
    </p>

    <div class="my-3 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg text-sm">
        <h4 class="font-bold text-amber-900 mb-1">Parameter Teknis & Finansial:</h4>
        <ul class="list-disc pl-5 space-y-1 text-slate-700">
            <li><strong>Kapasitas Terpasang:</strong> 2 kWp (menggunakan modul Monokristalin).</li>
            <li><strong>Jam Penyinaran Efektif Harian:</strong> 4 jam/hari.</li>
            <li><strong>Faktor Kinerja Sistem (Performance Ratio / PR):</strong> 80% (akibat rugi kabel, suhu, inverter).</li>
            <li><strong>Tarif Dasar Listrik PLN:</strong> Rp1.444,70 per kWh.</li>
            <li><strong>Total Biaya Investasi Pemasangan Awal:</strong> Rp28.000.000,- (sudah termasuk inverter & instalasi).</li>
        </ul>
    </div>

    <p>
        Rumus produksi energi harian PLTS dihitung dengan:<br>
        <span class="inline-block bg-slate-100 p-2 font-mono text-sm rounded border border-slate-300 my-1">
            Produksi Listrik (kWh/hari) = Kapasitas Panel (kWp) × Jam Penyinaran Efektif × PR
        </span>
    </p>
</div>
HTML;
    $stmtStimulus->execute([
        'STM-02',
        'Kalkulasi Finansial & Teknis PLTS Rumah Tangga',
        $stimulus2_html,
        'Pusat Asesmen Pendidikan (Pusmendik) - Numerasi Matematika Terapan'
    ]);
    $stimulus2Id = (int)$pdo->lastInsertId();

    // 5. Masukkan Stimulus 3: Konservasi Sains Biorock
    $stimulus3_html = <<<HTML
<div class="space-y-4 text-slate-700 leading-relaxed text-sm md:text-base">
    <p class="font-semibold text-slate-900 text-base md:text-lg">
        Teknologi Biorock: Restorasi Ekosistem Terumbu Karang Berarus Listrik Rendah
    </p>
    <p>
        Kerusakan terumbu karang akibat pemanasan global dan penangkapan ikan destruktif di perairan Indonesia mendorong penerapan metode restorasi buatan. Salah satu inovasi paling efektif adalah <strong>Teknologi Biorock (Mineral Accretion Technology)</strong> yang dipelopori di perairan Pemuteran, Bali.
    </p>
    <p>
        Metode ini memanfaatkan arus listrik searah (DC) bertegangan sangat rendah (biasanya 1,2 hingga 4 Volt) yang dialirkan ke dalam kerangka baja di dasar laut. Arus listrik memicu reaksi elektrolisis air laut:
    </p>
    <div class="bg-emerald-50 border-l-4 border-emerald-600 p-3 rounded-r font-mono text-xs md:text-sm text-emerald-950">
        Katoda (Kerangka Baja): 2H₂O + 2e⁻ → H₂ (gas) + 2OH⁻<br>
        Pengendapan Mineral: Ca²⁺ + HCO₃⁻ + OH⁻ → CaCO₃ (Kalsium Karbonat padat) + H₂O
    </div>
    <p>
        Endapan mineral kalsium karbonat (batu kapur alami) yang terbentuk pada struktur baja menyerupai karang alami. Bibit karang yang ditransplantasikan ke kerangka ini terbukti tumbuh 3 hingga 5 kali lebih cepat serta memiliki daya tahan 50 kali lebih kuat terhadap fenomena pemutihan karang (*coral bleaching*).
    </p>
</div>
HTML;
    $stmtStimulus->execute([
        'STM-03',
        'Teknologi Biorock untuk Konservasi Terumbu Karang',
        $stimulus3_html,
        'Pusmendik - Literasi Sains Lingkungan'
    ]);
    $stimulus3Id = (int)$pdo->lastInsertId();

    // 6. Siapkan Bank Soal Multi-Format Khas Pusmendik
    $soalData = [
        // --- SOAL 1: Pilihan Ganda Tunggal (Literasi) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus1Id,
            'nomor_urut' => 1,
            'subtes' => 'Literasi Membaca',
            'tipe_soal' => 'pg_tunggal',
            'pertanyaan' => 'Berdasarkan Tabel 1 pada wacana, mengapa panel surya jenis Monokristalin lebih direkomendasikan untuk proyek jangka panjang di wilayah perkotaan beriklim tropis dibandingkan jenis Polikristalin?',
            'opsi_jawaban' => json_encode([
                ['key' => 'A', 'text' => 'Karena panel Monokristalin memiliki biaya manufaktur yang paling murah di pasaran.'],
                ['key' => 'B', 'text' => 'Karena efisiensi konversinya lebih tinggi dan koefisien suhunya lebih kecil sehingga penurunan daya akibat panas terik lebih minim.'],
                ['key' => 'C', 'text' => 'Karena dapat menyerap energi surya secara maksimal hanya ketika cuaca mendung dan tertutup awan tebal.'],
                ['key' => 'D', 'text' => 'Karena tidak membutuhkan perawatan berkala serta tidak terpengaruh oleh debu dan polusi udara.'],
                ['key' => 'E', 'text' => 'Karena memiliki masa operasional yang identik dengan panel Perovskit hibrida.']
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode('B'),
            'pembahasan' => 'Jawaban benar adalah B. Berdasarkan data Tabel 1, Monokristalin memiliki efisiensi tinggi (20-22%) dan koefisien suhu -0,35%/°C (lebih kecil daripada Polikristalin yang bernilai -0,42%/°C). Koefisien suhu yang lebih kecil berarti penurunan daya akibat paparan suhu tinggi di wilayah tropis relatif lebih sedikit, ditambah ketahanan operasionalnya paling panjang (25-30 tahun).',
            'bobot' => 1.0
        ],

        // --- SOAL 2: Pilihan Ganda Kompleks / Multi-Checkbox (Literasi) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus1Id,
            'nomor_urut' => 2,
            'subtes' => 'Literasi Membaca',
            'tipe_soal' => 'pg_kompleks',
            'pertanyaan' => 'Faktor apa sajakah yang menjadi tantangan teknis dalam pengoperasian pembangkit listrik tenaga surya (PLTS) di Indonesia berdasarkan teks wacana? (Pilih semua jawaban yang benar)',
            'opsi_jawaban' => json_encode([
                ['key' => 'A', 'text' => 'Kenaikan suhu permukaan panel yang dapat menurunkan efisiensi konversi daya.'],
                ['key' => 'B', 'text' => 'Ketiadaan potensi radiasi matahari harian di sebagian besar pulau Indonesia.'],
                ['key' => 'C', 'text' => 'Sifat intermittency (fluktuasi daya) akibat tutupan awan yang dinamis.'],
                ['key' => 'D', 'text' => 'Penumpukan debu dan polusi udara pada permukaan modul panel.'],
                ['key' => 'E', 'text' => 'Larangan pemerintah terkait pemanfaatan atap bangunan untuk energi surya.']
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode(['A', 'C', 'D']),
            'pembahasan' => 'Jawaban benar adalah A, C, dan D. Paragraf terakhir secara eksplisit menyebutkan kenaikan suhu ekstrem, fluktuasi akibat tutupan awan (intermittency), serta kotoran debu/polusi sebagai tantangan nyata di iklim tropis Indonesia.',
            'bobot' => 1.0
        ],

        // --- SOAL 3: Matriks Benar/Salah (Literasi Kritis) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus1Id,
            'nomor_urut' => 3,
            'subtes' => 'Literasi Membaca',
            'tipe_soal' => 'matriks_bs',
            'pertanyaan' => 'Tentukan kebenaran pernyataan-pernyataan berikut berdasarkan isi teks wacana dan data Tabel 1!',
            'opsi_jawaban' => json_encode([
                'kolom' => ['Benar', 'Salah'],
                'pernyataan' => [
                    ['id' => 'p1', 'teks' => 'Panel surya Perovskit Hibrida saat ini telah menjadi standar modul komersial paling awet dengan usia pakai lebih dari 30 tahun.'],
                    ['id' => 'p2', 'teks' => 'Rata-rata radiasi harian sinar matahari di wilayah Indonesia mencapai sekitar 4,80 kWh/m².'],
                    ['id' => 'p3', 'teks' => 'Kenaikan suhu lingkungan kerja panel surya sebesar 10°C di atas standar akan menyebabkan efisiensi panel Polikristalin turun sebesar 4,2%.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode([
                'p1' => 'Salah',
                'p2' => 'Benar',
                'p3' => 'Benar'
            ]),
            'pembahasan' => 'Pembahasan: 1. Salah (Perovskit masih tahap R&D dan mengalami degradasi cepat dalam 5-10 tahun). 2. Benar (disebutkan di paragraf pertama). 3. Benar (Koefisien suhu Polikristalin adalah -0,42%/°C, sehingga kenaikan 10°C menyebabkan penurunan 10 × 0,42% = 4,2%).',
            'bobot' => 1.0
        ],

        // --- SOAL 4: Menjodohkan (Literasi Sains) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus1Id,
            'nomor_urut' => 4,
            'subtes' => 'Literasi Sains',
            'tipe_soal' => 'menjodohkan',
            'pertanyaan' => 'Pasangkanlah istilah teknis berikut dengan definisi atau fungsi yang paling sesuai menurut wacana!',
            'opsi_jawaban' => json_encode([
                'premis' => [
                    ['id' => 'm1', 'teks' => 'BESS (Battery Energy Storage System)'],
                    ['id' => 'm2', 'teks' => 'Koefisien Suhu'],
                    ['id' => 'm3', 'teks' => 'Intermittency']
                ],
                'target' => [
                    ['id' => 't1', 'teks' => 'Tingkat fluktuasi pasokan daya listrik akibat kondisi cuaca dan tutupan awan yang berubah-ubah.'],
                    ['id' => 't2', 'teks' => 'Sistem penyimpan daya baterai untuk menjaga stabilitas tegangan jaringan listrik.'],
                    ['id' => 't3', 'teks' => 'Indikator persentase penurunan efisiensi panel untuk setiap kenaikan suhu 1°C.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode([
                'm1' => 't2',
                'm2' => 't3',
                'm3' => 't1'
            ]),
            'pembahasan' => 'Pasangan yang tepat: BESS berfungsi sebagai penyimpan daya baterai (t2); Koefisien Suhu mengukur penurunan efisiensi per 1°C kenaikan panas (t3); Intermittency menggambarkan ketidakpastian dan fluktuasi produksi energi surya akibat cuaca (t1).',
            'bobot' => 1.0
        ],

        // --- SOAL 5: Isian Singkat (Penalaran Numerasi) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus2Id,
            'nomor_urut' => 5,
            'subtes' => 'Penalaran Numerasi',
            'tipe_soal' => 'isian_singkat',
            'pertanyaan' => 'Berapa kilowatt-hour (kWh) estimasi total energi listrik yang berhasil diproduksi oleh PLTS Keluarga Budi dalam satu hari? (Tuliskan hanya dalam angka desimal dengan tanda koma atau titik, contoh: 5.2 atau 6.4)',
            'opsi_jawaban' => null,
            'kunci_jawaban' => json_encode(['6.4', '6,4']),
            'pembahasan' => 'Rumus dari wacana: Produksi Harian = Kapasitas Panel (kWp) × Jam Penyinaran Efektif × PR. Diketahui: Kapasitas = 2 kWp, Jam Efektif = 4 jam, PR = 80% (0,8). Maka: Produksi = 2 × 4 × 0,8 = 6,4 kWh per hari.',
            'bobot' => 1.0
        ],

        // --- SOAL 6: Pilihan Ganda Tunggal (Numerasi Finansial) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus2Id,
            'nomor_urut' => 6,
            'subtes' => 'Penalaran Numerasi',
            'tipe_soal' => 'pg_tunggal',
            'pertanyaan' => 'Jika diasumsikan 1 bulan terdiri dari 30 hari dan seluruh energi listrik yang diproduksi PLTS terserap untuk mengurangi tagihan PLN, berapakah estimasi nominal penghematan biaya listrik Keluarga Budi per bulan? (Tarif PLN: Rp1.444,70 / kWh)',
            'opsi_jawaban' => json_encode([
                ['key' => 'A', 'text' => 'Sekitar Rp185.000,-'],
                ['key' => 'B', 'text' => 'Sekitar Rp277.382,-'],
                ['key' => 'C', 'text' => 'Sekitar Rp360.500,-'],
                ['key' => 'D', 'text' => 'Sekitar Rp450.000,-'],
                ['key' => 'E', 'text' => 'Sekitar Rp525.200,-']
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode('B'),
            'pembahasan' => 'Langkah perhitungan: 1) Produksi listrik bulanan = 6,4 kWh/hari × 30 hari = 192 kWh/bulan. 2) Penghematan biaya per bulan = 192 kWh × Rp1.444,70 = Rp277.382,40 (pilihan B).',
            'bobot' => 1.0
        ],

        // --- SOAL 7: Pilihan Ganda Kompleks (Numerasi Finansial) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus2Id,
            'nomor_urut' => 7,
            'subtes' => 'Penalaran Numerasi',
            'tipe_soal' => 'pg_kompleks',
            'pertanyaan' => 'Berdasarkan kalkulasi investasi awal (Rp28.000.000,-) dan potensi penghematan bulanan Keluarga Budi, pilihlah kesimpulan matematis yang BENAR di bawah ini! (Pilih semua yang sesuai)',
            'opsi_jawaban' => json_encode([
                ['key' => 'A', 'text' => 'Total penghematan listrik per tahun yang diperoleh mencapai lebih dari Rp3.300.000,-.'],
                ['key' => 'B', 'text' => 'Waktu pengembalian modal (Break Even Point / BEP) sistem PLTS ini adalah sekitar 8,4 hingga 8,5 tahun.'],
                ['key' => 'C', 'text' => 'Jika kapasitas panel surya digandakan menjadi 4 kWp dengan parameter yang sama, waktu BEP akan menjadi dua kali lebih lama.'],
                ['key' => 'D', 'text' => 'Penurunan nilai Performance Ratio (PR) menjadi 70% akan memperpanjang waktu balik modal.']
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode(['A', 'B', 'D']),
            'pembahasan' => 'Penghematan per tahun = Rp277.382,40 × 12 = Rp3.328.588,80 (A benar). BEP = Rp28.000.000 / Rp3.328.588,80 = 8,41 tahun (B benar). Menggandakan kapasitas jika biaya investasi proporsional tidak membuat BEP dua kali lipat, melainkan relatif tetap (C salah). Penurunan PR menurunkan produksi dan memperpanjang BEP (D benar).',
            'bobot' => 1.0
        ],

        // --- SOAL 8: Matriks Sesuai / Tidak Sesuai (Literasi Sains Biorock) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus3Id,
            'nomor_urut' => 8,
            'subtes' => 'Literasi Sains',
            'tipe_soal' => 'matriks_bs',
            'pertanyaan' => 'Evaluasi pernyataan berikut berdasarkan mekanisme reaksi kimia pada teknologi Biorock!',
            'opsi_jawaban' => json_encode([
                'kolom' => ['Sesuai', 'Tidak Sesuai'],
                'pernyataan' => [
                    ['id' => 'p1', 'teks' => 'Gas hidrogen (H₂) dihasilkan pada katoda sebagai salah satu produk reaksi elektrolisis air.'],
                    ['id' => 'p2', 'teks' => 'Teknologi Biorock memerlukan arus listrik bolak-balik (AC) bertegangan tinggi di atas 220 Volt agar efektif.'],
                    ['id' => 'p3', 'teks' => 'Mineral yang terakumulasi pada kerangka baja adalah kalsium karbonat (CaCO₃) yang menyusun kerangka alami terumbu karang.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode([
                'p1' => 'Sesuai',
                'p2' => 'Tidak Sesuai',
                'p3' => 'Sesuai'
            ]),
            'pembahasan' => 'Pembahasan: Pernyataan 1 Sesuai (reaksi katoda menghasilkan gas H2). Pernyataan 2 Tidak Sesuai (Biorock menggunakan listrik searah/DC dengan voltase sangat rendah 1,2 - 4 Volt demi keselamatan biota laut). Pernyataan 3 Sesuai (endapan kalsium karbonat menyerupai karang alami).',
            'bobot' => 1.0
        ],

        // --- SOAL 9: Menjodohkan (Sains Ekologi) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus3Id,
            'nomor_urut' => 9,
            'subtes' => 'Literasi Sains',
            'tipe_soal' => 'menjodohkan',
            'pertanyaan' => 'Jodohkanlah fenomena lingkungan atau mekanisme biologis berikut dengan dampak nyata dari teknologi Biorock!',
            'opsi_jawaban' => json_encode([
                'premis' => [
                    ['id' => 'b1', 'teks' => 'Laju Pertumbuhan Karang'],
                    ['id' => 'b2', 'teks' => 'Daya Tahan Pemutihan (Bleaching)'],
                    ['id' => 'b3', 'teks' => 'Kerangka Baja Terpasang']
                ],
                'target' => [
                    ['id' => 'd1', 'teks' => 'Meningkat hingga 50 kali lebih kuat dibanding karang pada terumbu alami non-listrik.'],
                    ['id' => 'd2', 'teks' => 'Bertindak sebagai katoda tempat mengendapnya mineral batu kapur pelindung.'],
                    ['id' => 'd3', 'teks' => 'Mengalami akselerasi 3 hingga 5 kali lebih cepat daripada kondisi konvensional.']
                ]
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode([
                'b1' => 'd3',
                'b2' => 'd1',
                'b3' => 'd2'
            ]),
            'pembahasan' => 'Berdasarkan wacana: Laju pertumbuhan karang meningkat 3-5x (d3); Resistansi pemutihan karang naik 50x (d1); Kerangka baja berfungsi sebagai katoda pengendapan CaCO3 (d2).',
            'bobot' => 1.0
        ],

        // --- SOAL 10: Pilihan Ganda Tunggal (Penalaran Kritis) ---
        [
            'paket_id' => $paketId,
            'stimulus_id' => $stimulus3Id,
            'nomor_urut' => 10,
            'subtes' => 'Penalaran Kritis',
            'tipe_soal' => 'pg_tunggal',
            'pertanyaan' => 'Jika sumber listrik teknologi Biorock di suatu pulau konservasi terpencil diintegrasikan dengan sistem Pembangkit Listrik Tenaga Surya (PLTS), keuntungan ekologis sinergis paling signifikan yang diperoleh adalah ...',
            'opsi_jawaban' => json_encode([
                ['key' => 'A', 'text' => 'Mengurangi kebutuhan air laut sehingga kadar garam perairan sekitar menjadi lebih stabil.'],
                ['key' => 'B', 'text' => 'Mewujudkan restorasi terumbu karang berkelanjutan tanpa menghasilkan jejak emisi karbon dari pembakaran bahan bakar genset fosil.'],
                ['key' => 'C', 'text' => 'Meningkatkan tegangan listrik hingga ratusan volt untuk mempercepat pengendapan mineral karang secara instan.'],
                ['key' => 'D', 'text' => 'Menghilangkan kebutuhan bibit karang karena listrik otomatis menciptakan organisme karang baru dari arus laut.'],
                ['key' => 'E', 'text' => 'Mencegah terjadinya pasang surut air laut di kawasan pulau terluar.']
            ], JSON_UNESCAPED_UNICODE),
            'kunci_jawaban' => json_encode('B'),
            'pembahasan' => 'Jawaban benar adalah B. Integrasi PLTS dengan Biorock menghasilkan solusi ramah lingkungan total: proses restorasi karang dialiri listrik dari energi terbarukan tanpa membutuhkan genset diesel (bahan bakar fosil), sehingga tidak menimbulkan polusi emisi gas rumah kaca di kawasan konservasi bahari.',
            'bobot' => 1.0
        ]
    ];

    $stmtSoal = $pdo->prepare("INSERT INTO soal (paket_id, stimulus_id, nomor_urut, subtes, tipe_soal, pertanyaan, opsi_jawaban, kunci_jawaban, pembahasan, bobot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($soalData as $s) {
        $stmtSoal->execute([
            $s['paket_id'],
            $s['stimulus_id'],
            $s['nomor_urut'],
            $s['subtes'],
            $s['tipe_soal'],
            $s['pertanyaan'],
            $s['opsi_jawaban'],
            $s['kunci_jawaban'],
            $s['pembahasan'],
            $s['bobot']
        ]);
    }
}
