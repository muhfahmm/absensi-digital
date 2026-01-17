<?php
// app/api/api-register.php
session_start();
require_once '../config/database.php';
require_once '../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role']; // siswa / guru
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        if ($role == 'siswa') {
            $nis = htmlspecialchars($_POST['u_id']); // NIS
            $id_kelas = !empty($_POST['id_kelas']) ? $_POST['id_kelas'] : null;
            
            // Generate simple QR token (Misal: SISWA-NIS)
            $kode_qr = "SISWA-" . $nis . "-" . uniqid();

            $sql = "INSERT INTO tb_siswa (nis, nama_lengkap, password, id_kelas, kode_qr) VALUES (:id, :nama, :pass, :kelas, :qr)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $nis, ':nama' => $nama, ':pass' => $password, ':kelas' => $id_kelas, ':qr' => $kode_qr]);
            
        } else if ($role == 'guru') {
            $nuptk = htmlspecialchars($_POST['nuptk_guru']); // NUPTK dari input baru
            $guru_mapel_id = !empty($_POST['guru_mapel_id']) ? $_POST['guru_mapel_id'] : null; // ID Mapel

            // Generate simple QR token
            $kode_qr = "GURU-" . $nuptk . "-" . uniqid();

            $sql = "INSERT INTO tb_guru (nuptk, nama_lengkap, password, kode_qr, guru_mapel_id) VALUES (:id, :nama, :pass, :qr, :mapel)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $nuptk, ':nama' => $nama, ':pass' => $password, ':qr' => $kode_qr, ':mapel' => $guru_mapel_id]);
        
        } else if ($role == 'admin') {
            $username = htmlspecialchars($_POST['username_admin']); // Username Admin
            $id_kelas = !empty($_POST['id_kelas']) ? $_POST['id_kelas'] : null;
            
            $sql = "INSERT INTO tb_admin (username, password, nama_lengkap, id_kelas) VALUES (:user, :pass, :nama, :kelas)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user' => $username, ':pass' => $password, ':nama' => $nama, ':kelas' => $id_kelas]);
        }
        
        // Simpan pesan sukses di session sementara (flash message)
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        redirect('app/pages/auth/login.php');

    } catch (PDOException $e) {
        // Simpan pesan error di session
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "NIS/NUPTK sudah terdaftar!";
        } else {
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        }
        redirect('app/pages/auth/register.php');
    }
} else {
    redirect('app/pages/auth/register.php');
}
