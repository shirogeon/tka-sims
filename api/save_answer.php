<?php
// api/save_answer.php - Menyimpan Jawaban Peserta Secara Real-Time (Auto-Save)
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
require_once dirname(__DIR__) . '/config/database.php';

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    $token = trim($data['token'] ?? '');
    $nomor = (int)($data['nomor'] ?? 0);
    $jawaban = $data['jawaban'] ?? null;
    $isRagu = !empty($data['is_ragu']);

    if (empty($token) || $nomor <= 0) {
        echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
        exit;
    }

    $pdo = getDatabaseConnection();

    // Ambil sesi ujian aktif
    $stmt = $pdo->prepare("SELECT id, status, jawaban_tersimpan FROM sesi_ujian WHERE token_sesi = ?");
    $stmt->execute([$token]);
    $sesi = $stmt->fetch();

    if (!$sesi) {
        echo json_encode(['success' => false, 'message' => 'Sesi ujian tidak valid']);
        exit;
    }

    if ($sesi['status'] === 'selesai') {
        echo json_encode(['success' => false, 'message' => 'Ujian sudah diselesaikan dan dikunci']);
        exit;
    }

    $jawabanTersimpan = json_decode($sesi['jawaban_tersimpan'] ?? '{}', true) ?: [];
    
    // Perbarui data jawaban untuk nomor ini
    $jawabanTersimpan[$nomor] = [
        'jawaban' => $jawaban,
        'is_ragu' => $isRagu,
        'timestamp' => time()
    ];

    $jsonUpdated = json_encode($jawabanTersimpan, JSON_UNESCAPED_UNICODE);

    $stmtUpdate = $pdo->prepare("UPDATE sesi_ujian SET jawaban_tersimpan = ? WHERE id = ?");
    $stmtUpdate->execute([$jsonUpdated, $sesi['id']]);

    echo json_encode([
        'success' => true,
        'nomor' => $nomor,
        'saved_at' => date('H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
