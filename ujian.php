<?php
// ujian.php - Halaman Antarmuka Utama CBT Standar Pusmendik
require_once __DIR__ . '/config/database.php';
$pdo = getDatabaseConnection();

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    header("Location: index.php");
    exit;
}

// Cek sesi ujian
$stmtSesi = $pdo->prepare("
    SELECT s.*, p.nomor_peserta, p.nama_lengkap, pk.judul AS judul_paket, pk.durasi_menit
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

// Jika ujian sudah diselesaikan, arahkan langsung ke halaman hasil
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
    <title>CBT Pusmendik - <?= htmlspecialchars($sesi['judul_paket']) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 font-size-md flex flex-col min-h-screen overflow-hidden">

    <!-- Top Header Bar Khas Pusmendik (65px) -->
    <header class="h-[65px] bg-blue-900 text-white flex items-center justify-between px-4 md:px-6 shadow-md z-30 select-none">
        <!-- Logo & Info Peserta -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center text-blue-900 font-extrabold text-lg shadow">
                🎓
            </div>
            <div class="leading-tight">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm md:text-base tracking-tight">CBT PUSMENDIK</span>
                    <span class="hidden md:inline-block bg-blue-800 text-blue-200 text-xs px-2 py-0.5 rounded font-mono">
                        <?= htmlspecialchars($sesi['nomor_peserta']) ?>
                    </span>
                </div>
                <div class="text-xs text-blue-200 truncate max-w-[200px] md:max-w-md">
                    <?= htmlspecialchars($sesi['nama_lengkap']) ?>
                </div>
            </div>
        </div>

        <!-- Controls Tengah: Ukuran Font & Indikator Auto-Save -->
        <div class="hidden sm:flex items-center gap-4">
            <!-- Save Status Indicator -->
            <div id="saveIndicator" class="text-xs text-blue-200 font-medium transition-all">
                Siap
            </div>

            <!-- Font Size Chooser -->
            <div class="flex items-center bg-blue-950/60 p-1 rounded-lg border border-blue-800 text-xs">
                <span class="text-slate-400 px-2 font-medium">Ukuran:</span>
                <button type="button" data-size="sm" class="btn-font-size px-2.5 py-1 rounded hover:bg-blue-800 text-blue-200 transition">A-</button>
                <button type="button" data-size="md" class="btn-font-size px-2.5 py-1 rounded bg-blue-100 text-blue-700 font-bold transition">A</button>
                <button type="button" data-size="lg" class="btn-font-size px-2.5 py-1 rounded hover:bg-blue-800 text-blue-200 transition">A+</button>
            </div>
        </div>

        <!-- Kanan: Timer & Tombol Daftar Soal -->
        <div class="flex items-center gap-3">
            <!-- Countdown Timer -->
            <div class="bg-blue-950 px-3 py-1.5 rounded-lg border border-blue-800 flex items-center gap-2 shadow-inner">
                <svg class="w-4 h-4 text-amber-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span id="timerDisplay" class="font-mono font-bold text-sm md:text-base tracking-wider text-amber-300">
                    --:--:--
                </span>
            </div>

            <!-- Tombol Drawer Daftar Soal -->
            <button id="btnTogglePalette" type="button" class="bg-blue-700 hover:bg-blue-600 active:bg-blue-800 text-white text-xs md:text-sm font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5 shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                <span class="hidden sm:inline">Daftar Soal</span>
            </button>
        </div>
    </header>

    <!-- Main Split-Screen Workspace (Height 100vh - 130px) -->
    <main class="split-container flex-1 relative">
        
        <!-- PANE KIRI: WACANA / STIMULUS SOAL -->
        <section id="paneStimulus" class="pane-stimulus flex flex-col">
            <!-- Sub-Header Wacana -->
            <div class="bg-slate-100 border-b border-slate-200 px-5 py-2.5 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">WACANA / STIMULUS</span>
                </div>
                <div id="stimulusTitle" class="text-xs font-semibold text-slate-700 truncate max-w-xs md:max-w-md">
                    Memuat wacana...
                </div>
            </div>

            <!-- Konten Bacaan Stimulus -->
            <div id="stimulusContent" class="p-6 overflow-y-auto flex-1 cbt-text">
                <div class="text-slate-400 italic text-center py-10">Memuat teks wacana...</div>
            </div>

            <!-- Footer Sumber Wacana -->
            <div id="stimulusSource" class="bg-slate-50 border-t border-slate-200 px-6 py-2 text-xs text-slate-500 italic">
                Sumber: Pusmendik
            </div>
        </section>

        <!-- PANE KANAN: LEMBAR SOAL & OPSI JAWABAN -->
        <section id="paneSoal" class="pane-soal flex flex-col">
            <!-- Sub-Header Lembar Soal -->
            <div class="bg-slate-100 border-b border-slate-200 px-5 py-2.5 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-2">
                    <span id="questionNumberBadge" class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded">
                        Soal No. --
                    </span>
                    <span id="subtesBadge" class="bg-slate-200 text-slate-700 text-xs font-semibold px-2 py-1 rounded">
                        Subtes
                    </span>
                </div>
                <span id="tipeSoalBadge" class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                    Pilihan Ganda
                </span>
            </div>

            <!-- Body Lembar Pertanyaan & Opsi -->
            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <!-- Teks Pertanyaan -->
                <div id="questionText" class="cbt-text font-medium text-slate-900 leading-relaxed">
                    Memuat pertanyaan...
                </div>

                <!-- Container Opsi Dinamis -->
                <div id="optionsContainer" class="space-y-3 pt-2">
                    <!-- Diisi secara dinamis oleh JavaScript -->
                </div>
            </div>
        </section>

        <!-- Overlay Loading Transisi -->
        <div id="cbtLoader" class="absolute inset-0 bg-white/70 backdrop-blur-xs flex items-center justify-center z-20 hidden">
            <div class="flex items-center gap-3 bg-white px-5 py-3 rounded-lg shadow-lg border border-slate-200">
                <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-semibold text-slate-700">Memuat Soal...</span>
            </div>
        </div>

    </main>

    <!-- Bottom Footer Navigation Bar Khas Pusmendik (65px) -->
    <footer class="h-[65px] bg-white border-t border-slate-200 flex items-center justify-between px-4 md:px-6 shadow-lg z-30 select-none">
        <!-- Tombol Sebelumnya -->
        <button id="btnPrev" type="button" class="bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs md:text-sm font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition disabled:opacity-40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span class="hidden sm:inline">Soal Sebelumnya</span>
        </button>

        <!-- Tombol / Checkbox Ragu-Ragu (Khas Pusmendik - Kuning) -->
        <label for="chkRagu" class="cursor-pointer flex items-center gap-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-300 px-4 py-2 rounded-lg transition">
            <input type="checkbox" id="chkRagu" class="w-4 h-4 text-amber-600 rounded focus:ring-amber-500 border-slate-300">
            <span class="text-xs md:text-sm font-bold text-amber-900 select-none">Ragu-Ragu</span>
        </label>

        <!-- Tombol Berikutnya / Selesai -->
        <button id="btnNext" type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-xs md:text-sm font-semibold px-5 py-2 rounded-lg flex items-center gap-2 transition shadow">
            <span>Soal Berikutnya</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </footer>

    <!-- Overlay Latar Belakang Drawer -->
    <div id="paletteOverlay" class="fixed inset-0 bg-slate-900/40 z-40 hidden backdrop-blur-xs transition-opacity"></div>

    <!-- Drawer Panel "Daftar Soal" (Slide Over) -->
    <aside id="paletteDrawer" class="palette-drawer">
        <!-- Header Drawer -->
        <div class="px-5 py-4 bg-blue-900 text-white flex items-center justify-between border-b border-blue-800">
            <div class="font-bold text-sm">DAFTAR SOAL UJIAN</div>
            <button id="btnClosePalette" type="button" class="text-blue-200 hover:text-white text-xl font-bold leading-none p-1">
                &times;
            </button>
        </div>

        <!-- Legend / Petunjuk Warna Khas Pusmendik -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 text-xs space-y-1.5">
            <div class="flex items-center gap-2.5">
                <span class="w-4 h-4 rounded bg-slate-200 border border-slate-300 inline-block"></span>
                <span class="text-slate-600">Belum dijawab</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-4 h-4 rounded bg-blue-600 inline-block"></span>
                <span class="text-slate-700 font-medium">Sudah dijawab</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-4 h-4 rounded bg-amber-500 inline-block"></span>
                <span class="text-amber-800 font-medium">Ragu-ragu</span>
            </div>
        </div>

        <!-- Grid Tombol Nomor Soal (1..10) -->
        <div id="paletteGrid" class="palette-grid flex-1">
            <!-- Diisi secara dinamis oleh cbt-engine.js -->
        </div>

        <!-- Tombol Selesai dari Drawer -->
        <div class="p-4 bg-slate-100 border-t border-slate-200">
            <button onclick="window.cbtApp.showFinishModal()" type="button" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm shadow transition">
                Selesaikan Ujian Sekarang
            </button>
        </div>
    </aside>

    <!-- Modal Konfirmasi Selesai Ujian -->
    <div id="finishModal" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden backdrop-blur-xs">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 fade-in">
            <!-- Header Modal -->
            <div class="bg-blue-900 px-6 py-4 text-white">
                <h3 class="font-bold text-lg">Konfirmasi Selesai Ujian</h3>
                <p class="text-xs text-blue-200">Periksa kembali ringkasan jawaban Anda sebelum mengakhiri sesi.</p>
            </div>

            <!-- Body Modal: Ringkasan Status Jawaban -->
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div id="statAnswered" class="text-2xl font-extrabold text-blue-700">0</div>
                        <div class="text-xs text-slate-600 font-medium mt-1">Sudah Dijawab</div>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <div id="statRagu" class="text-2xl font-extrabold text-amber-600">0</div>
                        <div class="text-xs text-slate-600 font-medium mt-1">Ragu-ragu</div>
                    </div>
                    <div class="p-3 bg-slate-100 border border-slate-200 rounded-lg">
                        <div id="statEmpty" class="text-2xl font-extrabold text-slate-600">0</div>
                        <div class="text-xs text-slate-600 font-medium mt-1">Belum Terisi</div>
                    </div>
                </div>

                <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200 leading-relaxed">
                    Setelah Anda menekan tombol <strong>Kumpulkan Ujian</strong>, sesi ujian ini akan langsung dikunci dan sistem akan menghitung perolehan nilai akhir beserta ulasan kunci jawabannya.
                </div>
            </div>

            <!-- Footer Action -->
            <div class="bg-slate-100 px-6 py-3.5 flex justify-end gap-3 border-t border-slate-200">
                <button id="btnCancelFinish" type="button" class="px-4 py-2 rounded-lg text-slate-700 font-semibold text-sm hover:bg-slate-200 transition">
                    Kembali Memeriksa
                </button>
                <button id="btnConfirmFinish" type="button" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow transition">
                    Ya, Kumpulkan Ujian
                </button>
            </div>
        </div>
    </div>

    <!-- Script Inisialisasi CBT Engine -->
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
