<?php
// api/verify_unlock_token.php - Verifikasi Token Pembuka Kunci dari Pengawas
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: $_POST;

    $tokenSesi = trim($data['token'] ?? '');
    $inputTokenPengawas = strtoupper(trim($data['unlock_token'] ?? ''));

    if (empty($tokenSesi) || empty($inputTokenPengawas)) {
        echo json_encode(['success' => false, 'message' => 'Masukkan Token Buka Kunci Pengawas']);
        exit;
    }

    $lockFile = dirname(__DIR__) . '/database/active_lock.json';
    if (!file_exists($lockFile)) {
        // Jika tidak ada file lock aktif, izinkan lanjut
        echo json_encode(['success' => true, 'message' => 'Sesi tidak terkunci']);
        exit;
    }

    $lockData = json_decode(file_get_contents($lockFile), true);
    if (!$lockData || $lockData['token_sesi'] !== $tokenSesi) {
        echo json_encode(['success' => false, 'message' => 'Data kunci sesi tidak cocok']);
        exit;
    }

    if (strtoupper($lockData['unlock_token']) === $inputTokenPengawas) {
        // Hapus lock aktif
        @unlink($lockFile);
        @unlink(dirname(__DIR__) . '/database/TOKEN_PENGAWAS.txt');

        echo json_encode([
            'success' => true,
            'message' => 'Akses ujian berhasil dipulihkan'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Token Pengawas salah! Silakan minta token yang valid dari CMD pengawas.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
