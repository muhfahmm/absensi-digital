<?php
// app/pages/admin/scanner/recent.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([]);
    exit;
}

try {
    $today = date('Y-m-d');
    
    // Get recent scans today
    $stmt = $pdo->prepare("
        SELECT 
            tb_absensi.id,
            tb_absensi.user_id,
            tb_absensi.role,
            tb_absensi.jam_masuk,
            tb_absensi.jam_keluar,
            tb_absensi.status,
            tb_absensi.created_at,
            CASE 
                WHEN tb_absensi.role = 'siswa' THEN tb_siswa.nama_lengkap
                WHEN tb_absensi.role = 'guru' THEN tb_guru.nama_lengkap
            END as nama
        FROM tb_absensi
        LEFT JOIN tb_siswa ON tb_absensi.user_id = tb_siswa.id AND tb_absensi.role = 'siswa'
        LEFT JOIN tb_guru ON tb_absensi.user_id = tb_guru.id AND tb_absensi.role = 'guru'
        WHERE tb_absensi.tanggal = ?
        ORDER BY GREATEST(IFNULL(jam_masuk, '00:00:00'), IFNULL(jam_keluar, '00:00:00')) DESC
        LIMIT 10
    ");
    $stmt->execute([$today]);
    $scans = $stmt->fetchAll();
    
    $result = [];
    foreach ($scans as $scan) {
        // Jika sudah absen PULANG, tambahkan item PULANG dulu (biar paling atas)
        if (!empty($scan['jam_keluar'])) {
            $result[] = [
                'nama' => $scan['nama'],
                'role' => ucfirst($scan['role']),
                'status' => 'Selesai', // Atau bisa tetap tampilkan status asli
                'waktu' => date('H:i', strtotime($scan['jam_keluar'])),
                'type' => 'Pulang'
            ];
        }

        // Tambahkan item MASUK (selalu ada jika sudah masuk)
        if (!empty($scan['jam_masuk'])) {
            $result[] = [
                'nama' => $scan['nama'],
                'role' => ucfirst($scan['role']),
                'status' => ucfirst($scan['status']),
                'waktu' => date('H:i', strtotime($scan['jam_masuk'])),
                'type' => 'Masuk'
            ];
        }
    }
    
    // Potong lagi jadi 10 item terbaru setelah di-split
    $result = array_slice($result, 0, 10);
    
    echo json_encode($result);
    
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
