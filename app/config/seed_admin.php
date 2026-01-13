<?php
// app/config/seed_admin.php
require_once 'database.php';

try {
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $nama = 'Administrator';

    // Cek apakah admin sudah ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_admin WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo "Admin default sudah ada.\n";
    } else {
        $sql = "INSERT INTO tb_admin (username, password, nama_lengkap) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password, $nama]);
        echo "Admin default berhasil dibuat!\nUsername: admin\nPassword: admin123\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
