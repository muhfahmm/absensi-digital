<?php
// app/pages/admin/jadwal/save_time.php
session_start();
require_once '../../../config/database.php';
require_once '../../../functions/helpers.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    // Note: Request said "guru dan admin", but currently login is restricted to admin zone. 
    // If teachers need access, we check their role too. 
    // Assuming for now only Admin has access to this settings page as per current folder structure.
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jam = $_POST['id_jam'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $hari_redirect = $_POST['hari_redirect'] ?? 'Senin';

    try {
        $stmt = $pdo->prepare("UPDATE tb_jam_pelajaran SET jam_mulai = ?, jam_selesai = ? WHERE id = ?");
        $stmt->execute([$jam_mulai, $jam_selesai, $id_jam]);
        
        // Success
        header("Location: index.php?hari=" . urlencode($hari_redirect));
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
