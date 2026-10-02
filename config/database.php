<?php
// config/database.php - Koneksi Database Cerdas (MySQL dengan Fallback ke SQLite)

function getDatabaseConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbType = 'sqlite';
    $mysqlHost = '127.0.0.1';
    $mysqlUser = 'root';
    $mysqlPass = '';
    $mysqlDb = 'cbt_pusmendik';

    // 1. Coba koneksi ke MySQL terlebih dahulu
    try {
        $tempPdo = new PDO("mysql:host=$mysqlHost;charset=utf8mb4", $mysqlUser, $mysqlPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 1
        ]);
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$mysqlDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        $pdo = new PDO("mysql:host=$mysqlHost;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $dbType = 'mysql';
    } catch (Exception $e) {
        // 2. Jika MySQL XAMPP belum dijalankan, gunakan SQLite otomatis
        $sqlitePath = dirname(__DIR__) . '/database/cbt_tka.sqlite';
        $sqliteDir = dirname($sqlitePath);
        if (!is_dir($sqliteDir)) {
            mkdir($sqliteDir, 0777, true);
        }
        $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $pdo->exec("PRAGMA foreign_keys = ON;");
        $dbType = 'sqlite';
    }

    // Pastikan tabel-tabel dan data awal tersedia
    ensureTablesAndSeed($pdo, $dbType);

    return $pdo;
}

function ensureTablesAndSeed(PDO $pdo, string $dbType) {
    // Cek apakah tabel paket_ujian sudah ada
    try {
        $check = $pdo->query("SELECT 1 FROM paket_ujian LIMIT 1");
        if ($check !== false) {
            return; // Tabel sudah ada dan siap dipakai
        }
    } catch (Exception $e) {
        // Tabel belum ada, lanjutkan inisialisasi
    }

    if ($dbType === 'mysql') {
        $queries = [
            "CREATE TABLE IF NOT EXISTS paket_ujian (
                id INT AUTO_INCREMENT PRIMARY KEY,
                kode VARCHAR(50) UNIQUE NOT NULL,
                judul VARCHAR(255) NOT NULL,
                kategori VARCHAR(100) NOT NULL,
                jenjang VARCHAR(50) NOT NULL,
                durasi_menit INT NOT NULL DEFAULT 45,
                petunjuk TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;",

            "CREATE TABLE IF NOT EXISTS stimulus (
                id INT AUTO_INCREMENT PRIMARY KEY,
                kode VARCHAR(50) UNIQUE NOT NULL,
                judul VARCHAR(255) NOT NULL,
                konten_html LONGTEXT NOT NULL,
                sumber VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;",

            "CREATE TABLE IF NOT EXISTS soal (
                id INT AUTO_INCREMENT PRIMARY KEY,
                paket_id INT NOT NULL,
                stimulus_id INT NULL,
                nomor_urut INT NOT NULL,
                subtes VARCHAR(100) NOT NULL,
                tipe_soal ENUM('pg_tunggal', 'pg_kompleks', 'matriks_bs', 'menjodohkan', 'isian_singkat') NOT NULL,
                pertanyaan LONGTEXT NOT NULL,
                opsi_jawaban LONGTEXT NULL,
                kunci_jawaban LONGTEXT NOT NULL,
                pembahasan LONGTEXT NULL,
                bobot FLOAT DEFAULT 1.0,
                FOREIGN KEY (paket_id) REFERENCES paket_ujian(id) ON DELETE CASCADE,
                FOREIGN KEY (stimulus_id) REFERENCES stimulus(id) ON DELETE SET NULL
            ) ENGINE=InnoDB;",

            "CREATE TABLE IF NOT EXISTS peserta (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nomor_peserta VARCHAR(50) UNIQUE NOT NULL,
                nama_lengkap VARCHAR(150) NOT NULL,
                sekolah_asal VARCHAR(150) DEFAULT 'SMA/SMK Sederajat',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;",

            "CREATE TABLE IF NOT EXISTS sesi_ujian (
                id INT AUTO_INCREMENT PRIMARY KEY,
                peserta_id INT NOT NULL,
                paket_id INT NOT NULL,
                token_sesi VARCHAR(64) UNIQUE NOT NULL,
                waktu_mulai DATETIME NOT NULL,
                waktu_selesai_target DATETIME NOT NULL,
                waktu_submit DATETIME NULL,
                status ENUM('berlangsung', 'selesai') DEFAULT 'berlangsung',
                skor_akhir FLOAT DEFAULT 0,
                jawaban_tersimpan LONGTEXT NULL,
                FOREIGN KEY (peserta_id) REFERENCES peserta(id) ON DELETE CASCADE,
                FOREIGN KEY (paket_id) REFERENCES paket_ujian(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;"
        ];
    } else {
        // SQLite Syntax
        $queries = [
            "CREATE TABLE IF NOT EXISTS paket_ujian (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kode TEXT UNIQUE NOT NULL,
                judul TEXT NOT NULL,
                kategori TEXT NOT NULL,
                jenjang TEXT NOT NULL,
                durasi_menit INTEGER NOT NULL DEFAULT 45,
                petunjuk TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );",

            "CREATE TABLE IF NOT EXISTS stimulus (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kode TEXT UNIQUE NOT NULL,
                judul TEXT NOT NULL,
                konten_html TEXT NOT NULL,
                sumber TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );",

            "CREATE TABLE IF NOT EXISTS soal (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                paket_id INTEGER NOT NULL,
                stimulus_id INTEGER NULL,
                nomor_urut INTEGER NOT NULL,
                subtes TEXT NOT NULL,
                tipe_soal TEXT NOT NULL,
                pertanyaan TEXT NOT NULL,
                opsi_jawaban TEXT NULL,
                kunci_jawaban TEXT NOT NULL,
                pembahasan TEXT NULL,
                bobot REAL DEFAULT 1.0,
                FOREIGN KEY (paket_id) REFERENCES paket_ujian(id) ON DELETE CASCADE,
                FOREIGN KEY (stimulus_id) REFERENCES stimulus(id) ON DELETE SET NULL
            );",

            "CREATE TABLE IF NOT EXISTS peserta (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nomor_peserta TEXT UNIQUE NOT NULL,
                nama_lengkap TEXT NOT NULL,
                sekolah_asal TEXT DEFAULT 'SMA/SMK Sederajat',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );",

            "CREATE TABLE IF NOT EXISTS sesi_ujian (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                peserta_id INTEGER NOT NULL,
                paket_id INTEGER NOT NULL,
                token_sesi TEXT UNIQUE NOT NULL,
                waktu_mulai DATETIME NOT NULL,
                waktu_selesai_target DATETIME NOT NULL,
                waktu_submit DATETIME NULL,
                status TEXT DEFAULT 'berlangsung',
                skor_akhir REAL DEFAULT 0,
                jawaban_tersimpan TEXT NULL,
                FOREIGN KEY (peserta_id) REFERENCES peserta(id) ON DELETE CASCADE,
                FOREIGN KEY (paket_id) REFERENCES paket_ujian(id) ON DELETE CASCADE
            );"
        ];
    }

    foreach ($queries as $q) {
        $pdo->exec($q);
    }

    // Panggil seeder untuk mengisi paket soal simulasi Pusmendik
    require_once dirname(__DIR__) . '/database/seed_soal.php';
    seedPusmendikData($pdo);
}
