<?php
// app/functions/auth.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check_already_login() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $role = $_SESSION['role'] ?? null;
        switch ($role) {
            case 'admin': redirect('app/pages/admin/dashboard.php'); break;
            case 'guru': redirect('app/pages/guru/dashboard.php'); break;
            case 'siswa': redirect('app/pages/siswa/dashboard.php'); break;
            default: redirect('app/pages/auth/logout.php'); break;
        }
    }
}

function check_login($required_role = null) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        redirect('app/pages/auth/login.php');
    }
    if ($required_role !== null) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
            check_already_login();
            exit;
        }
    }
}
