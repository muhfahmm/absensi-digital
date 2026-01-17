<?php
// app/pages/admin/siswa/verify_credential.php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username dan Password wajib diisi.']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE id = ?");
$stmt->execute([$current_user_id]);
$admin = $stmt->fetch();

if (!$admin) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

if ($username !== $admin['username']) {
    echo json_encode(['success' => false, 'message' => 'Username tidak cocok dengan akun Anda saat ini.']);
    exit;
}

// Verify Password
if (password_verify($password, $admin['password'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Password salah.']);
}
?>
