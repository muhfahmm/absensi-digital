<?php
// app/pages/admin/jadwal/save_jadwal.php
session_start();
require_once '../../../config/database.php';
require_once '../../../functions/helpers.php'; // For redirect() if exists, or use header

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hari = $_POST['hari'];
    $id_jam = $_POST['id_jam'];
    $id_kelas = $_POST['id_kelas'];
    $id_jadwal = $_POST['id_jadwal'] ?? '';
    
    $id_mapel = $_POST['id_mapel'] ?? null;
    $id_guru = $_POST['id_guru'] ?? null;
    $delete = $_POST['delete_schedule'] ?? 0;

    // Validation
    if ($delete != 1) {
        if (empty($id_mapel) || empty($id_guru)) {
            // Use javascript alert and gobacck
            echo "<script>alert('Mata Pelajaran dan Guru harus dipilih!'); window.history.back();</script>";
            exit;
        }
    }

    try {
        if ($delete == 1) {
            // Delete existing
            if ($id_jadwal) {
                $stmt = $pdo->prepare("DELETE FROM tb_jadwal_pelajaran WHERE id = ?");
                $stmt->execute([$id_jadwal]);
            }
        } else {
            // Insert or Update
            if ($id_jadwal) {
                // Update
                $stmt = $pdo->prepare("UPDATE tb_jadwal_pelajaran SET id_mapel = ?, id_guru = ? WHERE id = ?");
                $stmt->execute([$id_mapel, $id_guru, $id_jadwal]);
            } else {
                // Insert New
                // Check duplicate? Ideally table constraint.
                $stmt = $pdo->prepare("INSERT INTO tb_jadwal_pelajaran (hari, id_jam, id_kelas, id_mapel, id_guru) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$hari, $id_jam, $id_kelas, $id_mapel, $id_guru]);
            }
        }
        
        // Success
        header("Location: index.php?hari=" . urlencode($hari));
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        // Ideally show error gracefully
    }
}
?>
