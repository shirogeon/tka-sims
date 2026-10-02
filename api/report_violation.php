<?php
// api/report_violation.php - Mencatat Pelanggaran & Menghasilkan Token Reset Pengawas
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
require_once dirname(__DIR__) . '/config/database.php';

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: $_POST;

    $tokenSesi = trim($data['token'] ?? '');
    $alasan = trim($data['alasan'] ?? 'Keluar dari layar penuh atau beralih jendela');

    if (empty($tokenSesi)) {
        echo json_encode(['success' => false, 'message' => 'Token sesi tidak valid']);
        exit;
    }

    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT s.*, p.nomor_peserta, p.nama_lengkap FROM sesi_ujian s JOIN peserta p ON s.peserta_id = p.id WHERE s.token_sesi = ?");
    $stmt->execute([$tokenSesi]);
    $sesi = $stmt->fetch();

    if (!$sesi) {
        echo json_encode(['success' => false, 'message' => 'Sesi tidak ditemukan']);
        exit;
    }

    // Buat Token Acak Pengawas 6 Karakter (Huruf Kapital & Angka)
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $unlockToken = '';
    for ($i = 0; $i < 6; $i++) {
        $unlockToken .= $chars[random_int(0, strlen($chars) - 1)];
    }

    $lockData = [
        'token_sesi' => $tokenSesi,
        'nomor_peserta' => $sesi['nomor_peserta'],
        'nama_lengkap' => $sesi['nama_lengkap'],
        'unlock_token' => $unlockToken,
        'waktu_terkunci' => date('Y-m-d H:i:s'),
        'alasan' => $alasan
    ];

    $lockFile = dirname(__DIR__) . '/database/active_lock.json';
    file_put_contents($lockFile, json_encode($lockData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Cetak ke log output server CMD
    $logMsg = "\n" . str_repeat("=", 55) . "\n" .
              "  [PERINGATAN KEAMANAN CBT - PESERTA TERKUNCI!]\n" .
              "  Peserta   : {$sesi['nomor_peserta']} - {$sesi['nama_lengkap']}\n" .
              "  Alasan    : {$alasan}\n" .
              "  Waktu     : " . date('H:i:s d/m/Y') . "\n" .
              "  ---------------------------------------------------\n" .
              "  >>> TOKEN BUKA KUNCI PENGAWAS: [ {$unlockToken} ] <<<\n" .
              str_repeat("=", 55) . "\n";

    error_log($logMsg);

    // Tulis juga ke file teks simpel untuk dibaca batch script CMD
    $txtFile = dirname(__DIR__) . '/database/TOKEN_PENGAWAS.txt';
    file_put_contents($txtFile, "========================================================\n" .
                                "   TOKEN BUKA KUNCI PENGAWAS CBT: [ $unlockToken ]\n" .
                                "   Peserta : {$sesi['nomor_peserta']} ({$sesi['nama_lengkap']})\n" .
                                "   Waktu   : " . date('H:i:s d/m/Y') . "\n" .
                                "========================================================\n");

    echo json_encode([
        'success' => true,
        'is_locked' => true,
        'waktu_terkunci' => $lockData['waktu_terkunci']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
