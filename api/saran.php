<?php
/**
 * LAPEMAS PASANGSARI - API Aspirasi / Saran Warga
 * REST Endpoint for Saran (GET, POST, DELETE)
 */

require_once __DIR__ . '/db.php';

$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// GET: Ambil daftar saran
if ($method === 'GET') {
    $limit = isset($_GET['limit']) ? max(1, min(1000, (int)$_GET['limit'])) : 500;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $sql = "SELECT id, nama, kontak, isi_saran, status, created_at 
            FROM saran 
            ORDER BY datetime(created_at) DESC, id DESC 
            LIMIT $limit OFFSET $offset";

    try {
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        sendJson($rows, 200);
    } catch (\Throwable $e) {
        sendJson(['error' => $e->getMessage()], 500);
    }
}

// POST: Simpan saran baru
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        sendJson(['error' => 'Data JSON tidak valid'], 400);
    }

    $nama = trim($data['nama'] ?? '') ?: 'Anonim';
    $kontak = trim($data['kontak'] ?? '-') ?: '-';
    $isi_saran = trim($data['isi_saran'] ?? $data['isi'] ?? '');
    $status = trim($data['status'] ?? 'Baru') ?: 'Baru';
    $created_at = trim($data['created_at'] ?? '') ?: date('c');

    if (mb_strlen($isi_saran) < 5) {
        sendJson(['error' => 'Isi saran minimal 5 karakter'], 422);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO saran (nama, kontak, isi_saran, status, created_at)
            VALUES (:nama, :kontak, :isi_saran, :status, :created_at)
        ");
        $stmt->execute([
            ':nama' => $nama,
            ':kontak' => $kontak,
            ':isi_saran' => $isi_saran,
            ':status' => $status,
            ':created_at' => $created_at
        ]);
        $newId = (int)$pdo->lastInsertId();

        $savedRecord = [
            'id' => $newId,
            'nama' => $nama,
            'kontak' => $kontak,
            'isi_saran' => $isi_saran,
            'status' => $status,
            'created_at' => $created_at
        ];

        sendJson(['success' => true, 'data' => $savedRecord], 201);
    } catch (\Throwable $e) {
        sendJson(['error' => $e->getMessage()], 500);
    }
}

// DELETE: Hapus saran
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
        $stmt = $pdo->prepare("DELETE FROM saran WHERE id = :id");
        $stmt->execute([':id' => $id]);
        sendJson(['success' => true, 'id' => $id], 200);
    } catch (\Throwable $e) {
        sendJson(['error' => $e->getMessage()], 500);
    }
}

sendJson(['error' => 'Method not allowed'], 405);
