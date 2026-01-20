<?php
// app/api/api-login.php
session_start();
require_once '../config/database.php';
require_once '../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Bisa username (admin), NIS (siswa), NIP (guru)
    $password = $_POST['password'];

    // Modifikasi: Karena toggle role dihapus, kita cek satu per satu urut dari Admin -> Siswa -> Guru
    
    try {
        // 1. Cek Login Admin
        $stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE username = :user");
        $stmt->execute([':user' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = 'admin';
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];
            
            // Prioritaskan pilihan dari login form, jika tidak ada baru ambil dari database
            $selected_kelas = !empty($_POST['id_kelas']) ? $_POST['id_kelas'] : $admin['id_kelas'];
            $_SESSION['admin_kelas_id'] = $selected_kelas;
            
            $_SESSION['admin_logged_in'] = true;
            redirect('app/pages/admin/dashboard.php');
        }

        // 2. Cek Login Siswa
        $stmt = $pdo->prepare("SELECT * FROM tb_siswa WHERE nis = :user");
        $stmt->execute([':user' => $username]);
        $siswa = $stmt->fetch();

        if ($siswa && password_verify($password, $siswa['password'])) {
            $_SESSION['siswa_id'] = $siswa['id'];
            $_SESSION['siswa_role'] = 'siswa';
            $_SESSION['siswa_nama'] = $siswa['nama_lengkap'];
            $_SESSION['siswa_kelas_id'] = $siswa['id_kelas'];
            $_SESSION['siswa_kode_qr'] = $siswa['kode_qr'];
            $_SESSION['siswa_logged_in'] = true;
            redirect('app/pages/siswa/dashboard.php');
        }

        // 3. Cek Login Guru
        $stmt = $pdo->prepare("SELECT * FROM tb_guru WHERE nuptk = :user");
        $stmt->execute([':user' => $username]);
        $guru = $stmt->fetch();

        if ($guru && password_verify($password, $guru['password'])) {
            $_SESSION['guru_id'] = $guru['id'];
            $_SESSION['guru_role'] = 'guru';
            $_SESSION['guru_nama'] = $guru['nama_lengkap'];
            $_SESSION['guru_kode_qr'] = $guru['kode_qr'];
            $_SESSION['guru_logged_in'] = true;
            redirect('app/pages/guru/dashboard.php');
        }

        // Jika gagal
        $_SESSION['error'] = "Username/ID atau Password salah!";
        redirect('app/pages/auth/login.php');

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        redirect('app/pages/auth/login.php');
    }
} else {
    redirect('app/pages/auth/login.php');
}