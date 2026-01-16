<?php
// app/pages/admin/scanner/recent.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['masuk' => [], 'pulang' => []]);
    exit;
}

try {
    $today = date('Y-m-d');
    
    // 1. Get Latest MASUK (Limit 20)
    $stmtMasuk = $pdo->prepare("
        SELECT 
            tb_absensi.id, tb_absensi.user_id, tb_absensi.role, tb_absensi.jam_masuk, tb_absensi.jam_keluar, tb_absensi.status,
            CASE 
                WHEN tb_absensi.role = 'siswa' THEN tb_siswa.nama_lengkap
                WHEN tb_absensi.role = 'guru' THEN tb_guru.nama_lengkap
            END as nama
        FROM tb_absensi
        LEFT JOIN tb_siswa ON tb_absensi.user_id = tb_siswa.id AND tb_absensi.role = 'siswa'
        LEFT JOIN tb_guru ON tb_absensi.user_id = tb_guru.id AND tb_absensi.role = 'guru'
        WHERE tb_absensi.tanggal = ? AND tb_absensi.jam_masuk IS NOT NULL
        ORDER BY tb_absensi.jam_masuk DESC
        LIMIT 20
    ");
    $stmtMasuk->execute([$today]);
    $masuk = $stmtMasuk->fetchAll(PDO::FETCH_ASSOC);

    // Format Masuk
    $dataMasuk = [];
    foreach ($masuk as $row) {
        $dataMasuk[] = [
            'nama' => $row['nama'],
            'role' => ucfirst($row['role']),
            'status' => ucfirst($row['status']),
            'waktu' => date('H:i', strtotime($row['jam_masuk'])),
            'jam_masuk' => date('H:i', strtotime($row['jam_masuk'])),
            'jam_keluar' => $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-',
            'type' => 'Masuk'
        ];
    }

    // 2. Get Latest PULANG (Limit 20)
    $stmtPulang = $pdo->prepare("
        SELECT 
            tb_absensi.id, tb_absensi.user_id, tb_absensi.role, tb_absensi.jam_masuk, tb_absensi.jam_keluar, tb_absensi.status,
            CASE 
                WHEN tb_absensi.role = 'siswa' THEN tb_siswa.nama_lengkap
                WHEN tb_absensi.role = 'guru' THEN tb_guru.nama_lengkap
            END as nama
        FROM tb_absensi
        LEFT JOIN tb_siswa ON tb_absensi.user_id = tb_siswa.id AND tb_absensi.role = 'siswa'
        LEFT JOIN tb_guru ON tb_absensi.user_id = tb_guru.id AND tb_absensi.role = 'guru'
        WHERE tb_absensi.tanggal = ? AND tb_absensi.jam_keluar IS NOT NULL
        ORDER BY tb_absensi.jam_keluar DESC
        LIMIT 20
    ");
    $stmtPulang->execute([$today]);
    $pulang = $stmtPulang->fetchAll(PDO::FETCH_ASSOC);

    // Format Pulang
    $dataPulang = [];
    foreach ($pulang as $row) {
        $dataPulang[] = [
            'nama' => $row['nama'],
            'role' => ucfirst($row['role']),
            'status' => 'Selesai',
            'waktu' => date('H:i', strtotime($row['jam_keluar'])),
            'jam_masuk' => $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-',
            'jam_keluar' => date('H:i', strtotime($row['jam_keluar'])),
            'type' => 'Pulang'
        ];
    }
    
    echo json_encode([
        'masuk' => $dataMasuk,
        'pulang' => $dataPulang
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['masuk' => [], 'pulang' => [], 'error' => $e->getMessage()]);
}
?>
