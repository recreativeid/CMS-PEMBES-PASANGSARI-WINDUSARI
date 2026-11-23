<?php
/**
 * LAPEMAS PASANGSARI - API Laporan Kegiatan
 * REST Endpoint for Laporan (GET, POST, DELETE)
 */

require_once __DIR__ . '/db.php';

$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// GET: Ambil daftar laporan
if ($method === 'GET') {
    $limit = isset($_GET['limit']) ? max(1, min(10000, (int)$_GET['limit'])) : 5000;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
    $jabatan = isset($_GET['jabatan']) ? trim($_GET['jabatan']) : '';
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

    $where = [];
    $params = [];

    if ($jabatan !== '' && $jabatan !== 'all' && $jabatan !== 'Semua Jabatan') {
        $where[] = 'jabatan = :jabatan';
        $params[':jabatan'] = $jabatan;
    }

    if ($q !== '') {
        $where[] = '(judul LIKE :q OR deskripsi LIKE :q OR output LIKE :q OR outcome LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }

    $whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT id, judul, jabatan, deskripsi, output, outcome, foto_url, status, created_at 
            FROM laporan 
            $whereSql 
            ORDER BY datetime(created_at) DESC, id DESC 
            LIMIT $limit OFFSET $offset";

    try {
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        sendJson($rows, 200);
    } catch (\Throwable $e) {
        sendJson(['error' => $e->getMessage()], 500);
    }
}

// POST: Simpan laporan baru (bisa single object atau batch array)
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        sendJson(['error' => 'Data JSON tidak valid'], 400);
    }

    // Jika input adalah batch array
    if (isset($data[0]) && is_array($data[0])) {
        $inserted = [];
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO laporan (judul, jabatan, deskripsi, output, outcome, foto_url, status, created_at)
                VALUES (:judul, :jabatan, :deskripsi, :output, :outcome, :foto_url, :status, :created_at)
            ");

            foreach ($data as $item) {
                $judul = trim($item['judul'] ?? '');
                $jabatan = trim($item['jabatan'] ?? '');
                $deskripsi = trim($item['deskripsi'] ?? '');
                $output = trim($item['output'] ?? '-') ?: '-';
                $outcome = trim($item['outcome'] ?? '-') ?: '-';
                $foto_url = trim($item['foto_url'] ?? '');
                $status = trim($item['status'] ?? 'Diterima') ?: 'Diterima';
                $created_at = trim($item['created_at'] ?? '') ?: date('c');

                if ($judul && $jabatan) {
                    $stmt->execute([
                        ':judul' => $judul,
                        ':jabatan' => $jabatan,
                        ':deskripsi' => $deskripsi,
                        ':output' => $output,
                        ':outcome' => $outcome,
                        ':foto_url' => $foto_url,
                        ':status' => $status,
                        ':created_at' => $created_at
                    ]);
                    $newId = (int)$pdo->lastInsertId();
                    $item['id'] = $newId;
                    $inserted[] = $item;
                }
            }
            $pdo->commit();
            sendJson(['success' => true, 'count' => count($inserted), 'data' => $inserted], 201);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            sendJson(['error' => $e->getMessage()], 500);
        }
    }

    // Single item
    $judul = trim($data['judul'] ?? '');
    $jabatan = trim($data['jabatan'] ?? '');
    $deskripsi = trim($data['deskripsi'] ?? '');
    $output = trim($data['output'] ?? '-') ?: '-';
    $outcome = trim($data['outcome'] ?? '-') ?: '-';
    $foto_url = trim($data['foto_url'] ?? '');
    $status = trim($data['status'] ?? 'Diterima') ?: 'Diterima';
    $created_at = trim($data['created_at'] ?? '') ?: date('c');

    if (!$judul || !$jabatan) {
        sendJson(['error' => 'Judul dan Jabatan wajib diisi'], 422);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO laporan (judul, jabatan, deskripsi, output, outcome, foto_url, status, created_at)
            VALUES (:judul, :jabatan, :deskripsi, :output, :outcome, :foto_url, :status, :created_at)
        ");
        $stmt->execute([
            ':judul' => $judul,
            ':jabatan' => $jabatan,
            ':deskripsi' => $deskripsi,
            ':output' => $output,
            ':outcome' => $outcome,
            ':foto_url' => $foto_url,
            ':status' => $status,
            ':created_at' => $created_at
        ]);
        $newId = (int)$pdo->lastInsertId();

        $savedRecord = [
            'id' => $newId,
            'judul' => $judul,
            'jabatan' => $jabatan,
            'deskripsi' => $deskripsi,
            'output' => $output,
            'outcome' => $outcome,
            'foto_url' => $foto_url,
            'status' => $status,
            'created_at' => $created_at
        ];

        sendJson(['success' => true, 'data' => $savedRecord], 201);
    } catch (\Throwable $e) {
        sendJson(['error' => $e->getMessage()], 500);
    }
}

// DELETE: Hapus laporan
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;
    }

    if ($id <= 0) {
        sendJson(['error' => 'ID tidak valid'], 422);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM laporan WHERE id = :id");
        $stmt->execute([':id' => $id]);
        sendJson(['success' => true, 'id' => $id], 200);
    } catch (\Throwable $e) {
        sendJson(['error' => $e->getMessage()], 500);
    }
}

sendJson(['error' => 'Method not allowed'], 405);
