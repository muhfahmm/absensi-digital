<?php
// app/api/scan.php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../config/database.php';
date_default_timezone_set('Asia/Jakarta'); // FORCE TIMEZONE

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $qr_code = $input['qr_code'] ?? '';
    $scanner_role = $input['scanner_role'] ?? ''; // 'admin' or 'guru'
    
    if (empty($qr_code)) {
        echo json_encode(['success' => false, 'message' => 'QR Code kosong']);
        exit;
    }
    
    try {
        // Check if QR code belongs to siswa
        $stmt = $pdo->prepare("SELECT id, nama_lengkap, 'siswa' as role, poin FROM tb_siswa WHERE kode_qr = ?");
        $stmt->execute([$qr_code]);
        $user = $stmt->fetch();
        
        // If not siswa, check guru
        if (!$user) {
            $stmt = $pdo->prepare("SELECT id, nama_lengkap, 'guru' as role FROM tb_guru WHERE kode_qr = ?");
            $stmt->execute([$qr_code]);
            $user = $stmt->fetch();
        }
        
        // If not found
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'QR Code tidak terdaftar']);
            exit;
        }
        
        // Check if already present today
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT id, jam_masuk, jam_keluar FROM tb_absensi WHERE user_id = ? AND role = ? AND tanggal = ?");
        $stmt->execute([$user['id'], $user['role'], $today]);
        $existing = $stmt->fetch();
        
        $now_time = date('H:i:s');

        if ($existing) {
            // Logika PULANG
            if ($existing['jam_keluar'] == null) {
                $stmt = $pdo->prepare("UPDATE tb_absensi SET jam_keluar = ? WHERE id = ?");
                $stmt->execute([$now_time, $existing['id']]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Absensi Pulang Berhasil',
                    'data' => [
                        'type' => 'pulang',
                        'nama' => $user['nama_lengkap'],
                        'role' => $user['role'],
                        'jam' => $now_time
                    ]
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Sudah absen masuk & pulang hari ini',
                    'data' => [
                        'nama' => $user['nama_lengkap'],
                        'role' => $user['role']
                    ]
                ]);
                exit;
            }
        }
        
        // Logika MASUK
        $status = 'hadir';
        $day_of_week = date('N'); 
        $cutoff = ($day_of_week == 1) ? '06:45:00' : '07:00:00';
        
        if (strtotime($now_time) > strtotime($cutoff)) {
            $status = 'terlambat';
            // Kurangi poin siswa jika terlambat
            if ($user['role'] == 'siswa') {
                $updPoin = $pdo->prepare("UPDATE tb_siswa SET poin = poin - 1 WHERE id = ?");
                $updPoin->execute([$user['id']]);
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO tb_absensi (user_id, role, tanggal, jam_masuk, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user['id'], $user['role'], $today, $now_time, $status]);

        // RE-FETCH Poin terbaru untuk memastikan data akurat
        $final_poin = $user['poin'];
        if ($user['role'] == 'siswa') {
            $stmtPoin = $pdo->prepare("SELECT poin FROM tb_siswa WHERE id = ?");
            $stmtPoin->execute([$user['id']]);
            $fetched = $stmtPoin->fetch();
            $final_poin = $fetched['poin'];
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Absensi Masuk Berhasil',
            'data' => [
                'type' => 'masuk',
                'nama' => $user['nama_lengkap'],
                'role' => $user['role'],
                'jam' => $now_time,
                'status' => $status,
                'poin' => $final_poin // Gunakan nilai terbaru dari DB
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
