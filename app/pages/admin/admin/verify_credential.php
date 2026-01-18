<?php
// app/pages/admin/admin/verify_credential.php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
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

// Verify against the *current logged in user* or the user they provide?
// Prompt says: "username dan password guru yang bersangkutan" (implies target OR the one involved).
// Usually, we verify the credentials of the user *performing* the action (sudo mode).
// BUT, if the user specifically asked for "guru yang bersangkutan" (the teacher involved/linked), 
// it might mean they want to ensure the admin knows the credential of whom they are editing. 
// However, I will strictly follow "sudo mode" (verify current user) as it is the standard and logical interpretation 
// for "security check before deleting someone else". 
// Wait, if I delete *someone else*, why would I input *their* password? I definitely don't know it.
// If I am Admin A, deleting Admin B, I input Admin A (my) 's password to confirm.
// Let's assume the prompt implies "Verification of the executor".
// However, the wording "guru yang bersangkutan" is tricky. Could it mean the "teacher account" in `tb_admin`?
// Let's stick to: Verify the credentials provided against `tb_admin`.
// If the provided credentials match ANY admin in the system who has permission, it's okay? 
// No, it must match the CURRENT session user.

$current_user_id = $_SESSION['admin_id'];

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE id = ?");
$stmt->execute([$current_user_id]);
$admin = $stmt->fetch();

if (!$admin) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// Check if provided username matches current user
// (Optional: Allow them to just input password if we assume username, but user said "input username and password")
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
