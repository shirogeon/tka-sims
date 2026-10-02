<?php
// index.php - Halaman Konfirmasi Peserta Ujian TKA Pusmendik
require_once __DIR__ . '/config/database.php';
$pdo = getDatabaseConnection();

// Ambil paket ujian pertama
$stmtPaket = $pdo->query("SELECT * FROM paket_ujian ORDER BY id ASC LIMIT 1");
$paket = $stmtPaket->fetch();

// Ambil data peserta default
$stmtPeserta = $pdo->query("SELECT * FROM peserta ORDER BY id ASC LIMIT 1");
$peserta = $stmtPeserta->fetch();

// Hitung total butir soal
$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM soal WHERE paket_id = ?");
$stmtCount->execute([$paket['id']]);
$totalSoal = (int)$stmtCount->fetch()['total'];

// Jika tombol mulai ditekan
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $namaInput = trim($_POST['nama_lengkap'] ?? $peserta['nama_lengkap']);
    $sekolahInput = trim($_POST['sekolah_asal'] ?? $peserta['sekolah_asal']);
    
    // Perbarui profil peserta jika diubah
    $stmtUpdatePeserta = $pdo->prepare("UPDATE peserta SET nama_lengkap = ?, sekolah_asal = ? WHERE id = ?");
    $stmtUpdatePeserta->execute([$namaInput, $sekolahInput, $peserta['id']]);

    // Buat sesi ujian baru
    $durasi = (int)$paket['durasi_menit'];
    $tokenBaru = bin2hex(random_bytes(16));
    $waktuMulai = date('Y-m-d H:i:s');
    $waktuTarget = date('Y-m-d H:i:s', strtotime("+{$durasi} minutes"));

    $stmtSesi = $pdo->prepare("
        INSERT INTO sesi_ujian (peserta_id, paket_id, token_sesi, waktu_mulai, waktu_selesai_target, status, skor_akhir, jawaban_tersimpan)
        VALUES (?, ?, ?, ?, ?, 'berlangsung', 0, '{}')
    ");
    $stmtSesi->execute([$peserta['id'], $paket['id'], $tokenBaru, $waktuMulai, $waktuTarget]);

    header("Location: ujian.php?token=" . urlencode($tokenBaru));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Data Peserta - Simulasi CBT TKA Pusmendik</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between">

    <!-- Header Instansi Pusmendik -->
    <header class="bg-blue-900 text-white shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-900 font-extrabold text-xl shadow">
                    🎓
                </div>
                <div>
                    <h1 class="text-base md:text-lg font-bold tracking-tight">PUSAT ASESMEN PENDIDIKAN</h1>
                    <p class="text-xs text-blue-200">Badan Standar, Kurikulum, dan Asesmen Pendidikan (BSKAP)</p>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <span class="inline-block bg-blue-800 text-blue-200 text-xs px-2.5 py-1 rounded font-semibold border border-blue-700">
                    Sistem CBT Terstandar
                </span>
            </div>
        </div>
    </header>

    <!-- Konten Utama: Kartu Konfirmasi Data -->
    <main class="max-w-3xl mx-auto px-4 py-8 flex-1 w-full">
        <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
            <!-- Header Kartu -->
            <div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-6 py-4 text-white">
                <h2 class="text-lg md:text-xl font-bold">Konfirmasi Data Peserta & Informasi Ujian</h2>
                <p class="text-xs md:text-sm text-blue-100 mt-0.5">Periksa kembali data Anda sebelum memulai simulasi Tes Kemampuan Akademik (TKA).</p>
            </div>

            <form method="POST" action="index.php" class="p-6 md:p-8 space-y-6">
                <!-- Data Peserta -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b pb-2 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-blue-600 rounded-full"></span>
                        1. Identitas Peserta
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nomor Peserta Ujian</label>
                            <input type="text" value="<?= htmlspecialchars($peserta['nomor_peserta']) ?>" readonly class="w-full bg-slate-100 border border-slate-300 rounded-lg px-3.5 py-2 text-sm font-mono font-bold text-slate-700 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Lengkap Peserta</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($peserta['nama_lengkap']) ?>" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2 text-sm font-medium text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Asal Sekolah / Institusi</label>
                            <input type="text" name="sekolah_asal" value="<?= htmlspecialchars($peserta['sekolah_asal']) ?>" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2 text-sm font-medium text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Informasi Ujian -->
                <div class="space-y-4 pt-2">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b pb-2 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></span>
                        2. Rincian Paket Ujian
                    </h3>

                    <div class="bg-blue-50/60 rounded-lg p-4 border border-blue-100 space-y-2.5 text-sm">
                        <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-blue-100/80">
                            <span class="text-slate-500 font-medium">Mata Asesmen:</span>
                            <span class="font-bold text-slate-900"><?= htmlspecialchars($paket['judul']) ?></span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-blue-100/80">
                            <span class="text-slate-500 font-medium">Jenjang & Kategori:</span>
                            <span class="font-semibold text-slate-800"><?= htmlspecialchars($paket['jenjang']) ?> (<?= htmlspecialchars($paket['kategori']) ?>)</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-blue-100/80">
                            <span class="text-slate-500 font-medium">Alokasi Waktu Pengerjaan:</span>
                            <span class="font-bold text-blue-700"><?= (int)$paket['durasi_menit'] ?> Menit</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between py-1">
                            <span class="text-slate-500 font-medium">Jumlah Butir Soal:</span>
                            <span class="font-bold text-slate-900"><?= $totalSoal ?> Butir (Multi-Format Pusmendik)</span>
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Pengerjaan Ringkas -->
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg text-xs md:text-sm text-amber-900 space-y-1">
                    <p class="font-bold text-amber-950 flex items-center gap-1.5">
                        <span>⚠️</span> Peringatan Sebelum Memulai:
                    </p>
                    <ul class="list-disc pl-5 space-y-0.5 text-amber-900/90">
                        <li>Pastikan Anda berada di tempat yang tenang dengan waktu 45 menit tanpa gangguan.</li>
                        <li>Sistem menggunakan format <strong>Split-Screen</strong>: Wacana di kiri, soal di kanan.</li>
                        <li>Setiap jawaban yang Anda pilih akan <strong>otomatis tersimpan</strong> secara realtime.</li>
                    </ul>
                </div>

                <!-- Tombol Action -->
                <div class="pt-4 flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all flex items-center justify-center gap-2 text-base">
                        <span>Mulai Kerjakan Ujian Sekarang</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer Standar Pusmendik -->
    <footer class="bg-slate-800 text-slate-300 text-center py-4 text-xs border-t border-slate-700">
        <p>&copy; <?= date('Y') ?> Pusat Asesmen Pendidikan (Pusmendik). Simulasi Mandiri Berbasis Komputer (CBT).</p>
    </footer>

</body>
</html>
