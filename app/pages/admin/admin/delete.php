<?php
// app/pages/admin/admin/delete.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

$id = $_GET['id'] ?? null;

if ($id) {
    // Prevent deleting self? (Optional safety)
    // if ($id == $_SESSION['user_id']) { ... }

    try {
        // Hapus foto jika ada
        $stmt_foto = $pdo->prepare("SELECT foto_profil FROM tb_admin WHERE id = ?");
        $stmt_foto->execute([$id]);
        $data = $stmt_foto->fetch();
        
        if ($data && !empty($data['foto_profil'])) {
            $path = "../../../../uploads/admin/" . $data['foto_profil'];
            if (file_exists($path)) unlink($path);
        }

        $stmt = $pdo->prepare("DELETE FROM tb_admin WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "<script>alert('Data Admin Berhasil Dihapus!'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        $msg = "Gagal menghapus: " . $e->getMessage();
        echo "<script>alert('$msg'); window.location.href='index.php';</script>";
    }
} else {
    redirect('app/pages/admin/admin/index.php');
}
