<?php
// app/api/wali_kelas_monitoring.php
header('Content-Type: application/json');
require_once '../config/database.php';

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $guru_id = $input['guru_id'] ?? '';

    if (empty($guru_id)) {
        echo json_encode(['success' => false, 'message' => 'Guru ID required']);
        exit;
    }

    try {
        // 1. Cek Apakah Guru ini Wali Kelas
        $stmt = $pdo->prepare("SELECT id_kelas_wali, k.nama_kelas 
                               FROM tb_guru g 
                               JOIN tb_kelas k ON g.id_kelas_wali = k.id 
                               WHERE g.id = ?");
        $stmt->execute([$guru_id]);
        $guru_info = $stmt->fetch();

        if (!$guru_info || !$guru_info['id_kelas_wali']) {
            echo json_encode(['success' => false, 'message' => 'Bukan Wali Kelas']);
            exit;
        }

        $kelas_id = $guru_info['id_kelas_wali'];
        $nama_kelas = $guru_info['nama_kelas'];
        $today = date('Y-m-d');

        // 2. Ambil Data Siswa Sekelas + Status Absensi Hari Ini
        $sql = "SELECT 
                    s.id, 
                    s.nis, 
                    s.nama_lengkap, 
                    s.foto_profil,
                    a.jam_masuk,
                    a.jam_keluar,
                    a.status as status_absensi
                FROM tb_siswa s
                LEFT JOIN tb_absensi a ON s.id = a.user_id 
                    AND a.role = 'siswa' 
                    AND a.tanggal = :today
                WHERE s.id_kelas = :kelas_id
                ORDER BY s.nama_lengkap ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':today' => $today, ':kelas_id' => $kelas_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Hitung Summary
        $summary = [
            'total' => count($students),
            'hadir' => 0,
            'belum' => 0,
            'terlambat' => 0
        ];

        $data_siswa = [];

        foreach ($students as $student) {
            $status = $student['status_absensi'] ? $student['status_absensi'] : 'belum';
            
            // Increment counters
            if ($status == 'belum') {
                $summary['belum']++;
            } else {
                $summary['hadir']++; // Hadir or Terlambat counts as "Sudah Absen" for basic count
                if ($status == 'terlambat') {
                    $summary['terlambat']++;
                }
            }

            $data_siswa[] = [
                'id' => $student['id'],
                'nama' => $student['nama_lengkap'],
                'nis' => $student['nis'],
                'foto' => $student['foto_profil'],
                'status' => $status, // hadir, terlambat, izin, sakit, alpa, belum
                'jam_masuk' => $student['jam_masuk'],
                'jam_keluar' => $student['jam_keluar']
            ];
        }

        echo json_encode([
            'success' => true,
            'kelas' => $nama_kelas,
            'summary' => $summary,
            'students' => $data_siswa
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
