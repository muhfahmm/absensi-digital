<?php
header('Content-Type: application/json');
require_once '../../../../config/database.php';

$user_id = $_GET['user_id'] ?? null;
$role = $_GET['role'] ?? 'siswa';

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User ID required']);
    exit;
}

try {
    // Get Saldo
    $stmt = $pdo->prepare("SELECT saldo_saat_ini FROM tb_saldo WHERE user_id = ? AND role = ?");
    $stmt->execute([$user_id, $role]);
    $saldo = $stmt->fetchColumn();

    // Get History
    $stmt = $pdo->prepare("SELECT * FROM tb_riwayat_saldo WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'saldo' => $saldo ? (float)$saldo : 0,
        'history' => $history
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
