<?php
// app/api/toggle_comment.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$materi_id = $data['materi_id'] ?? null;
$user_id = $data['user_id'] ?? null; // ID of the specific teacher
$status = $data['status'] ?? null; // 1 (enable) or 0 (disable)

if (!$materi_id || !$user_id || !isset($status)) {
    echo json_encode(['success' => false, 'message' => 'Incomplete data']);
    exit;
}

try {
    // Verify ownership: Ensure this teacher owns the material
    // Assuming table 'tb_materi' has 'id_guru'
    $checkSql = "SELECT id FROM tb_materi WHERE id = ? AND id_guru = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$materi_id, $user_id]);
    
    if ($checkStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized or Material not found']);
        exit;
    }

    // Update status
    $sql = "UPDATE tb_materi SET is_comment_enabled = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status, $materi_id]);

    echo json_encode(['success' => true, 'message' => 'Comment status updated']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
