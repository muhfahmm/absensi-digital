<?php
// app/pages/admin/guru/delete_mapel.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_mata_pelajaran WHERE id = ?");
        $stmt->execute([$id]);
        
        echo "<script>alert('Mata Pelajaran Berhasil Dihapus!'); window.location.href='index.php?view=mapel';</script>";
    } catch (PDOException $e) {
        $msg = "Gagal menghapus: " . $e->getMessage();
        // Handle foreign key constraints if needed
        if ($e->getCode() == 23000) {
            $msg = "Gagal menghapus! Mata pelajaran sedang digunakan oleh Guru atau Jadwal.";
        }
        echo "<script>alert('$msg'); window.location.href='index.php?view=mapel';</script>";
    }
} else {
    redirect('app/pages/admin/guru/index.php?view=mapel');
}
