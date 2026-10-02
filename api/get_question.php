<?php
// api/get_question.php - Mengambil Soal dan Status Jawaban Peserta
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
require_once dirname(__DIR__) . '/config/database.php';

try {
    $pdo = getDatabaseConnection();
    $token = trim($_GET['token'] ?? '');
    $nomor = (int)($_GET['nomor'] ?? 1);

    if (empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Token sesi tidak valid']);
        exit;
    }

    // Ambil sesi ujian
    $stmtSesi = $pdo->prepare("SELECT s.*, p.judul, p.durasi_menit FROM sesi_ujian s JOIN paket_ujian p ON s.paket_id = p.id WHERE s.token_sesi = ?");
    $stmtSesi->execute([$token]);
    $sesi = $stmtSesi->fetch();

    if (!$sesi) {
        echo json_encode(['success' => false, 'message' => 'Sesi ujian tidak ditemukan']);
        exit;
    }

    $paketId = (int)$sesi['paket_id'];
    $jawabanTersimpan = json_decode($sesi['jawaban_tersimpan'] ?? '{}', true) ?: [];

    // Ambil daftar nomor semua soal dalam paket untuk status drawer
    $stmtList = $pdo->prepare("SELECT id, nomor_urut, tipe_soal, subtes FROM soal WHERE paket_id = ? ORDER BY nomor_urut ASC");
    $stmtList->execute([$paketId]);
    $allQuestions = $stmtList->fetchAll();

    $palette = [];
    foreach ($allQuestions as $item) {
        $no = $item['nomor_urut'];
        $jData = $jawabanTersimpan[$no] ?? null;
        $isAnswered = false;
        $previewJawaban = '';
        $isRagu = false;

        if ($jData) {
            $isRagu = !empty($jData['is_ragu']);
            $ans = $jData['jawaban'] ?? null;
            if ($ans !== null && $ans !== '' && (!is_array($ans) || count($ans) > 0)) {
                $isAnswered = true;
                if (is_string($ans)) {
                    $previewJawaban = strtoupper($ans);
                } elseif (is_array($ans)) {
                    if ($item['tipe_soal'] === 'pg_kompleks') {
                        $previewJawaban = implode(',', $ans);
                    } else {
                        $previewJawaban = '✓';
                    }
                }
            }
        }

        $palette[] = [
            'nomor' => (int)$no,
            'tipe_soal' => $item['tipe_soal'],
            'is_answered' => $isAnswered,
            'is_ragu' => $isRagu,
            'preview_jawaban' => $previewJawaban
        ];
    }

    // Ambil detail soal aktif
    $stmtSoal = $pdo->prepare("
        SELECT s.*, st.judul AS stimulus_judul, st.konten_html AS stimulus_konten, st.sumber AS stimulus_sumber
        FROM soal s
        LEFT JOIN stimulus st ON s.stimulus_id = st.id
        WHERE s.paket_id = ? AND s.nomor_urut = ?
        LIMIT 1
    ");
    $stmtSoal->execute([$paketId, $nomor]);
    $soal = $stmtSoal->fetch();

    if (!$soal) {
        echo json_encode(['success' => false, 'message' => 'Soal nomor ' . $nomor . ' tidak ditemukan']);
        exit;
    }

    $currentAnswer = $jawabanTersimpan[$nomor] ?? ['jawaban' => null, 'is_ragu' => false];

    // Hitung sisa waktu dalam detik
    $targetTime = strtotime($sesi['waktu_selesai_target']);
    $currentTime = time();
    $sisaDetik = max(0, $targetTime - $currentTime);

    echo json_encode([
        'success' => true,
        'sesi' => [
            'status' => $sesi['status'],
            'sisa_detik' => $sisaDetik,
            'durasi_menit' => (int)$sesi['durasi_menit']
        ],
        'total_soal' => count($allQuestions),
        'nomor_aktif' => $nomor,
        'palette' => $palette,
        'soal' => [
            'id' => (int)$soal['id'],
            'nomor_urut' => (int)$soal['nomor_urut'],
            'subtes' => $soal['subtes'],
            'tipe_soal' => $soal['tipe_soal'],
            'pertanyaan' => $soal['pertanyaan'],
            'opsi_jawaban' => json_decode($soal['opsi_jawaban'] ?? 'null', true),
            'stimulus' => $soal['stimulus_id'] ? [
                'judul' => $soal['stimulus_judul'],
                'konten' => $soal['stimulus_konten'],
                'sumber' => $soal['stimulus_sumber']
            ] : null,
            'jawaban_tersimpan' => $currentAnswer['jawaban'] ?? null,
            'is_ragu' => !empty($currentAnswer['is_ragu'])
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
