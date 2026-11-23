<?php
/**
 * LAPEMAS PASANGSARI - Database Connection & Initialization
 * SQLite centralized database on server
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function getDb(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0755, true);
    }

    $htaccess = $dataDir . '/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
    }

    $dbFile = $dataDir . '/lapemas.sqlite';
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('PRAGMA journal_mode = WAL;');
    $pdo->exec('PRAGMA synchronous = NORMAL;');
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // Buat tabel laporan
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS laporan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            judul TEXT NOT NULL,
            jabatan TEXT NOT NULL,
            deskripsi TEXT NOT NULL,
            output TEXT DEFAULT '-',
            outcome TEXT DEFAULT '-',
            foto_url TEXT NOT NULL,
            status TEXT DEFAULT 'Diterima',
            created_at TEXT NOT NULL
        );
        CREATE INDEX IF NOT EXISTS idx_laporan_created ON laporan(created_at DESC);
        CREATE INDEX IF NOT EXISTS idx_laporan_jabatan ON laporan(jabatan);
    ");

    // Buat tabel saran
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS saran (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            kontak TEXT DEFAULT '-',
            isi_saran TEXT NOT NULL,
            status TEXT DEFAULT 'Baru',
            created_at TEXT NOT NULL
        );
        CREATE INDEX IF NOT EXISTS idx_saran_created ON saran(created_at DESC);
    ");

    // Auto seed jika database baru dan masih kosong
    $countLaporan = (int)$pdo->query("SELECT COUNT(*) FROM laporan")->fetchColumn();
    if ($countLaporan === 0) {
        seedDatabase($pdo);
    }

    return $pdo;
}

function seedDatabase(PDO $pdo): void {
    $jsonPath = __DIR__ . '/../canva_all_records.json';
    if (!file_exists($jsonPath)) {
        return;
    }

    $raw = @file_get_contents($jsonPath);
    if (!$raw) return;

    $items = json_decode($raw, true);
    if (!is_array($items)) return;

    $pdo->beginTransaction();
    try {
        $stmtLap = $pdo->prepare("
            INSERT INTO laporan (judul, jabatan, deskripsi, output, outcome, foto_url, status, created_at)
            VALUES (:judul, :jabatan, :deskripsi, :output, :outcome, :foto_url, :status, :created_at)
        ");

        foreach ($items as $item) {
            $judul = trim($item['judul'] ?? '');
            $jabatan = trim($item['jabatan'] ?? '');
            $deskripsi = trim($item['deskripsi'] ?? '');
            $output = trim($item['output'] ?? '-') ?: '-';
            $outcome = trim($item['outcome'] ?? '-') ?: '-';
            $foto = trim($item['foto_url'] ?? '');
            $status = trim($item['status'] ?? 'Diterima') ?: 'Diterima';
            $created = trim($item['created_at'] ?? $item['timestamp'] ?? '');
            if (!$created) {
                $created = date('c');
            }

            if ($judul && $jabatan) {
                $stmtLap->execute([
                    ':judul' => $judul,
                    ':jabatan' => $jabatan,
                    ':deskripsi' => $deskripsi,
                    ':output' => $output,
                    ':outcome' => $outcome,
                    ':foto_url' => $foto,
                    ':status' => $status,
                    ':created_at' => $created
                ]);
            }
        }

        // Seed 4 saran awal
        $seedSaran = [
            [
                'nama' => 'Karang taruna',
                'kontak' => '-',
                'isi_saran' => 'Banyak yang bermain judol di pasangsari pak, mohon dibimbing pak.',
                'status' => 'Baru',
                'created_at' => '2026-11-04T07:05:22.964Z'
            ],
            [
                'nama' => 'Warga Desa Pasangsari',
                'kontak' => '-',
                'isi_saran' => 'Mohon untuk dimaksimalkan kinerja bagi Ketua RT dan RW. Ketua RT dan RW kurang berperan di Dusun.',
                'status' => 'Baru',
                'created_at' => '2026-10-31T03:56:09.660Z'
            ],
            [
                'nama' => 'Hamba Allah',
                'kontak' => '-',
                'isi_saran' => 'Terimakasih Pak Aan atas penyelenggaraan pilkades yang aman dan tertib, ini menjadi rekor bagi kami di Desa Pasang sari dalam pesta demokrasi yang damai.',
                'status' => 'Baru',
                'created_at' => '2026-10-31T02:45:52.083Z'
            ],
            [
                'nama' => 'Munif',
                'kontak' => '085786752996',
                'isi_saran' => 'Ada oknum buaang sampah di kali',
                'status' => 'Baru',
                'created_at' => '2026-08-04T08:01:31.745Z'
            ]
        ];

        $stmtSar = $pdo->prepare("
            INSERT INTO saran (nama, kontak, isi_saran, status, created_at)
            VALUES (:nama, :kontak, :isi_saran, :status, :created_at)
        ");
        foreach ($seedSaran as $s) {
            $stmtSar->execute([
                ':nama' => $s['nama'],
                ':kontak' => $s['kontak'],
                ':isi_saran' => $s['isi_saran'],
                ':status' => $s['status'],
                ':created_at' => $s['created_at']
            ]);
        }

        $pdo->commit();
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Seed failed: ' . $e->getMessage());
    }
}

function sendJson($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
