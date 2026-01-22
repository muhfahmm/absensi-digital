<?php
// app/api/attendance_history.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__ . '/../../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? '';
    $role = $input['role'] ?? 'siswa';

    if(empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID required']);
        exit;
    }
    
    try {
        // Ambil riwayat absen bulan ini (atau semua riwayat)
        $stmt = $pdo->prepare("
            SELECT * FROM tb_absensi 
            WHERE user_id = ? AND role = ? 
            ORDER BY tanggal DESC, created_at DESC 
            LIMIT 30
        ");
        $stmt->execute([$user_id, $role]);
        $history = $stmt->fetchAll();

        // Hitung ringkasan
        $stmt_summary = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa
            FROM tb_absensi 
            WHERE user_id = ? AND role = ? AND MONTH(tanggal) = MONTH(CURRENT_DATE())
        ");
        $stmt_summary->execute([$user_id, $role]);
        $summary = $stmt_summary->fetch();

        echo json_encode([
            'success' => true,
            'data' => [
                'history' => $history,
                'summary' => $summary
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
