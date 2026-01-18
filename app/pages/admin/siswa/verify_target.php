<?php
// app/pages/admin/siswa/verify_target.php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$target_id = $input['target_id'] ?? '';
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (empty($target_id) || empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username dan Password target wajib diisi.']);
    exit;
}

// Fetch the TARGET siswa
$stmt = $pdo->prepare("SELECT * FROM tb_siswa WHERE id = ?");
$stmt->execute([$target_id]);
$target = $stmt->fetch();

if (!$target) {
    echo json_encode(['success' => false, 'message' => 'Data siswa tidak ditemukan.']);
    exit;
}

// Verify provided username matches the target's username
if ($username !== $target['username']) {
    echo json_encode(['success' => false, 'message' => 'Username tidak cocok dengan data siswa yang dipilih.']);
    exit;
}

// Verify Password
if (password_verify($password, $target['password'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Password salah.']);
}
?>
