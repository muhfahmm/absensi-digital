<?php
// app/api/komentar.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // GET COMMENTS
    // Param: materi_id
    $materi_id = $_GET['materi_id'] ?? null;

    if (!$materi_id) {
        echo json_encode(['success' => false, 'message' => 'materi_id required']);
        exit;
    }

    try {
        // Fetch ALL comments for this materi_id, ordered by created_at
        // We will build the tree on the client side (React Native)
        $sql = "
            SELECT k.*, 
                CASE 
                    WHEN k.role = 'siswa' THEN s.nama_lengkap 
                    WHEN k.role = 'guru' THEN g.nama_lengkap 
                    WHEN k.role = 'admin' THEN a.nama_lengkap 
                END as nama_user,
                CASE 
                    WHEN k.role = 'siswa' THEN s.foto_profil 
                    WHEN k.role = 'guru' THEN g.foto_profil 
                    WHEN k.role = 'admin' THEN a.foto_profil 
                END as foto_profil
            FROM tb_komentar_elearning k
            LEFT JOIN tb_siswa s ON k.user_id = s.id AND k.role = 'siswa'
            LEFT JOIN tb_guru g ON k.user_id = g.id AND k.role = 'guru'
            LEFT JOIN tb_admin a ON k.user_id = a.id AND k.role = 'admin'
            WHERE k.materi_id = ?
            ORDER BY k.created_at ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$materi_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $comments]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($method === 'POST') {
    // POST COMMENT
    // Body: materi_id, user_id, role, komentar
    $data = json_decode(file_get_contents("php://input"), true);

    $materi_id = $data['materi_id'] ?? null;
    $parent_id = $data['parent_id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $role = $data['role'] ?? null;
    $komentar = $data['komentar'] ?? null;

    if (!$materi_id || !$user_id || !$role || !$komentar) {
        echo json_encode(['success' => false, 'message' => 'Incomplete data']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO tb_komentar_elearning (materi_id, parent_id, user_id, role, komentar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$materi_id, $parent_id, $user_id, $role, $komentar]);

        echo json_encode(['success' => true, 'message' => 'Comment posted']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($method === 'PUT') {
    // UPDATE COMMENT
    // Body: id, user_id, role, komentar
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $role = $data['role'] ?? null;
    $komentar = $data['komentar'] ?? null;

    if (!$id || !$user_id || !$role || !$komentar) {
        echo json_encode(['success' => false, 'message' => 'Incomplete data']);
        exit;
    }

    try {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT * FROM tb_komentar_elearning WHERE id = ? AND user_id = ? AND role = ?");
        $stmt->execute([$id, $user_id, $role]);
        $comment = $stmt->fetch();

        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or comment not found']);
            exit;
        }

        // Update comment
        $stmt = $pdo->prepare("UPDATE tb_komentar_elearning SET komentar = ? WHERE id = ?");
        $stmt->execute([$komentar, $id]);

        echo json_encode(['success' => true, 'message' => 'Comment updated']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($method === 'DELETE') {
    // DELETE COMMENT
    // Param: id, user_id, role
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $role = $data['role'] ?? null;

    if (!$id || !$user_id || !$role) {
        echo json_encode(['success' => false, 'message' => 'Incomplete data']);
        exit;
    }

    try {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT * FROM tb_komentar_elearning WHERE id = ? AND user_id = ? AND role = ?");
        $stmt->execute([$id, $user_id, $role]);
        $comment = $stmt->fetch();

        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or comment not found']);
            exit;
        }

        // Delete comment
        $stmt = $pdo->prepare("DELETE FROM tb_komentar_elearning WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Comment deleted']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
