<?php
// app/api/jadwal.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Suppress PHP errors to avoid breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->role)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$user_id = $data->user_id;
$role = $data->role;

try {
    $jadwal = [];

    if ($role === 'siswa') {
        // 1. Get Student's Class ID
        $stmtSiswa = $pdo->prepare("SELECT id_kelas FROM tb_siswa WHERE id = ?");
        $stmtSiswa->execute([$user_id]);
        $siswa = $stmtSiswa->fetch();

        if ($siswa) {
            $id_kelas = $siswa['id_kelas'];

            // 2. Fetch Schedule for this Class
            // Link to tb_jam_pelajaran to get time
            $sql = "
                SELECT j.*, 
                       jp.jam_mulai, jp.jam_selesai, jp.jam_ke, jp.is_istirahat, 
                       m.nama_mapel, 
                       g.nama_lengkap as nama_guru
                FROM tb_jadwal_pelajaran j
                JOIN tb_jam_pelajaran jp ON j.id_jam = jp.id
                LEFT JOIN tb_mata_pelajaran m ON j.id_mapel = m.id
                LEFT JOIN tb_guru g ON j.id_guru = g.id
                WHERE j.id_kelas = ?
                ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jp.jam_mulai ASC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_kelas]);
            $jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo json_encode(['success' => false, 'message' => 'Data siswa tidak ditemukan']);
            exit;
        }

    } else if ($role === 'guru') {
        // Fetch Schedule where this teacher is teaching
        $sql = "
            SELECT j.*, 
                   jp.jam_mulai, jp.jam_selesai, jp.jam_ke, jp.is_istirahat,
                   m.nama_mapel, 
                   k.nama_kelas
            FROM tb_jadwal_pelajaran j
            JOIN tb_jam_pelajaran jp ON j.id_jam = jp.id
            LEFT JOIN tb_mata_pelajaran m ON j.id_mapel = m.id
            LEFT JOIN tb_kelas k ON j.id_kelas = k.id
            WHERE j.id_guru = ?
            ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jp.jam_mulai ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Group by Day
    $grouped_jadwal = [];
    foreach ($jadwal as $row) {
        $hari = $row['hari'];
        if (!isset($grouped_jadwal[$hari])) {
            $grouped_jadwal[$hari] = [];
        }
        $grouped_jadwal[$hari][] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $grouped_jadwal
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
