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
    $stmt = $pdo->prepare("SELECT id FROM tb_absensi WHERE user_id = ? AND role = ? AND tanggal = ?");
    $stmt->execute([$user['id'], $user['role'], $today]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo json_encode([
            'success' => false, 
            'message' => 'Sudah absen hari ini',
            'nama' => $user['nama_lengkap'],
            'role' => $user['role']
        ]);
        exit;
    }
    
    // Insert absensi
    $jam_masuk = date('H:i:s');
    $status = 'hadir';
    
    // Check if late (after 07:30)
    if (strtotime($jam_masuk) > strtotime('07:30:00')) {
        $status = 'terlambat';
    }
    
    // Ensure role matches ENUM exactly
    $valid_roles = ['siswa', 'guru', 'karyawan'];
    if (!in_array($user['role'], $valid_roles)) {
        throw new Exception("Invalid role: " . $user['role']);
    }

    $stmt = $pdo->prepare("
        INSERT INTO tb_absensi (user_id, role, tanggal, jam_masuk, status, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user['id'], $user['role'], $today, $jam_masuk, $status]);
    
    echo json_encode([
        'success' => true,
        'nama' => $user['nama_lengkap'],
        'role' => $user['role'],
        'jam_masuk' => $jam_masuk,
        'status' => $status
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
