<?php
// app/api/nilai.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Suppress PHP errors to avoid breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../../../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->role)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$user_id = $data->user_id;
$role = $data->role;

try {
    if ($role === 'siswa') {
        // Fetch grades for this student
        $sql = "
            SELECT n.*, m.nama_mapel, g.nama_lengkap as nama_guru
            FROM tb_nilai n
            LEFT JOIN tb_mata_pelajaran m ON n.id_mapel = m.id
            LEFT JOIN tb_guru g ON n.id_guru = g.id
            WHERE n.id_siswa = ?
            ORDER BY m.nama_mapel ASC, n.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $nilai = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by Subject
        $grouped_nilai = [];
        foreach ($nilai as $row) {
            $mapel = $row['nama_mapel'] ?: 'Lainnya';
            if (!isset($grouped_nilai[$mapel])) {
                $grouped_nilai[$mapel] = [
                    'mata_pelajaran' => $mapel,
                    'guru' => $row['nama_guru'],
                    'grades' => []
                ];
            }
            $grouped_nilai[$mapel]['grades'][] = [
                'tipe' => $row['tipe_nilai'],
                'nilai' => $row['nilai'],
                'ket' => $row['keterangan'],
                'tgl' => $row['created_at']
            ];
        }

        echo json_encode([
            'success' => true,
            'role' => 'siswa',
            'data' => array_values($grouped_nilai)
        ]);

    } else if ($role === 'guru') {
        // Fetch grades given by this teacher
        $sql = "
            SELECT n.*, m.nama_mapel, s.nama_lengkap as nama_siswa, k.nama_kelas
            FROM tb_nilai n
            LEFT JOIN tb_mata_pelajaran m ON n.id_mapel = m.id
            LEFT JOIN tb_siswa s ON n.id_siswa = s.id
            LEFT JOIN tb_kelas k ON n.id_kelas = k.id
            WHERE n.id_guru = ?
            ORDER BY n.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $nilai = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'role' => 'guru',
            'data' => $nilai
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Role not supported']);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
