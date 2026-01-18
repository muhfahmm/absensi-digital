<?php
// app/pages/guru/scanner/process_scan.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../functions/helpers.php';

// Parse Input
$input = json_decode(file_get_contents('php://input'), true);
$qr_code = $input['qr_code'] ?? '';

if (empty($qr_code)) {
    echo json_encode(['success' => false, 'message' => 'QR Code tidak terbaca']);
    exit;
}

try {
    // 1. Find Siswa
    $stmt = $pdo->prepare("SELECT * FROM tb_siswa WHERE kode_qr = ?");
    $stmt->execute([$qr_code]);
    $siswa = $stmt->fetch();

    if (!$siswa) {
        echo json_encode(['success' => false, 'message' => 'Siswa tidak ditemukan']);
        exit;
    }

    $id_siswa = $siswa['id'];
    $nama_siswa = $siswa['nama_lengkap'];
    $today = date('Y-m-d');
    $now_time = date('H:i:s');

    // 2. Check Attendance Today
    $stmt = $pdo->prepare("SELECT * FROM tb_absensi WHERE user_id = ? AND role = 'siswa' AND tanggal = ?");
    $stmt->execute([$id_siswa, $today]);
    $absensi = $stmt->fetch();

    if (!$absensi) {
        // --- CLOCK IN ---
        
        // Define Late Time (Misal 07:15)
        $jam_masuk_sekolah = "07:15:00";
        $status = ($now_time > $jam_masuk_sekolah) ? 'terlambat' : 'hadir';

        $stmt = $pdo->prepare("INSERT INTO tb_absensi (user_id, role, tanggal, jam_masuk, status) VALUES (?, 'siswa', ?, ?, ?)");
        if ($stmt->execute([$id_siswa, $today, $now_time, $status])) {
            echo json_encode([
                'success' => true,
                'message' => 'Absen Masuk Berhasil',
                'nama_siswa' => $nama_siswa,
                'waktu' => $now_time . ' (' . ucfirst($status) . ')'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
        }

    } else {
        // --- CLOCK OUT or ALREADY DONE ---
        
        if ($absensi['jam_keluar']) {
            echo json_encode([
                'success' => false,
                'message' => 'Siswa sudah absen masuk & pulang hari ini',
                'nama_siswa' => $nama_siswa
            ]);
        } else {
            // Update Jam Keluar
            $stmt = $pdo->prepare("UPDATE tb_absensi SET jam_keluar = ? WHERE id = ?");
            if ($stmt->execute([$now_time, $absensi['id']])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Absen Pulang Berhasil',
                    'nama_siswa' => $nama_siswa,
                    'waktu' => $now_time
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal update jam pulang']);
            }
        }
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
