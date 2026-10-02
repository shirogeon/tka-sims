<?php
// ujian.php - Antarmuka CBT Standar Pusmendik ANBK dengan Kiosk Mode & Lockdown CMD
require_once __DIR__ . '/config/database.php';
$pdo = getDatabaseConnection();

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    header("Location: index.php");
    exit;
}

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

    <!-- Top Header Bar Formal ANBK Pusmendik (65px) -->
    <header class="h-[65px] bg-[#152e55] text-white flex items-center justify-between px-4 md:px-6 shadow-xs z-30 border-b border-blue-950">
        <!-- Logo & Identitas Peserta Tabular Khas ANBK -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded flex items-center justify-center text-blue-900 font-black text-xl shadow-xs">
                🏛️
            </div>
            <div class="text-xs leading-snug">
                <div class="font-extrabold text-white tracking-wide uppercase">PUSAT ASESMEN PENDIDIKAN - BSKAP</div>
                <div class="text-slate-300">
                    Mata Uji: <strong class="text-white"><?= htmlspecialchars($sesi['judul_paket']) ?></strong>
                </div>
                <div class="text-blue-200">
                    Peserta: <span class="font-mono font-bold text-white"><?= htmlspecialchars($sesi['nomor_peserta']) ?></span> (<?= htmlspecialchars($sesi['nama_lengkap']) ?>)
                </div>
            </div>
        </div>

        <!-- Controls Tengah: Ukuran Font Formal -->
        <div class="hidden md:flex items-center gap-2">
            <span class="text-xs text-slate-300 font-medium">Ukuran Font:</span>
            <div class="flex border border-slate-600 rounded bg-[#0d1e38] overflow-hidden text-xs">
                <button type="button" data-size="sm" class="btn-font-size px-2.5 py-1 hover:bg-blue-800 text-slate-300">A-</button>
                <button type="button" data-size="md" class="btn-font-size px-2.5 py-1 bg-blue-700 text-white font-bold border-x border-slate-600">A</button>
                <button type="button" data-size="lg" class="btn-font-size px-2.5 py-1 hover:bg-blue-800 text-slate-300">A+</button>
            </div>
        </div>

        <!-- Kanan: Sisa Waktu & Tombol Daftar Soal -->
        <div class="flex items-center gap-3">
            <!-- Digital Timer Box -->
            <div class="bg-[#0a182e] px-3.5 py-1.5 rounded border border-slate-700 flex items-center gap-2 text-xs">
                <span class="text-slate-400 font-medium hidden sm:inline">Sisa Waktu:</span>
                <span id="timerDisplay" class="font-mono font-black text-sm md:text-base tracking-widest text-amber-400">
                    --:--:--
                </span>
            </div>

            <!-- Tombol Daftar Soal ANBK -->
            <button id="btnOpenPalette" type="button" class="bg-[#1e40af] hover:bg-[#1d4ed8] active:bg-[#1e3a8a] text-white text-xs md:text-sm font-bold px-4 py-2 rounded flex items-center gap-2 shadow-xs transition border border-blue-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                <span>DAFTAR SOAL</span>
            </button>
        </div>
    </header>

    <!-- Main Split-Screen Workspace -->
    <main class="split-container flex-1 relative">
        
        <!-- PANE KIRI: WACANA / STIMULUS -->
        <section id="paneStimulus" class="pane-stimulus flex flex-col">
            <!-- Header Wacana -->
            <div class="bg-slate-100 border-b border-slate-300 px-5 py-2.5 flex items-center justify-between sticky top-0 z-10 text-xs">
                <span class="font-bold uppercase tracking-wider text-slate-700">
                    INFORMASI WACANA / STIMULUS
                </span>
                <span id="stimulusTitle" class="font-semibold text-slate-800 truncate max-w-xs md:max-w-md">
                    Memuat wacana...
                </span>
            </div>

            <!-- Konten Wacana -->
            <div id="stimulusContent" class="p-6 md:p-8 overflow-y-auto flex-1 cbt-text text-slate-900 leading-relaxed">
                <div class="text-slate-400 italic text-center py-12">Memuat teks wacana...</div>
            </div>

            <!-- Footer Sumber Wacana -->
            <div id="stimulusSource" class="bg-slate-50 border-t border-slate-300 px-6 py-2 text-xs text-slate-600 italic">
                Sumber: Pusat Asesmen Pendidikan
            </div>
        </section>

        <!-- PANE KANAN: LEMBAR PERTANYAAN -->
        <section id="paneSoal" class="pane-soal flex flex-col">
            <!-- Header Lembar Soal -->
            <div class="bg-slate-100 border-b border-slate-300 px-5 py-2.5 flex items-center justify-between sticky top-0 z-10 text-xs">
                <div class="flex items-center gap-2">
                    <span id="questionNumberBadge" class="bg-[#152e55] text-white font-extrabold px-3 py-1 rounded-sm uppercase tracking-wide">
                        SOAL NO. --
                    </span>
                    <span id="subtesBadge" class="bg-slate-200 text-slate-800 font-semibold px-2.5 py-1 rounded-sm">
                        Subtes
                    </span>
                </div>
                <span id="tipeSoalBadge" class="font-bold text-blue-900 bg-blue-100 px-2.5 py-1 rounded-sm border border-blue-300">
                    Pilihan Ganda
                </span>
            </div>

            <!-- Body Soal -->
            <div class="p-6 md:p-8 overflow-y-auto flex-1 space-y-6">
                <!-- Teks Pertanyaan -->
                <div id="questionText" class="cbt-text font-medium text-slate-900 leading-relaxed">
                    Memuat pertanyaan...
                </div>

                <!-- Opsi Jawaban -->
                <div id="optionsContainer" class="space-y-3 pt-1">
                    <!-- Diisi dinamis oleh JavaScript -->
                </div>
            </div>
        </section>

        <!-- Loading Overlay -->
        <div id="cbtLoader" class="absolute inset-0 bg-white/70 flex items-center justify-center z-20 hidden">
            <div class="flex items-center gap-3 bg-white px-5 py-3 rounded shadow-lg border border-slate-300">
                <div class="w-5 h-5 border-2 border-blue-900 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-bold text-slate-800">Memuat Butir Soal...</span>
            </div>
        </div>

    </main>

    <!-- Bottom Footer Navigation Khas ANBK (60px) -->
    <footer class="h-[60px] bg-slate-100 border-t border-slate-300 flex items-center justify-between px-4 md:px-8 z-30">
        <!-- Tombol Sebelumnya -->
        <button id="btnPrev" type="button" class="bg-slate-600 hover:bg-slate-700 active:bg-slate-800 text-white text-xs md:text-sm font-bold px-5 py-2.5 rounded flex items-center gap-2 transition disabled:opacity-30 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>SOAL SEBELUMNYA</span>
        </button>

        <!-- Tombol Ragu-Ragu Khas ANBK -->
        <label for="chkRagu" class="cursor-pointer flex items-center gap-2 bg-amber-400 hover:bg-amber-300 active:bg-amber-500 border border-amber-600 px-5 py-2 rounded transition">
            <input type="checkbox" id="chkRagu" class="w-4 h-4 text-amber-900 rounded focus:ring-amber-500 border-amber-600">
            <span class="text-xs md:text-sm font-black text-amber-950 uppercase tracking-wide">RAGU - RAGU</span>
        </label>

        <!-- Tombol Berikutnya -->
        <button id="btnNext" type="button" class="bg-[#1e40af] hover:bg-[#1d4ed8] active:bg-[#1e3a8a] text-white text-xs md:text-sm font-bold px-6 py-2.5 rounded flex items-center gap-2 transition">
            <span>SOAL BERIKUTNYA</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </footer>

    <!-- POPUP MODAL DAFTAR SOAL KHAS ANBK PUSMENDIK -->
    <div id="paletteModal" class="anbk-modal-backdrop hidden">
        <div class="anbk-palette-card">
            <!-- Header Modal -->
            <div class="px-5 py-3 bg-[#152e55] text-white flex items-center justify-between border-b border-blue-900">
                <div class="font-extrabold text-sm tracking-wider uppercase">
                    DAFTAR SOAL UJIAN
                </div>
                <button id="btnClosePalette" type="button" class="text-slate-300 hover:text-white text-2xl font-bold leading-none p-1">
                    &times;
                </button>
            </div>

            <!-- Grid 5 Kolom Nomor Soal ANBK -->
            <div id="paletteGrid" class="anbk-palette-grid">
                <!-- Diisi oleh JavaScript -->
            </div>

            <!-- Legenda Warna ANBK -->
            <div class="px-5 py-2.5 bg-slate-100 border-t border-slate-300 flex items-center justify-around text-xs font-semibold text-slate-800">
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-xs bg-white border-2 border-slate-400 inline-block"></span>
                    <span>Belum Dijawab</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-xs bg-slate-900 border-2 border-slate-900 inline-block"></span>
                    <span>Sudah Dijawab</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-xs bg-amber-500 border-2 border-amber-600 inline-block"></span>
                    <span>Ragu-Ragu</span>
                </div>
            </div>

            <!-- Footer Selesai -->
            <div class="p-3.5 bg-white border-t border-slate-300">
                <button onclick="window.cbtApp.showFinishModal()" type="button" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-black py-2.5 px-4 rounded text-sm transition">
                    Selesaikan Ujian Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI SELESAI UJIAN -->
    <div id="finishModal" class="fixed inset-0 bg-slate-900/70 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded max-w-md w-full overflow-hidden border border-slate-400 shadow-2xl">
            <div class="bg-[#152e55] px-6 py-3.5 text-white">
                <h3 class="font-bold text-base uppercase">Konfirmasi Selesai Ujian</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-slate-900 text-white rounded">
                        <div id="statAnswered" class="text-2xl font-black">0</div>
                        <div class="text-xs text-slate-300 mt-1">Sudah Dijawab</div>
                    </div>
                    <div class="p-3 bg-amber-500 text-amber-950 rounded">
                        <div id="statRagu" class="text-2xl font-black">0</div>
                        <div class="text-xs font-bold mt-1">Ragu-ragu</div>
                    </div>
                    <div class="p-3 bg-slate-100 border border-slate-300 rounded">
                        <div id="statEmpty" class="text-2xl font-black text-slate-700">0</div>
                        <div class="text-xs text-slate-600 font-bold mt-1">Belum Diisi</div>
                    </div>
                </div>

                <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded border border-slate-300 leading-relaxed">
                    Setelah Anda menekan <strong>Kumpulkan Ujian</strong>, sesi ujian ini akan langsung dikunci dan hasil nilai beserta pembahasan butir soal akan langsung ditampilkan.
                </p>
            </div>

            <div class="bg-slate-100 px-6 py-3 flex justify-end gap-2.5 border-t border-slate-300">
                <button id="btnCancelFinish" type="button" class="px-4 py-2 rounded text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                    Kembali Memeriksa
                </button>
                <button id="btnConfirmFinish" type="button" class="px-5 py-2 rounded bg-emerald-700 hover:bg-emerald-800 text-white font-black text-xs shadow-xs transition">
                    Ya, Kumpulkan Ujian
                </button>
            </div>
        </div>
    </div>

    <!-- LAYAR KUNCI PELANGGARAN KIOSK (LOCKOUT REPUTASI PENGAWAS CMD) -->
    <div id="examLockoutScreen" class="lockout-screen hidden">
        <div class="lockout-card">
            <div class="w-14 h-14 bg-red-600 rounded flex items-center justify-center text-3xl mx-auto mb-3 shadow-lg">
                🔒
            </div>
            <h2 class="text-xl font-black text-red-500 uppercase tracking-wider mb-1">
                UJIAN TERKUNCI (PELANGGARAN!)
            </h2>
            <p class="text-xs text-slate-300 mb-4 leading-relaxed">
                Sistem mendeteksi Anda keluar dari Mode Layar Penuh atau berpindah tab/jendela. Sesi ujian Anda telah dibekukan secara otomatis oleh sistem keamanan CBT.
            </p>
            
            <div class="bg-slate-900 border border-slate-700 p-4 rounded text-left text-xs mb-5 space-y-1">
                <p class="text-slate-400">Status: <span class="text-red-400 font-bold">Akses Dibekukan</span></p>
                <p class="text-slate-300 leading-relaxed">
                    Untuk membuka kembali lembar ujian ini, minta <strong>TOKEN RESET PENGAWAS</strong> yang muncul di Command Prompt (CMD) server CBT.
                </p>
            </div>

            <form id="formUnlockExam" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Ketikkan 6 Karakter Token Pengawas:</label>
                    <input type="text" id="inputUnlockToken" maxlength="6" placeholder="Contoh: AB894K" required autocomplete="off" class="w-full bg-slate-900 border-2 border-slate-600 focus:border-blue-500 rounded py-2.5 px-3 text-center text-2xl font-mono font-black text-amber-400 tracking-widest uppercase outline-none">
                </div>
                <div id="unlockErrorMsg" class="text-xs text-red-400 font-semibold hidden"></div>
                <button type="submit" id="btnSubmitUnlock" class="w-full bg-blue-700 hover:bg-blue-600 active:bg-blue-800 text-white font-bold py-2.5 px-4 rounded text-sm transition">
                    Buka Kunci & Lanjutkan Ujian
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL PROMPT AWAL MASUK FULLSCREEN -->
    <div id="startFullscreenPrompt" class="fixed inset-0 bg-slate-950/90 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded max-w-md w-full p-6 text-center border border-slate-400 shadow-2xl">
            <div class="w-12 h-12 bg-blue-100 text-blue-900 rounded flex items-center justify-center text-2xl mx-auto mb-3">
                🖥️
            </div>
            <h3 class="text-base font-black text-slate-900 uppercase tracking-tight mb-2">Mode Kiosk Layar Penuh Resmi</h3>
            <p class="text-xs text-slate-600 mb-6 leading-relaxed">
                Ujian CBT dilaksanakan dalam Mode Layar Penuh (*Full Screen*). Jika Anda keluar dari layar penuh atau berpindah jendela/aplikasi, sistem akan **langsung mengunci ujian** dan memerlukan Token Pengawas dari CMD untuk membukanya kembali.
            </p>
            <button id="btnStartExamFullscreen" type="button" class="w-full bg-[#152e55] hover:bg-[#1e40af] active:bg-blue-900 text-white font-bold py-3 px-6 rounded text-sm transition">
                Masuk Layar Penuh & Mulai Mengerjakan
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
