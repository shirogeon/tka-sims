<?php
// ujian.php - Antarmuka CBT Standar Pusmendik ANBK dengan Kiosk Mode
require_once __DIR__ . '/config/database.php';
$pdo = getDatabaseConnection();

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    header("Location: index.php");
    exit;
}

// Cek sesi ujian
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

if ($sesi['status'] === 'selesai') {
    header("Location: hasil.php?token=" . urlencode($token));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT PUSMENDIK - <?= htmlspecialchars($sesi['judul_paket']) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 font-size-md flex flex-col min-h-screen overflow-hidden select-none">

    <!-- Top Header Bar Khas ANBK Pusmendik (65px) -->
    <header class="h-[65px] bg-[#152e55] text-white flex items-center justify-between px-4 md:px-6 shadow-md z-30">
        <!-- Logo & Identitas Peserta -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-900 font-black text-xl shadow-xs">
                🏛️
            </div>
            <div class="leading-tight">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm md:text-base tracking-tight text-white uppercase">CBT PUSMENDIK</span>
                    <span class="hidden lg:inline-block bg-blue-950 text-blue-200 text-xs px-2.5 py-0.5 rounded font-mono font-bold border border-blue-800">
                        <?= htmlspecialchars($sesi['nomor_peserta']) ?>
                    </span>
                </div>
                <div class="text-xs text-blue-200 font-medium truncate max-w-[220px] md:max-w-md">
                    <?= htmlspecialchars($sesi['nama_lengkap']) ?> &bull; <span class="text-slate-300"><?= htmlspecialchars($sesi['sekolah_asal']) ?></span>
                </div>
            </div>
        </div>

        <!-- Controls Tengah: Ukuran Font & Fullscreen Toggle -->
        <div class="hidden sm:flex items-center gap-3">
            <!-- Save Toast Indicator -->
            <div id="saveIndicator" class="text-xs text-emerald-400 font-semibold px-2 py-1 bg-blue-950/60 rounded border border-blue-900">
                Tersimpan ✓
            </div>

            <!-- Font Size -->
            <div class="flex items-center bg-blue-950/80 p-1 rounded-lg border border-blue-900 text-xs">
                <span class="text-slate-300 px-2 font-medium">Font:</span>
                <button type="button" data-size="sm" class="btn-font-size px-2 py-0.5 rounded hover:bg-blue-800 text-blue-200">A-</button>
                <button type="button" data-size="md" class="btn-font-size px-2 py-0.5 rounded bg-blue-500 text-white font-bold">A</button>
                <button type="button" data-size="lg" class="btn-font-size px-2 py-0.5 rounded hover:bg-blue-800 text-blue-200">A+</button>
            </div>

            <!-- Tombol Layar Penuh -->
            <button id="btnToggleFullscreen" type="button" class="bg-blue-950/80 hover:bg-blue-900 border border-blue-900 text-blue-200 hover:text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                </svg>
                <span>Layar Penuh</span>
            </button>
        </div>

        <!-- Kanan: Sisa Waktu & Tombol Daftar Soal Khas ANBK -->
        <div class="flex items-center gap-3">
            <!-- Digital Timer -->
            <div class="bg-[#0b1b36] px-3.5 py-1.5 rounded-lg border border-blue-900 flex items-center gap-2 shadow-inner">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span id="timerDisplay" class="font-mono font-black text-sm md:text-base tracking-wider text-amber-400">
                    --:--:--
                </span>
            </div>

            <!-- Tombol Daftar Soal -->
            <button id="btnOpenPalette" type="button" class="bg-[#1e40af] hover:bg-[#1d4ed8] active:bg-[#1e3a8a] text-white text-xs md:text-sm font-bold px-3.5 py-2 rounded-lg flex items-center gap-2 shadow transition border border-blue-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                <span>Daftar Soal</span>
            </button>
        </div>
    </header>

    <!-- Main Split-Screen Workspace (100vh - 130px) -->
    <main class="split-container flex-1 relative">
        
        <!-- PANE KIRI: WACANA / STIMULUS -->
        <section id="paneStimulus" class="pane-stimulus flex flex-col">
            <!-- Header Wacana -->
            <div class="bg-slate-100 border-b border-slate-200 px-5 py-2.5 flex items-center justify-between sticky top-0 z-10">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                    <span>📄</span> INFORMASI WACANA / STIMULUS
                </span>
                <span id="stimulusTitle" class="text-xs font-semibold text-slate-700 truncate max-w-xs md:max-w-md">
                    Memuat...
                </span>
            </div>

            <!-- Konten Wacana -->
            <div id="stimulusContent" class="p-6 md:p-8 overflow-y-auto flex-1 cbt-text">
                <div class="text-slate-400 italic text-center py-12">Memuat wacana stimulus...</div>
            </div>

            <!-- Footer Sumber -->
            <div id="stimulusSource" class="bg-slate-50 border-t border-slate-200 px-6 py-2.5 text-xs text-slate-600 italic">
                Sumber: Pusat Asesmen Pendidikan
            </div>
        </section>

        <!-- PANE KANAN: LEMBAR SOAL & OPSI -->
        <section id="paneSoal" class="pane-soal flex flex-col">
            <!-- Header Lembar Soal -->
            <div class="bg-slate-100 border-b border-slate-200 px-5 py-2.5 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-2">
                    <span id="questionNumberBadge" class="bg-[#152e55] text-white text-xs font-extrabold px-3 py-1 rounded shadow-xs uppercase tracking-wide">
                        SOAL NO. --
                    </span>
                    <span id="subtesBadge" class="bg-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded">
                        Subtes
                    </span>
                </div>
                <span id="tipeSoalBadge" class="text-xs font-bold text-blue-800 bg-blue-50 px-2.5 py-1 rounded border border-blue-200">
                    Pilihan Ganda
                </span>
            </div>

            <!-- Pertanyaan & Opsi -->
            <div class="p-6 md:p-8 overflow-y-auto flex-1 space-y-6">
                <!-- Pertanyaan -->
                <div id="questionText" class="cbt-text font-medium text-slate-900 leading-relaxed">
                    Memuat butir pertanyaan...
                </div>

                <!-- Opsi Jawaban Dinamis -->
                <div id="optionsContainer" class="space-y-3 pt-2">
                    <!-- Diisi dinamis oleh JavaScript -->
                </div>
            </div>
        </section>

        <!-- Loader Indikator -->
        <div id="cbtLoader" class="absolute inset-0 bg-white/70 backdrop-blur-xs flex items-center justify-center z-20 hidden">
            <div class="flex items-center gap-3 bg-white px-5 py-3 rounded-lg shadow-xl border border-slate-200">
                <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-bold text-slate-700">Memuat Butir Soal...</span>
            </div>
        </div>

    </main>

    <!-- Bottom Footer Navigation Khas ANBK (65px) -->
    <footer class="h-[65px] bg-slate-50 border-t border-slate-300 flex items-center justify-between px-4 md:px-8 shadow-md z-30">
        <!-- Tombol Sebelumnya -->
        <button id="btnPrev" type="button" class="bg-slate-600 hover:bg-slate-700 active:bg-slate-800 text-white text-xs md:text-sm font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 transition disabled:opacity-30 disabled:cursor-not-allowed shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>SOAL SEBELUMNYA</span>
        </button>

        <!-- Tombol Ragu-Ragu (Khas ANBK Kuning Emas) -->
        <label for="chkRagu" class="cursor-pointer flex items-center gap-2.5 bg-amber-400 hover:bg-amber-300 active:bg-amber-500 border-2 border-amber-500 px-5 py-2 rounded-lg transition shadow-xs">
            <input type="checkbox" id="chkRagu" class="w-4 h-4 text-amber-900 rounded focus:ring-amber-500 border-amber-600">
            <span class="text-xs md:text-sm font-black text-amber-950 uppercase tracking-wide">RAGU - RAGU</span>
        </label>

        <!-- Tombol Berikutnya / Selesai -->
        <button id="btnNext" type="button" class="bg-[#1e40af] hover:bg-[#1d4ed8] active:bg-[#1e3a8a] text-white text-xs md:text-sm font-bold px-6 py-2.5 rounded-lg flex items-center gap-2 transition shadow-xs">
            <span>SOAL BERIKUTNYA</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </footer>

    <!-- POPUP MODAL DAFTAR SOAL KHAS ANBK (BUKAN SIDEBAR BESAR!) -->
    <div id="paletteModal" class="anbk-modal-backdrop hidden">
        <div class="anbk-palette-card">
            <!-- Header Modal -->
            <div class="px-5 py-3.5 bg-[#152e55] text-white flex items-center justify-between">
                <div class="font-extrabold text-sm tracking-wider uppercase flex items-center gap-2">
                    <span>📋</span> DAFTAR SOAL UJIAN
                </div>
                <button id="btnClosePalette" type="button" class="text-slate-300 hover:text-white text-2xl font-bold leading-none p-1">
                    &times;
                </button>
            </div>

            <!-- Grid Nomor Soal ANBK (5 Kolom Rapi) -->
            <div id="paletteGrid" class="anbk-palette-grid">
                <!-- Diisi oleh JavaScript -->
            </div>

            <!-- Legenda Warna Khas Pusmendik -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-around text-xs font-semibold text-slate-700">
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded bg-white border-2 border-slate-300 inline-block"></span>
                    <span>Belum Dijawab</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded bg-slate-900 border-2 border-slate-900 inline-block"></span>
                    <span>Sudah Dijawab</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded bg-amber-500 border-2 border-amber-600 inline-block"></span>
                    <span>Ragu-Ragu</span>
                </div>
            </div>

            <!-- Footer Selesai -->
            <div class="p-4 bg-white border-t border-slate-200">
                <button onclick="window.cbtApp.showFinishModal()" type="button" class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black py-2.5 px-4 rounded-lg text-sm shadow transition">
                    Selesaikan Ujian Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI SELESAI UJIAN -->
    <div id="finishModal" class="fixed inset-0 bg-slate-900/70 z-50 flex items-center justify-center p-4 hidden backdrop-blur-xs">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-300">
            <div class="bg-[#152e55] px-6 py-4 text-white">
                <h3 class="font-bold text-lg">Konfirmasi Selesai Ujian</h3>
                <p class="text-xs text-blue-200">Periksa kembali ringkasan lembar jawaban Anda.</p>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-slate-900 text-white rounded-lg">
                        <div id="statAnswered" class="text-2xl font-black">0</div>
                        <div class="text-xs text-slate-300 mt-1">Sudah Dijawab</div>
                    </div>
                    <div class="p-3 bg-amber-500 text-amber-950 rounded-lg">
                        <div id="statRagu" class="text-2xl font-black">0</div>
                        <div class="text-xs font-bold mt-1">Ragu-ragu</div>
                    </div>
                    <div class="p-3 bg-slate-100 border border-slate-300 rounded-lg">
                        <div id="statEmpty" class="text-2xl font-black text-slate-700">0</div>
                        <div class="text-xs text-slate-500 font-bold mt-1">Belum Diisi</div>
                    </div>
                </div>

                <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200 leading-relaxed">
                    Setelah menekan <strong>Kumpulkan Ujian</strong>, sesi ujian ini akan langsung dikunci dan hasil nilai beserta pembahasan soal akan langsung ditampilkan.
                </p>
            </div>

            <div class="bg-slate-100 px-6 py-3.5 flex justify-end gap-3 border-t border-slate-200">
                <button id="btnCancelFinish" type="button" class="px-4 py-2 rounded-lg text-slate-700 font-bold text-sm hover:bg-slate-200 transition">
                    Kembali Memeriksa
                </button>
                <button id="btnConfirmFinish" type="button" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm shadow transition">
                    Ya, Kumpulkan Ujian
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL PERINGATAN KELUAR FULLSCREEN / KIOSK LOCKDOWN -->
    <div id="fullscreenLockOverlay" class="fullscreen-lock-overlay hidden">
        <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center text-3xl mb-4 animate-pulse shadow-lg">
            ⚠️
        </div>
        <h2 class="text-xl md:text-2xl font-black text-red-500 uppercase tracking-wider mb-2">Peringatan Keamanan CBT!</h2>
        <p class="text-slate-300 max-w-md text-sm mb-3 leading-relaxed">
            Anda terdeteksi keluar dari Mode Layar Penuh (Fullscreen) atau berpindah aplikasi/tab. Seluruh tindakan ini terekam dalam log pengawas ujian.
        </p>
        <div class="bg-slate-900 border border-red-500/50 px-5 py-2.5 rounded-lg text-xs font-mono text-amber-400 mb-6">
            Jumlah Pelanggaran: <span id="violationCount" class="font-extrabold text-red-400 text-base">0</span> kali
        </div>
        <button id="btnReenterFullscreen" type="button" class="bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-extrabold py-3 px-8 rounded-lg shadow-xl text-sm transition tracking-wide">
            KEMBALI KE LAYAR PENUH & LANJUTKAN UJIAN
        </button>
    </div>

    <!-- MODAL PROMPT AWAL MASUK FULLSCREEN -->
    <div id="startFullscreenPrompt" class="fixed inset-0 bg-slate-950/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center border border-slate-300">
            <div class="w-14 h-14 bg-blue-100 text-blue-900 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                🖥️
            </div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-2">Mode Ujian Kiosk Layar Penuh</h3>
            <p class="text-xs md:text-sm text-slate-600 mb-6 leading-relaxed">
                Untuk menjaga integritas dan ketertiban asesmen berstandar Pusmendik, ujian harus dijalankan dalam mode layar penuh (Full Screen). Tombol Back browser dan klik kanan akan dinonaktifkan.
            </p>
            <button id="btnStartExamFullscreen" type="button" class="w-full bg-[#1e40af] hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg text-sm shadow transition">
                Masuk Layar Penuh & Mulai Ujian
            </button>
        </div>
    </div>

    <!-- CBT Engine Script -->
    <script src="assets/js/cbt-engine.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.cbtApp = new CBTEngine({
                token: '<?= htmlspecialchars($token) ?>'
            });
        });
    </script>
</body>
</html>
