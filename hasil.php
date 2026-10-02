<?php
// hasil.php - Halaman Analisis Hasil Skor & Pembahasan Lengkap Standar Pusmendik
require_once __DIR__ . '/config/database.php';
$pdo = getDatabaseConnection();

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    header("Location: index.php");
    exit;
}

// Ambil sesi ujian dan data peserta
$stmtSesi = $pdo->prepare("
    SELECT s.*, p.nomor_peserta, p.nama_lengkap, p.sekolah_asal, pk.judul AS judul_paket, pk.durasi_menit
    FROM sesi_ujian s
    JOIN peserta p ON s.peserta_id = p.id
    JOIN paket_ujian pk ON s.paket_id = pk.id
    WHERE s.token_sesi = ?
");
$stmtSesi->execute([$token]);
$sesi = $stmtSesi->fetch();

if (!$sesi) {
    header("Location: index.php");
    exit;
}

$jawabanTersimpan = json_decode($sesi['jawaban_tersimpan'] ?? '{}', true) ?: [];
$paketId = (int)$sesi['paket_id'];

// Ambil semua soal beserta stimulus
$stmtSoal = $pdo->prepare("
    SELECT s.*, st.judul AS stimulus_judul, st.konten_html AS stimulus_konten, st.sumber AS stimulus_sumber
    FROM soal s
    LEFT JOIN stimulus st ON s.stimulus_id = st.id
    WHERE s.paket_id = ?
    ORDER BY s.nomor_urut ASC
");
$stmtSoal->execute([$paketId]);
$soalList = $stmtSoal->fetchAll();

// Evaluasi Rinci Tiap Nomor
$totalSoal = count($soalList);
$countBenar = 0;
$countSalah = 0;
$countKosong = 0;

$evaluasiList = [];

foreach ($soalList as $soal) {
    $no = (int)$soal['nomor_urut'];
    $tipe = $soal['tipe_soal'];
    $kunci = json_decode($soal['kunci_jawaban'], true);
    $userObj = $jawabanTersimpan[$no] ?? null;
    $userAns = $userObj['jawaban'] ?? null;

    $status = 'kosong'; // 'benar', 'sebagian', 'salah', 'kosong'
    $skorSoal = 0.0;
    $bobot = (float)($soal['bobot'] ?: 1.0);

    if ($userAns === null || $userAns === '' || (is_array($userAns) && count($userAns) === 0)) {
        $status = 'kosong';
        $countKosong++;
    } else {
        switch ($tipe) {
            case 'pg_tunggal':
                if (strtoupper((string)$userAns) === strtoupper((string)$kunci)) {
                    $status = 'benar';
                    $countBenar++;
                    $skorSoal = $bobot;
                } else {
                    $status = 'salah';
                    $countSalah++;
                }
                break;

            case 'pg_kompleks':
                if (is_array($userAns) && is_array($kunci)) {
                    $cleanUser = array_map('strtoupper', $userAns);
                    $cleanKunci = array_map('strtoupper', $kunci);
                    sort($cleanUser);
                    sort($cleanKunci);
                    if ($cleanUser === $cleanKunci) {
                        $status = 'benar';
                        $countBenar++;
                        $skorSoal = $bobot;
                    } else {
                        $intersect = count(array_intersect($cleanUser, $cleanKunci));
                        if ($intersect > 0) {
                            $status = 'sebagian';
                            $skorSoal = round($bobot * ($intersect / count($cleanKunci)), 2);
                        } else {
                            $status = 'salah';
                        }
                        $countSalah++;
                    }
                }
                break;

            case 'matriks_bs':
                if (is_array($userAns) && is_array($kunci)) {
                    $matchCount = 0;
                    $totalRows = count($kunci);
                    foreach ($kunci as $rowId => $expectedVal) {
                        if (isset($userAns[$rowId]) && strcasecmp((string)$userAns[$rowId], (string)$expectedVal) === 0) {
                            $matchCount++;
                        }
                    }
                    if ($matchCount === $totalRows) {
                        $status = 'benar';
                        $countBenar++;
                        $skorSoal = $bobot;
                    } elseif ($matchCount > 0) {
                        $status = 'sebagian';
                        $countSalah++;
                        $skorSoal = round($bobot * ($matchCount / $totalRows), 2);
                    } else {
                        $status = 'salah';
                        $countSalah++;
                    }
                }
                break;

            case 'menjodohkan':
                if (is_array($userAns) && is_array($kunci)) {
                    $matchCount = 0;
                    $totalPairs = count($kunci);
                    foreach ($kunci as $premisId => $targetExpected) {
                        if (isset($userAns[$premisId]) && $userAns[$premisId] === $targetExpected) {
                            $matchCount++;
                        }
                    }
                    if ($matchCount === $totalPairs) {
                        $status = 'benar';
                        $countBenar++;
                        $skorSoal = $bobot;
                    } elseif ($matchCount > 0) {
                        $status = 'sebagian';
                        $countSalah++;
                        $skorSoal = round($bobot * ($matchCount / $totalPairs), 2);
                    } else {
                        $status = 'salah';
                        $countSalah++;
                    }
                }
                break;

            case 'isian_singkat':
                $userClean = trim(str_replace(',', '.', (string)$userAns));
                $isMatch = false;
                if (is_array($kunci)) {
                    foreach ($kunci as $k) {
                        if (strcasecmp($userClean, trim(str_replace(',', '.', (string)$k))) === 0) {
                            $isMatch = true;
                            break;
                        }
                    }
                } else {
                    $isMatch = (strcasecmp($userClean, trim(str_replace(',', '.', (string)$kunci))) === 0);
                }

                if ($isMatch) {
                    $status = 'benar';
                    $countBenar++;
                    $skorSoal = $bobot;
                } else {
                    $status = 'salah';
                    $countSalah++;
                }
                break;
        }
    }

    $evaluasiList[] = [
        'soal' => $soal,
        'opsi' => json_decode($soal['opsi_jawaban'] ?? 'null', true),
        'kunci' => $kunci,
        'user_ans' => $userAns,
        'status' => $status,
        'skor' => $skorSoal
    ];
}

// Durasi Pengerjaan
$durasiDetik = 0;
if ($sesi['waktu_submit']) {
    $durasiDetik = max(0, strtotime($sesi['waktu_submit']) - strtotime($sesi['waktu_mulai']));
}
$durasiMenit = floor($durasiDetik / 60);
$durasiSisaDetik = $durasiDetik % 60;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian & Pembahasan - CBT Pusmendik</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">

    <!-- Header Instansi Pusmendik -->
    <header class="bg-blue-900 text-white shadow-md print:hidden">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-900 font-extrabold text-xl shadow">
                    🎓
                </div>
                <div>
                    <h1 class="text-base md:text-lg font-bold tracking-tight">HASIL & PEMBAHASAN RESMI</h1>
                    <p class="text-xs text-blue-200">Pusat Asesmen Pendidikan - Simulasi CBT TKA</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="bg-blue-800 hover:bg-blue-700 text-xs px-3 py-1.5 rounded font-semibold text-blue-100 flex items-center gap-1.5 border border-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Rapor
                </button>
                <a href="index.php" class="bg-emerald-600 hover:bg-emerald-700 text-xs px-3 py-1.5 rounded font-bold text-white shadow">
                    Ujian Baru
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-5xl mx-auto px-4 py-8 space-y-8">

        <!-- KARTU RINGKASAN HASIL & SKOR -->
        <section class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-900 to-indigo-900 px-6 py-4 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight"><?= htmlspecialchars($sesi['judul_paket']) ?></h2>
                    <p class="text-xs md:text-sm text-blue-200 mt-0.5">
                        Peserta: <strong class="text-white"><?= htmlspecialchars($sesi['nama_lengkap']) ?></strong> (<?= htmlspecialchars($sesi['nomor_peserta']) ?>) &bull; <?= htmlspecialchars($sesi['sekolah_asal']) ?>
                    </p>
                </div>
                <div class="bg-blue-950/80 px-4 py-2 rounded-lg border border-blue-700 text-center">
                    <span class="text-xs text-blue-300 block">Waktu Selesai:</span>
                    <span class="text-xs font-mono font-bold text-white"><?= date('d/m/Y H:i', strtotime($sesi['waktu_submit'] ?: 'now')) ?></span>
                </div>
            </div>

            <!-- Grid Metrik Nilai -->
            <div class="p-6 md:p-8 grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50/50">
                <!-- Skor Skala 100 -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs text-center flex flex-col justify-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai Akhir (0-100)</span>
                    <span class="text-4xl md:text-5xl font-black text-blue-700 mt-1"><?= $sesi['skor_akhir'] ?></span>
                    <span class="text-xs text-slate-500 mt-1 font-medium">Skala Standar Pusmendik</span>
                </div>

                <!-- Benar -->
                <div class="bg-white p-4 rounded-xl border border-emerald-200 shadow-xs text-center flex flex-col justify-center">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Jawaban Benar</span>
                    <span class="text-3xl md:text-4xl font-extrabold text-emerald-600 mt-1"><?= $countBenar ?> <span class="text-base text-slate-500 font-normal">/ <?= $totalSoal ?></span></span>
                    <span class="text-xs text-emerald-600 mt-1 font-medium">Tepat Sesuai Kunci</span>
                </div>

                <!-- Salah / Sebagian -->
                <div class="bg-white p-4 rounded-xl border border-rose-200 shadow-xs text-center flex flex-col justify-center">
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Kurang Tepat</span>
                    <span class="text-3xl md:text-4xl font-extrabold text-rose-600 mt-1"><?= $countSalah ?></span>
                    <span class="text-xs text-rose-500 mt-1 font-medium">Salah atau Sebagian</span>
                </div>

                <!-- Durasi Pengerjaan -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs text-center flex flex-col justify-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Durasi Selesai</span>
                    <span class="text-2xl md:text-3xl font-extrabold text-slate-700 mt-1"><?= $durasiMenit ?>m <?= $durasiSisaDetik ?>s</span>
                    <span class="text-xs text-slate-500 mt-1 font-medium">Dari alokasi <?= (int)$sesi['durasi_menit'] ?> menit</span>
                </div>
            </div>
        </section>

        <!-- DAFTAR PEMBAHASAN LENGKAP BUTIR SOAL -->
        <section class="space-y-6">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg md:text-xl font-bold text-slate-900 flex items-center gap-2">
                    <span>📖</span> Pembahasan Mendalam Per Butir Soal
                </h3>
                <span class="text-xs font-medium text-slate-500">10 Soal Standar Pusmendik</span>
            </div>

            <?php foreach ($evaluasiList as $idx => $item): 
                $soal = $item['soal'];
                $status = $item['status'];
                $badgeColor = match($status) {
                    'benar' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'sebagian' => 'bg-amber-100 text-amber-800 border-amber-300',
                    'salah' => 'bg-rose-100 text-rose-800 border-rose-300',
                    default => 'bg-slate-100 text-slate-700 border-slate-300'
                };
                $badgeText = match($status) {
                    'benar' => '✓ Jawaban Tepat',
                    'sebagian' => '⚠️ Sebagian Tepat',
                    'salah' => '✕ Salah',
                    default => '⚪ Tidak Dijawab'
                };
            ?>
            <article class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Header Soal -->
                <div class="bg-slate-50 px-6 py-3.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <span class="font-extrabold text-sm text-slate-900 bg-white border border-slate-300 px-3 py-1 rounded-md shadow-xs">
                            Soal No. <?= $soal['nomor_urut'] ?>
                        </span>
                        <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200">
                            <?= htmlspecialchars($soal['subtes']) ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-3 py-1 rounded-full border <?= $badgeColor ?>">
                            <?= $badgeText ?> (Skor: <?= $item['skor'] ?>)
                        </span>
                    </div>
                </div>

                <!-- Konten Soal & Stimulus -->
                <div class="p-6 space-y-4">
                    <!-- Jika ada stimulus bacaan -->
                    <?php if (!empty($soal['stimulus_konten'])): ?>
                    <details class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs md:text-sm text-slate-700">
                        <summary class="font-bold text-blue-800 cursor-pointer select-none">
                            Lihat Wacana Stimulus (<?= htmlspecialchars($soal['stimulus_judul']) ?>)
                        </summary>
                        <div class="mt-3 pt-3 border-t border-slate-200 leading-relaxed max-h-60 overflow-y-auto">
                            <?= $soal['stimulus_konten'] ?>
                        </div>
                    </details>
                    <?php endif; ?>

                    <!-- Teks Pertanyaan -->
                    <div class="font-medium text-slate-900 leading-relaxed text-sm md:text-base">
                        <?= $soal['pertanyaan'] ?>
                    </div>

                    <!-- Perbandingan Jawaban Peserta vs Kunci -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 text-xs md:text-sm">
                        <!-- Jawaban Anda -->
                        <div class="p-3.5 rounded-lg border <?= ($status === 'benar') ? 'bg-emerald-50/70 border-emerald-200 text-emerald-950' : 'bg-rose-50/70 border-rose-200 text-rose-950' ?>">
                            <span class="text-xs font-bold uppercase tracking-wider block mb-1 opacity-75">Jawaban Anda:</span>
                            <div class="font-mono font-semibold">
                                <?php 
                                if ($item['user_ans'] === null || $item['user_ans'] === '') {
                                    echo '<span class="italic font-sans text-slate-500 font-normal">Tidak dijawab (Kosong)</span>';
                                } elseif (is_array($item['user_ans'])) {
                                    echo htmlspecialchars(json_encode($item['user_ans'], JSON_UNESCAPED_UNICODE));
                                } else {
                                    echo htmlspecialchars((string)$item['user_ans']);
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Kunci Jawaban Resmi -->
                        <div class="p-3.5 rounded-lg border bg-blue-50/70 border-blue-200 text-blue-950">
                            <span class="text-xs font-bold uppercase tracking-wider block mb-1 opacity-75">Kunci Jawaban Resmi:</span>
                            <div class="font-mono font-semibold">
                                <?php 
                                if (is_array($item['kunci'])) {
                                    echo htmlspecialchars(json_encode($item['kunci'], JSON_UNESCAPED_UNICODE));
                                } else {
                                    echo htmlspecialchars((string)$item['kunci']);
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Kotak Pembahasan Komprehensif -->
                    <div class="bg-amber-50/80 border-l-4 border-amber-500 p-4 rounded-r-lg mt-3">
                        <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <span>💡</span> Ulasan Pembahasan:
                        </h4>
                        <p class="text-xs md:text-sm text-slate-700 leading-relaxed font-normal">
                            <?= nl2br(htmlspecialchars($soal['pembahasan'])) ?>
                        </p>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </section>

        <!-- Navigasi Bawah -->
        <div class="text-center pt-6 pb-12 print:hidden">
            <a href="index.php" class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-xl shadow-md transition text-sm">
                <span>Mulai Simulasi Ulang dengan Sesi Baru</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </a>
        </div>
    </main>

</body>
</html>
