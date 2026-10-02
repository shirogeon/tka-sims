<?php
// api/submit_exam.php - Finalisasi Ujian dan Perhitungan Skor Otomatis
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
require_once dirname(__DIR__) . '/config/database.php';

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?: $_POST;

    $token = trim($data['token'] ?? '');
    if (empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Token sesi tidak valid']);
        exit;
    }

    $pdo = getDatabaseConnection();

    $stmtSesi = $pdo->prepare("SELECT s.*, p.id AS paket_id FROM sesi_ujian s JOIN paket_ujian p ON s.paket_id = p.id WHERE s.token_sesi = ?");
    $stmtSesi->execute([$token]);
    $sesi = $stmtSesi->fetch();

    if (!$sesi) {
        echo json_encode(['success' => false, 'message' => 'Sesi tidak ditemukan']);
        exit;
    }

    $paketId = (int)$sesi['paket_id'];
    $jawabanTersimpan = json_decode($sesi['jawaban_tersimpan'] ?? '{}', true) ?: [];

    // Ambil seluruh soal untuk evaluasi nilai
    $stmtSoal = $pdo->prepare("SELECT id, nomor_urut, tipe_soal, kunci_jawaban, bobot FROM soal WHERE paket_id = ? ORDER BY nomor_urut ASC");
    $stmtSoal->execute([$paketId]);
    $soalList = $stmtSoal->fetchAll();

    $totalBobotMaksimal = 0;
    $totalSkorDiperoleh = 0;
    $detailKoreksi = [];

    foreach ($soalList as $s) {
        $no = (int)$s['nomor_urut'];
        $tipe = $s['tipe_soal'];
        $bobot = (float)($s['bobot'] ?: 1.0);
        $totalBobotMaksimal += $bobot;

        $kunci = json_decode($s['kunci_jawaban'], true);
        $userObj = $jawabanTersimpan[$no] ?? null;
        $userAns = $userObj['jawaban'] ?? null;

        $skorSoal = 0.0;
        $isCorrect = false;

        if ($userAns !== null && $userAns !== '') {
            switch ($tipe) {
                case 'pg_tunggal':
                    if (strtoupper((string)$userAns) === strtoupper((string)$kunci)) {
                        $skorSoal = $bobot;
                        $isCorrect = true;
                    }
                    break;

                case 'pg_kompleks':
                    if (is_array($userAns) && is_array($kunci)) {
                        $cleanUser = array_map('strtoupper', $userAns);
                        $cleanKunci = array_map('strtoupper', $kunci);
                        sort($cleanUser);
                        sort($cleanKunci);
                        if ($cleanUser === $cleanKunci) {
                            $skorSoal = $bobot;
                            $isCorrect = true;
                        } else {
                            // Partial score: hitung irisan yang tepat dikurangi tebakan salah
                            $intersect = count(array_intersect($cleanUser, $cleanKunci));
                            $wrongGuesses = count(array_diff($cleanUser, $cleanKunci));
                            $ratio = max(0, ($intersect - $wrongGuesses) / count($cleanKunci));
                            $skorSoal = round($bobot * $ratio, 2);
                        }
                    }
                    break;

                case 'matriks_bs':
                    if (is_array($userAns) && is_array($kunci)) {
                        $matchCount = 0;
                        $totalRows = count($kunci);
                        foreach ($kunci as $rowId => $expectedVal) {
                            if (isset($userAns[$rowId]) && strcasecmp((string)$userAns[$rowId], (string)$expectedVal) === 0) {
                                $matchCount++;
                            }
                        }
                        $skorSoal = round($bobot * ($matchCount / max(1, $totalRows)), 2);
                        if ($matchCount === $totalRows) {
                            $isCorrect = true;
                        }
                    }
                    break;

                case 'menjodohkan':
                    if (is_array($userAns) && is_array($kunci)) {
                        $matchCount = 0;
                        $totalPairs = count($kunci);
                        foreach ($kunci as $premisId => $targetExpected) {
                            if (isset($userAns[$premisId]) && $userAns[$premisId] === $targetExpected) {
                                $matchCount++;
                            }
                        }
                        $skorSoal = round($bobot * ($matchCount / max(1, $totalPairs)), 2);
                        if ($matchCount === $totalPairs) {
                            $isCorrect = true;
                        }
                    }
                    break;

                case 'isian_singkat':
                    $userClean = trim(str_replace(',', '.', (string)$userAns));
                    if (is_array($kunci)) {
                        foreach ($kunci as $k) {
                            $kClean = trim(str_replace(',', '.', (string)$k));
                            if (strcasecmp($userClean, $kClean) === 0) {
                                $skorSoal = $bobot;
                                $isCorrect = true;
                                break;
                            }
                        }
                    } else {
                        $kClean = trim(str_replace(',', '.', (string)$kunci));
                        if (strcasecmp($userClean, $kClean) === 0) {
                            $skorSoal = $bobot;
                            $isCorrect = true;
                        }
                    }
                    break;
            }
        }

        $totalSkorDiperoleh += $skorSoal;
        $detailKoreksi[$no] = [
            'skor' => $skorSoal,
            'is_correct' => $isCorrect
        ];
    }

    $nilaiSkala100 = ($totalBobotMaksimal > 0) ? round(($totalSkorDiperoleh / $totalBobotMaksimal) * 100, 1) : 0;

    $waktuSubmit = date('Y-m-d H:i:s');
    $stmtUpdate = $pdo->prepare("UPDATE sesi_ujian SET status = 'selesai', skor_akhir = ?, waktu_submit = ? WHERE id = ?");
    $stmtUpdate->execute([$nilaiSkala100, $waktuSubmit, $sesi['id']]);

    echo json_encode([
        'success' => true,
        'nilai_akhir' => $nilaiSkala100,
        'redirect' => 'hasil.php?token=' . urlencode($token)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
