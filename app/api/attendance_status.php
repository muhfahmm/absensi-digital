<?php
// app/api/attendance_status.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? '';
    $role = $input['role'] ?? 'siswa';

    if(empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        exit;
    }
    
    try {
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT * FROM tb_absensi WHERE user_id = ? AND role = ? AND tanggal = ?");
        $stmt->execute([$user_id, $role, $today]);
        $absensi = $stmt->fetch();

        if ($absensi) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'status' => $absensi['status'],
                    'jam_masuk' => $absensi['jam_masuk'],
                    'jam_keluar' => $absensi['jam_keluar'],
                    'tanggal' => $absensi['tanggal']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'data' => null
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
