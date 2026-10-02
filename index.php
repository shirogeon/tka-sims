<?php
// index.php - Portal Utama Simulasi CBT TKA Pusmendik (Semua Mapel SMA & SMK)
require_once __DIR__ . '/config/database.php';
$pdo = getDatabaseConnection();

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
    <title>Simulasi CBT TKA Pusmendik - Semua Mapel SMA & SMK</title>
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
                    <p class="text-xs text-slate-300">Sistem Simulasi Resmi TKA: SMA (Saintek & Soshum) dan SMK (Semua Rumpun Kejuruan)</p>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <span class="inline-block bg-[#0d1e38] text-blue-200 text-xs px-3 py-1 rounded font-bold border border-blue-900">
                    CBT Kiosk Terstandar
                </span>
            </div>
        </div>
    </header>

    <!-- Konten Utama: Pilihan Paket Ujian & Identitas -->
    <main class="max-w-4xl mx-auto px-4 py-8 flex-1 w-full">
        <div class="bg-white rounded border border-slate-300 shadow-sm overflow-hidden">
            <!-- Header Kartu -->
            <div class="bg-[#152e55] px-6 py-4 text-white border-b border-blue-950 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-base md:text-lg font-extrabold uppercase tracking-wide">Pilih Mata Pelajaran / Rumpun Kejuruan</h2>
                    <p class="text-xs text-slate-300 mt-0.5">Tersedia bank soal resmi TKA untuk seluruh mata pelajaran SMA dan program keahlian SMK.</p>
                </div>
                <div class="bg-[#0b1b36] px-3 py-1 rounded text-xs font-mono font-bold text-amber-400 border border-slate-700 whitespace-nowrap self-start sm:self-center">
                    <?= count($paketList) ?> MAPEL TERSEDIA
                </div>
            </div>

            <form method="POST" action="index.php" class="p-6 md:p-8 space-y-6">
                <!-- 1. Data Peserta -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b pb-2 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-blue-900 rounded-xs"></span>
                        1. IDENTITAS PESERTA UJIAN
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nomor Peserta Ujian</label>
                            <input type="text" value="<?= htmlspecialchars($peserta['nomor_peserta']) ?>" readonly class="w-full bg-slate-100 border border-slate-300 rounded px-3 py-2 text-sm font-mono font-bold text-slate-700 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap Siswa</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($peserta['nama_lengkap']) ?>" required class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm font-medium text-slate-900 focus:border-blue-900">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Asal Sekolah & Jurusan</label>
                            <input type="text" name="sekolah_asal" value="<?= htmlspecialchars($peserta['sekolah_asal']) ?>" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm font-medium text-slate-900 focus:border-blue-900" placeholder="Contoh: SMAN 1 / SMKN 2 - Teknik Jaringan Komputer">
                        </div>
                    </div>
                </div>

                <!-- 2. PILIHAN SEMUA MAPEL DENGAN FILTER TAB -->
                <div class="space-y-4 pt-2">
                    <div class="border-b pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2.5 h-2.5 bg-blue-900 rounded-xs"></span>
                            2. PILIH PAKET SOAL UJIAN TKA
                        </h3>
                        <!-- Search Box Cepat -->
                        <div class="relative">
                            <input type="text" id="searchMapel" placeholder="Cari mapel / jurusan..." class="text-xs border border-slate-300 rounded px-3 py-1.5 w-full sm:w-56 focus:border-blue-900 outline-none">
                        </div>
                    </div>

                    <!-- Tombol Filter Kategori -->
                    <div class="flex flex-wrap gap-1.5 text-xs font-bold" id="filterTabs">
                        <button type="button" data-filter="all" class="tab-btn px-3 py-1.5 rounded bg-blue-900 text-white">Semua (<?= count($paketList) ?>)</button>
                        <button type="button" data-filter="SMK" class="tab-btn px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300">Khusus SMK (8)</button>
                        <button type="button" data-filter="Saintek" class="tab-btn px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300">SMA Saintek (4)</button>
                        <button type="button" data-filter="Soshum" class="tab-btn px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300">SMA Soshum (4)</button>
                        <button type="button" data-filter="Umum" class="tab-btn px-3 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300">Wajib / Umum (3)</button>
                    </div>

                    <!-- Daftar Kartu Paket Ujian (Scrollable & Responsive) -->
                    <div class="grid grid-cols-1 gap-2.5 max-h-[420px] overflow-y-auto pr-1" id="paketContainer">
                        <?php foreach ($paketList as $idx => $p): 
                            $isFirst = ($idx === 0);
                            $badgeColor = match(true) {
                                str_contains($p['kategori'], 'Saintek') => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                str_contains($p['kategori'], 'Soshum') => 'bg-amber-100 text-amber-900 border-amber-300',
                                str_contains($p['kategori'], 'TIK') => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                                str_contains($p['kategori'], 'Rekayasa') => 'bg-orange-100 text-orange-900 border-orange-300',
                                str_contains($p['kategori'], 'Bisnis') => 'bg-cyan-100 text-cyan-900 border-cyan-300',
                                str_contains($p['kategori'], 'Kesehatan') => 'bg-rose-100 text-rose-900 border-rose-300',
                                str_contains($p['kategori'], 'Agribisnis') => 'bg-lime-100 text-lime-900 border-lime-300',
                                default => 'bg-blue-100 text-blue-900 border-blue-300'
                            };
                        ?>
                        <label class="paket-card cursor-pointer border <?= $isFirst ? 'border-blue-900 bg-blue-50/50' : 'border-slate-300 bg-white' ?> rounded p-3.5 flex items-start gap-3 transition hover:border-slate-400"
                               data-kategori="<?= htmlspecialchars($p['kategori']) ?>"
                               data-jenjang="<?= htmlspecialchars($p['jenjang']) ?>"
                               data-judul="<?= htmlspecialchars(strtolower($p['judul'])) ?>">
                            <div class="pt-0.5">
                                <input type="radio" name="paket_id" value="<?= $p['id'] ?>" <?= $isFirst ? 'checked' : '' ?> class="w-4 h-4 text-blue-900 focus:ring-blue-900 border-slate-300">
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-xs border <?= $badgeColor ?>">
                                        <?= htmlspecialchars($p['jenjang']) ?> &bull; <?= htmlspecialchars($p['kategori']) ?>
                                    </span>
                                    <span class="text-[10px] font-mono font-bold text-slate-500">[<?= htmlspecialchars($p['kode']) ?>]</span>
                                </div>
                                <h4 class="font-extrabold text-sm text-slate-900 leading-tight">
                                    <?= htmlspecialchars($p['judul']) ?>
                                </h4>
                                <div class="flex items-center gap-4 text-xs font-medium text-slate-600">
                                    <span>⏱️ <?= $p['durasi_menit'] ?> Menit</span>
                                    <span>📝 <?= $p['total_soal'] ?> Butir Soal</span>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Info Kiosk Mode -->
                <div class="bg-amber-50 border-l-4 border-amber-500 p-3.5 rounded-r text-xs text-amber-950 space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <span>⚠️</span> Peringatan Keamanan Kiosk CBT:
                    </p>
                    <p class="text-amber-900 leading-relaxed">
                        Jika peserta keluar dari Mode Layar Penuh atau berpindah jendela/aplikasi <strong>hanya 1 kali</strong>, sistem akan <strong>langsung mengunci ujian</strong>. Pembukaan kunci wajib menggunakan <strong>Token Pengawas</strong> yang ada di konsol CMD server CBT (<code class="bg-amber-200 px-1 py-0.5 rounded font-mono font-bold">token-pengawas.bat</code>).
                    </p>
                </div>

                <!-- Tombol Action -->
                <div>
                    <button type="submit" class="w-full bg-[#152e55] hover:bg-[#1e40af] active:bg-blue-950 text-white font-extrabold py-3.5 px-6 rounded text-sm md:text-base shadow transition tracking-wide flex items-center justify-center gap-2">
                        <span>Mulai Kerjakan Paket Terpilih</span>
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
        <p>&copy; <?= date('Y') ?> Pusat Asesmen Pendidikan (Pusmendik). Simulasi TKA Lengkap SMA & SMK.</p>
    </footer>

    <!-- Filter & Search Script -->
    <script>
        // Handler Pergantian Kartu
        const updateRadioSelection = () => {
            document.querySelectorAll('.paket-card').forEach(card => {
                const radio = card.querySelector('input[type="radio"]');
                if (radio && radio.checked) {
                    card.classList.remove('border-slate-300', 'bg-white');
                    card.classList.add('border-blue-900', 'bg-blue-50/50');
                } else {
                    card.classList.remove('border-blue-900', 'bg-blue-50/50');
                    card.classList.add('border-slate-300', 'bg-white');
                }
            });
        };

        document.querySelectorAll('input[name="paket_id"]').forEach(radio => {
            radio.addEventListener('change', updateRadioSelection);
        });

        // Handler Filter Tabs
        const filterTabs = document.querySelectorAll('.tab-btn');
        const cards = document.querySelectorAll('.paket-card');
        const searchInput = document.getElementById('searchMapel');

        const applyFilters = () => {
            const activeTab = document.querySelector('.tab-btn.bg-blue-900').dataset.filter;
            const query = (searchInput.value || '').toLowerCase().trim();

            cards.forEach(card => {
                const kategori = card.dataset.kategori || '';
                const jenjang = card.dataset.jenjang || '';
                const judul = card.dataset.judul || '';

                let matchTab = false;
                if (activeTab === 'all') {
                    matchTab = true;
                } else if (activeTab === 'SMK') {
                    matchTab = jenjang.includes('SMK') || kategori.includes('SMK');
                } else if (activeTab === 'Saintek') {
                    matchTab = kategori.includes('Saintek');
                } else if (activeTab === 'Soshum') {
                    matchTab = kategori.includes('Soshum');
                } else if (activeTab === 'Umum') {
                    matchTab = kategori.includes('Umum') || kategori.includes('Literasi');
                }

                const matchQuery = (judul.includes(query) || kategori.toLowerCase().includes(query) || jenjang.toLowerCase().includes(query));

                if (matchTab && matchQuery) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        };

        filterTabs.forEach(btn => {
            btn.addEventListener('click', () => {
                filterTabs.forEach(b => {
                    b.classList.remove('bg-blue-900', 'text-white');
                    b.classList.add('bg-slate-200', 'text-slate-700');
                });
                btn.classList.add('bg-blue-900', 'text-white');
                btn.classList.remove('bg-slate-200', 'text-slate-700');
                applyFilters();
            });
        });

        searchInput.addEventListener('input', applyFilters);
    </script>
</body>
</html>
