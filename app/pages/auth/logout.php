<?php
// app/pages/auth/logout.php
require_once '../../functions/helpers.php';

session_start();

$role = $_GET['role'] ?? null;

if ($role === 'admin') {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_role']);
    unset($_SESSION['admin_nama']);
    unset($_SESSION['admin_kelas_id']);
    unset($_SESSION['admin_logged_in']);
} elseif ($role === 'guru') {
    unset($_SESSION['guru_id']);
    unset($_SESSION['guru_role']);
    unset($_SESSION['guru_nama']);
    unset($_SESSION['guru_kode_qr']);
    unset($_SESSION['guru_logged_in']);
} elseif ($role === 'siswa') {
    unset($_SESSION['siswa_id']);
    unset($_SESSION['siswa_role']);
    unset($_SESSION['siswa_nama']);
    unset($_SESSION['siswa_kelas_id']);
    unset($_SESSION['siswa_kode_qr']);
    unset($_SESSION['siswa_logged_in']);
} else {
    session_destroy();
}

redirect('app/pages/auth/login.php');
