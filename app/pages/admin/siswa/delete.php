<?php
// app/pages/admin/siswa/delete.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Hapus foto fisik file jika ada
        $stmt = $pdo->prepare("SELECT foto FROM tb_siswa WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if ($row && !empty($row['foto'])) {
            $file = "../../../../uploads/siswa/" . $row['foto'];
            if (file_exists($file)) unlink($file);
        }

        $stmt = $pdo->prepare("DELETE FROM tb_siswa WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "<script>alert('Data Siswa Berhasil Dihapus!'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        $msg = "Gagal menghapus: " . $e->getMessage();
        echo "<script>alert('$msg'); window.location.href='index.php';</script>";
    }
} else {
    redirect('app/pages/admin/siswa/index.php');
}
