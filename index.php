<?php
// index.php - Halaman Konfirmasi Peserta & Pemilihan Paket Ujian SMK / Pusmendik
require_once __DIR__ . '/config/database.php';
$pdo = getDatabaseConnection();

// Pastikan paket-paket terdaftar
require_once __DIR__ . '/database/seed_soal.php';
seedPusmendikData($pdo);

// Ambil semua paket ujian beserta jumlah soalnya
$stmtPaketList = $pdo->query("
    SELECT p.*, (SELECT COUNT(*) FROM soal s WHERE s.paket_id = p.id) AS total_soal
    FROM paket_ujian p
    ORDER BY p.id ASC
");
$paketList = $stmtPaketList->fetchAll();

// Ambil data peserta default
$stmtPeserta = $pdo->query("SELECT * FROM peserta ORDER BY id ASC LIMIT 1");
$peserta = $stmtPeserta->fetch();

// Jika form disubmit
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $namaInput = trim($_POST['nama_lengkap'] ?? $peserta['nama_lengkap']);
    $sekolahInput = trim($_POST['sekolah_asal'] ?? $peserta['sekolah_asal']);
    $paketId = (int)($_POST['paket_id'] ?? $paketList[0]['id']);

    // Validasi paket_id
    $stmtCek = $pdo->prepare("SELECT * FROM paket_ujian WHERE id = ?");
    $stmtCek->execute([$paketId]);
    $paketTerpilih = $stmtCek->fetch();

    if (!$paketTerpilih) {
        $paketTerpilih = $paketList[0];
        $paketId = $paketTerpilih['id'];
    }

    // Perbarui identitas peserta jika diubah
    $stmtUpdatePeserta = $pdo->prepare("UPDATE peserta SET nama_lengkap = ?, sekolah_asal = ? WHERE id = ?");
    $stmtUpdatePeserta->execute([$namaInput, $sekolahInput, $peserta['id']]);

    // Buat sesi ujian baru
    $durasi = (int)($paketTerpilih['durasi_menit'] ?: 45);
    $tokenBaru = bin2hex(random_bytes(16));
    $waktuMulai = date('Y-m-d H:i:s');
    $waktuTarget = date('Y-m-d H:i:s', strtotime("+{$durasi} minutes"));

    $stmtSesi = $pdo->prepare("
        INSERT INTO sesi_ujian (peserta_id, paket_id, token_sesi, waktu_mulai, waktu_selesai_target, status, skor_akhir, jawaban_tersimpan)
        VALUES (?, ?, ?, ?, ?, 'berlangsung', 0, '{}')
    ");
    $stmtSesi->execute([$peserta['id'], $paketId, $tokenBaru, $waktuMulai, $waktuTarget]);

    header("Location: ujian.php?token=" . urlencode($tokenBaru));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Paket Ujian - CBT Pusmendik & SMK</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between text-slate-900">

    <!-- Header Instansi Pusmendik -->
    <header class="bg-[#152e55] text-white shadow-xs border-b border-blue-950">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded flex items-center justify-center text-blue-900 font-black text-xl shadow-xs">
                    🏛️
                </div>
                <div>
                    <h1 class="text-sm md:text-base font-extrabold tracking-wide uppercase">PUSAT ASESMEN PENDIDIKAN</h1>
                    <p class="text-xs text-slate-300">Badan Standar, Kurikulum, dan Asesmen Pendidikan (BSKAP) Kemendikbudristek</p>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <span class="inline-block bg-[#0d1e38] text-blue-200 text-xs px-3 py-1 rounded font-bold border border-blue-900">
                    Sistem CBT Terstandar
                </span>
            </div>
        </div>
    </header>

    <!-- Konten Utama: Pilihan Paket Ujian & Identitas -->
    <main class="max-w-4xl mx-auto px-4 py-8 flex-1 w-full">
        <div class="bg-white rounded border border-slate-300 shadow-sm overflow-hidden">
            <!-- Header Kartu -->
            <div class="bg-[#152e55] px-6 py-4 text-white border-b border-blue-950">
                <h2 class="text-base md:text-lg font-extrabold uppercase tracking-wide">Konfirmasi Peserta & Pemilihan Paket Asesmen</h2>
                <p class="text-xs text-slate-300 mt-0.5">Tersedia bank soal resmi Pusmendik untuk rumpun kejuruan SMK dan asesmen umum.</p>
            </div>

            <form method="POST" action="index.php" class="p-6 md:p-8 space-y-6">
                <!-- 1. Data Peserta -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b pb-2 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-blue-900 rounded-xs"></span>
                        1. IDENTITAS PESERTA ASESMEN
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nomor Peserta Ujian</label>
                            <input type="text" value="<?= htmlspecialchars($peserta['nomor_peserta']) ?>" readonly class="w-full bg-slate-100 border border-slate-300 rounded px-3 py-2 text-sm font-mono font-bold text-slate-700 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap Peserta</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($peserta['nama_lengkap']) ?>" required class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm font-medium text-slate-900 focus:border-blue-900">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Asal Sekolah / Jurusan Kejuruan</label>
                            <input type="text" name="sekolah_asal" value="<?= htmlspecialchars($peserta['sekolah_asal']) ?>" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm font-medium text-slate-900 focus:border-blue-900" placeholder="Contoh: SMKN 1 Jakarta - Teknik Jaringan Komputer">
                        </div>
                    </div>
                </div>

                <!-- 2. PILIHAN PAKET SOAL SMK & PUSMENDIK -->
                <div class="space-y-4 pt-2">
                    <div class="border-b pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2.5 h-2.5 bg-blue-900 rounded-xs"></span>
                            2. PILIH PAKET SOAL UJIAN (TERMASUK SMK)
                        </h3>
                        <span class="text-xs text-blue-900 font-bold"><?= count($paketList) ?> Paket Soal Tersedia</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <?php foreach ($paketList as $idx => $p): 
                            $isFirst = ($idx === 0);
                            $badgeColor = match($p['kategori']) {
                                'Teknologi Informasi & Komunikasi' => 'bg-indigo-100 text-indigo-950 border-indigo-300',
                                'Teknologi & Rekayasa' => 'bg-amber-100 text-amber-950 border-amber-300',
                                'Bisnis & Manajemen' => 'bg-emerald-100 text-emerald-950 border-emerald-300',
                                default => 'bg-blue-100 text-blue-950 border-blue-300'
                            };
                        ?>
                        <label class="paket-card cursor-pointer border-2 <?= $isFirst ? 'border-blue-900 bg-blue-50/50' : 'border-slate-300 bg-white' ?> rounded p-4 flex items-start gap-4 transition hover:border-slate-400">
                            <div class="pt-1">
                                <input type="radio" name="paket_id" value="<?= $p['id'] ?>" <?= $isFirst ? 'checked' : '' ?> class="w-5 h-5 text-blue-900 focus:ring-blue-900 border-slate-300">
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-extrabold px-2 py-0.5 rounded-xs border <?= $badgeColor ?>">
                                        <?= htmlspecialchars($p['jenjang']) ?> &bull; <?= htmlspecialchars($p['kategori']) ?>
                                    </span>
                                    <span class="text-xs font-mono font-bold text-slate-500">[<?= htmlspecialchars($p['kode']) ?>]</span>
                                </div>
                                <h4 class="font-extrabold text-sm md:text-base text-slate-900">
                                    <?= htmlspecialchars($p['judul']) ?>
                                </h4>
                                <div class="flex items-center gap-4 text-xs font-semibold text-slate-600 pt-1">
                                    <span>⏱️ Alokasi: <strong><?= $p['durasi_menit'] ?> Menit</strong></span>
                                    <span>📝 Jumlah: <strong><?= $p['total_soal'] ?> Butir Soal</strong></span>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Petunjuk Kiosk Mode -->
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r text-xs md:text-sm text-amber-950 space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <span>⚠️</span> Peringatan Keamanan Kiosk CBT:
                    </p>
                    <p class="text-amber-900 text-xs leading-relaxed">
                        Sistem dilengkapi pengaman ketat: jika Anda keluar dari layar penuh atau berpindah jendela/tab <strong>1 kali saja</strong>, sesi ujian akan langsung <strong>terkunci total</strong> dan memerlukan <strong>Token Pengawas</strong> dari CMD untuk membukanya kembali.
                    </p>
                </div>

                <!-- Tombol Action -->
                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#152e55] hover:bg-[#1e40af] active:bg-blue-950 text-white font-extrabold py-3.5 px-6 rounded text-sm md:text-base shadow transition tracking-wide flex items-center justify-center gap-2">
                        <span>Mulai Kerjakan Paket Ujian Terpilih</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-800 text-slate-300 text-center py-4 text-xs border-t border-slate-700">
        <p>&copy; <?= date('Y') ?> Pusat Asesmen Pendidikan (Pusmendik). Simulasi Mandiri CBT SMK & SMA.</p>
    </footer>

    <!-- Script Pemilihan Kartu Interaktif -->
    <script>
        document.querySelectorAll('input[name="paket_id"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.paket-card').forEach(card => {
                    card.classList.remove('border-blue-900', 'bg-blue-50/50');
                    card.classList.add('border-slate-300', 'bg-white');
                });
                const chosenCard = radio.closest('.paket-card');
                if (chosenCard) {
                    chosenCard.classList.remove('border-slate-300', 'bg-white');
                    chosenCard.classList.add('border-blue-900', 'bg-blue-50/50');
                }
            });
        });
    </script>
</body>
</html>
