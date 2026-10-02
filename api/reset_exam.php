<?php
// api/reset_exam.php - Membuat Sesi Ujian Baru / Mengulang Ujian
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
require_once dirname(__DIR__) . '/config/database.php';

try {
    $pdo = getDatabaseConnection();

    // Pastikan ada peserta
    $stmtPeserta = $pdo->query("SELECT id FROM peserta ORDER BY id ASC LIMIT 1");
    $peserta = $stmtPeserta->fetch();
    $pesertaId = $peserta ? (int)$peserta['id'] : 1;

    // Pastikan ada paket ujian
    $stmtPaket = $pdo->query("SELECT id, durasi_menit FROM paket_ujian ORDER BY id ASC LIMIT 1");
    $paket = $stmtPaket->fetch();
    
    if (!$paket) {
        echo json_encode(['success' => false, 'message' => 'Paket ujian belum siap']);
        exit;
    }

    $paketId = (int)$paket['id'];
    $durasi = (int)($paket['durasi_menit'] ?: 45);

    $newToken = bin2hex(random_bytes(16));
    $waktuMulai = date('Y-m-d H:i:s');
    $waktuTarget = date('Y-m-d H:i:s', strtotime("+{$durasi} minutes"));

    $stmtInsert = $pdo->prepare("
        INSERT INTO sesi_ujian (peserta_id, paket_id, token_sesi, waktu_mulai, waktu_selesai_target, status, skor_akhir, jawaban_tersimpan)
        VALUES (?, ?, ?, ?, ?, 'berlangsung', 0, '{}')
    ");
    $stmtInsert->execute([$pesertaId, $paketId, $newToken, $waktuMulai, $waktuTarget]);

    echo json_encode([
        'success' => true,
        'token' => $newToken,
        'redirect' => 'ujian.php?token=' . urlencode($newToken)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
