<?php
// app/pages/admin/guru/delete.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_guru WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "<script>alert('Data Guru Berhasil Dihapus!'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        $msg = "Gagal menghapus: " . $e->getMessage();
        echo "<script>alert('$msg'); window.location.href='index.php';</script>";
    }
} else {
    redirect('app/pages/admin/guru/index.php');
}
