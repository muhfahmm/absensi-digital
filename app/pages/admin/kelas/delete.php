<?php
// app/pages/admin/kelas/delete.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_kelas WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "<script>alert('Kelas Berhasil Dihapus!'); window.location.href='index.php';</script>";
        // redirect('app/pages/admin/kelas/index.php'); // Alternatif jika tanpa alert
    } catch (PDOException $e) {
        $msg = "Gagal menghapus: " . $e->getMessage();
        // Cek foreign key constraint (misal ada siswa di kelas ini)
        if ($e->getCode() == 23000) {
            $msg = "Gagal: Kelas ini masih memiliki data Siswa. Hapus siswa terkait terlebih dahulu.";
        }
        echo "<script>alert('$msg'); window.location.href='index.php';</script>";
    }
} else {
    redirect('app/pages/admin/kelas/index.php');
}
