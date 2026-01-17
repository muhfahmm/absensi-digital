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
        // Ambil Data Guru dulu buat cek username
        $chk = $pdo->prepare("SELECT username FROM tb_guru WHERE id = ?");
        $chk->execute([$id]);
        $g = $chk->fetch();

        // Hapus Riwayat Absensi Terkait
        $stmtAbsen = $pdo->prepare("DELETE FROM tb_absensi WHERE user_id = ? AND role = 'guru'");
        $stmtAbsen->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM tb_guru WHERE id = ?");
        $stmt->execute([$id]);
        
        // Hapus juga dari tb_admin jika ada username yg sama
        if ($g && !empty($g['username'])) {
            $pdo->prepare("DELETE FROM tb_admin WHERE username = ?")->execute([$g['username']]);
        }
        
        echo "<script>alert('Data Guru Berhasil Dihapus!'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        $msg = "Gagal menghapus: " . $e->getMessage();
        echo "<script>alert('$msg'); window.location.href='index.php';</script>";
    }
} else {
    redirect('app/pages/admin/guru/index.php');
}
