<?php
// app/pages/admin/scanner/process.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$qr_code = $input['qr_code'] ?? '';

if (empty($qr_code)) {
    echo json_encode(['success' => false, 'message' => 'QR Code kosong']);
    exit;
}

try {
    // Check if QR code belongs to siswa
    $stmt = $pdo->prepare("SELECT id, nama_lengkap, 'siswa' as role FROM tb_siswa WHERE kode_qr = ?");
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
        // Jika sudah scan masuk tapi BELUM scan pulang
        if ($existing['jam_keluar'] == null) {
            $stmt = $pdo->prepare("UPDATE tb_absensi SET jam_keluar = ? WHERE id = ?");
            $stmt->execute([$now_time, $existing['id']]);

            echo json_encode([
                'success' => true, 
                'type' => 'pulang',
                'message' => 'Presensi Pulang Diterima',
                'nama' => $user['nama_lengkap'],
                'role' => $user['role'],
                'jam' => $now_time
            ]);
            exit;
        } else {
            // Jika sudah scan masuk DAN sudah scan pulang
            echo json_encode([
                'success' => false, 
                'message' => 'Presensi sudah lengkap hari ini',
                'nama' => $user['nama_lengkap'],
                'role' => $user['role']
            ]);
            exit;
        }
    }
    
    // Insert absensi MASUK
    $status = 'hadir';
    # Aturan Telat: Senin 06:45, lainnya 07:00 (Matching Python Scanner)
    $day_of_week = date('N'); // 1 (Mon) to 7 (Sun)
    $cutoff = ($day_of_week == 1) ? '06:45:00' : '07:00:00';
    
    if (strtotime($now_time) > strtotime($cutoff)) {
        $status = 'terlambat';
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO tb_absensi (user_id, role, tanggal, jam_masuk, status, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user['id'], $user['role'], $today, $now_time, $status]);
    
    echo json_encode([
        'success' => true,
        'type' => 'masuk',
        'nama' => $user['nama_lengkap'],
        'role' => $user['role'],
        'jam' => $now_time,
        'status' => $status
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
