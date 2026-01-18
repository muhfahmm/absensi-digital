<?php
// app/pages/admin/scanner/recent.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['masuk' => [], 'pulang' => []]);
    exit;
}

// Check if Admin is Wali Kelas
$admin_id = $_SESSION['admin_id'];
$kelas_id = null;

// Use session cache if available, otherwise check DB
if (isset($_SESSION['admin_kelas_id'])) {
    $kelas_id = $_SESSION['admin_kelas_id'];
} else {
    // Check DB
    $stmtCheck = $pdo->prepare("SELECT g.id_kelas_wali FROM tb_admin a JOIN tb_guru g ON a.nuptk = g.nuptk WHERE a.id = ?");
    $stmtCheck->execute([$admin_id]);
    $res = $stmtCheck->fetch();
    if ($res && $res['id_kelas_wali']) {
        $kelas_id = $res['id_kelas_wali'];
    }
}

try {
    $today = date('Y-m-d');
    
    // Base WHERE clause
    $whereClause = "WHERE tb_absensi.tanggal = ? AND tb_absensi.jam_masuk IS NOT NULL";
    $params = [$today];
    
    // If Wali Kelas, filter by class ID for students only
    if ($kelas_id) {
        $whereClause .= " AND ( (tb_absensi.role = 'siswa' AND tb_siswa.id_kelas = ?) OR tb_absensi.role = 'guru' )"; 
        // Note: Usually wali kelas only monitors their students. Do they want to see other gurus? 
        // Request says: "siswa nya sendiri sesuai dengan kelasnya".
        // Let's strict it to: only Siswa in that class OR allow seeing guru (maybe self?).
        // Usually scanner monitors everyone entering. If filter applied, hidden data might confuse user ("why my scan not showing?").
        // But request is specific: "yang terekam adalah siswa nya sendiri sesuai dengan kelasnya".
        // Let's interpret as: Show ONLY students of that class. Do not show other students. 
        // What about teachers? Probably neutral. Let's filter students by class.
        
        $whereClause = "WHERE tb_absensi.tanggal = ? AND tb_absensi.jam_masuk IS NOT NULL 
                        AND ( (tb_absensi.role = 'siswa' AND tb_siswa.id_kelas = ?) )";
        $params[] = $kelas_id;
    }

    // 1. Get Latest MASUK (Limit 20)
    $sqlMasuk = "
        SELECT 
            tb_absensi.id, tb_absensi.user_id, tb_absensi.role, tb_absensi.jam_masuk, tb_absensi.jam_keluar, tb_absensi.status,
            CASE 
                WHEN tb_absensi.role = 'siswa' THEN tb_siswa.nama_lengkap
                WHEN tb_absensi.role = 'guru' THEN tb_guru.nama_lengkap
            END as nama
        FROM tb_absensi
        LEFT JOIN tb_siswa ON tb_absensi.user_id = tb_siswa.id AND tb_absensi.role = 'siswa'
        LEFT JOIN tb_guru ON tb_absensi.user_id = tb_guru.id AND tb_absensi.role = 'guru'
        $whereClause
        ORDER BY tb_absensi.jam_masuk DESC
        LIMIT 20
    ";
    
    $stmtMasuk = $pdo->prepare($sqlMasuk);
    $stmtMasuk->execute($params);
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

    // Logic for Pulang
    $whereClausePulang = "WHERE tb_absensi.tanggal = ? AND tb_absensi.jam_keluar IS NOT NULL";
    $paramsPulang = [$today];
    
    if ($kelas_id) {
        $whereClausePulang .= " AND ( (tb_absensi.role = 'siswa' AND tb_siswa.id_kelas = ?) )";
        $paramsPulang[] = $kelas_id;
    }

    // 2. Get Latest PULANG (Limit 20)
    $sqlPulang = "
        SELECT 
            tb_absensi.id, tb_absensi.user_id, tb_absensi.role, tb_absensi.jam_masuk, tb_absensi.jam_keluar, tb_absensi.status,
            CASE 
                WHEN tb_absensi.role = 'siswa' THEN tb_siswa.nama_lengkap
                WHEN tb_absensi.role = 'guru' THEN tb_guru.nama_lengkap
            END as nama
        FROM tb_absensi
        LEFT JOIN tb_siswa ON tb_absensi.user_id = tb_siswa.id AND tb_absensi.role = 'siswa'
        LEFT JOIN tb_guru ON tb_absensi.user_id = tb_guru.id AND tb_absensi.role = 'guru'
        $whereClausePulang
        ORDER BY tb_absensi.jam_keluar DESC
        LIMIT 20
    ";

    $stmtPulang = $pdo->prepare($sqlPulang);
    $stmtPulang->execute($paramsPulang);
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
