<?php
// app/api/login.php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if(empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and Password required']);
        exit;
    }
    
    try {
        // 1. Cek Login Admin
        $stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE username = :user");
        $stmt->execute([':user' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            echo json_encode([
                'success' => true,
                'role' => 'admin',
                'user' => [
                    'id' => $admin['id'],
                    'nama' => $admin['nama_lengkap'],
                    'username' => $admin['username']
                ]
            ]);
            exit;
        }

        // 2. Cek Login Siswa
        $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas 
                              FROM tb_siswa s 
                              LEFT JOIN tb_kelas k ON s.id_kelas = k.id 
                              WHERE s.nis = :user");
        $stmt->execute([':user' => $username]);
        $siswa = $stmt->fetch();

        if ($siswa && password_verify($password, $siswa['password'])) {
            echo json_encode([
                'success' => true,
                'role' => 'siswa',
                'user' => [
                    'id' => $siswa['id'],
                    'nama' => $siswa['nama_lengkap'],
                    'nis' => $siswa['nis'],
                    'kelas_id' => $siswa['id_kelas'],
                    'nama_kelas' => $siswa['nama_kelas'] ?? '-',
                    'kode_qr' => $siswa['kode_qr'],
                    'foto_profil' => $siswa['foto_profil'],
                    'poin' => $siswa['poin'] ?? 100 // Default 100 jika null
                ]
            ]);
            exit;
        }

        // 3. Cek Login Guru
        $stmt = $pdo->prepare("SELECT g.*, k.nama_kelas 
                              FROM tb_guru g 
                              LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
                              WHERE g.nuptk = :user");
        $stmt->execute([':user' => $username]);
        $guru = $stmt->fetch();

        if ($guru && password_verify($password, $guru['password'])) {
            echo json_encode([
                'success' => true,
                'role' => 'guru',
                'user' => [
                    'id' => $guru['id'],
                    'nama' => $guru['nama_lengkap'],
                    'nuptk' => $guru['nuptk'],
                    'nama_kelas' => $guru['nama_kelas'] ? 'Wali Kelas ' . $guru['nama_kelas'] : 'Tenaga Pendidik',
                    'kode_qr' => $guru['kode_qr'],
                    'foto_profil' => $guru['foto_profil']
                ]
            ]);
            exit;
        }

        // Jika gagal
        echo json_encode(['success' => false, 'message' => 'Username atau Password salah']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
