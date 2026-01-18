    <?php
// app/functions/auth.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check_already_login() {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        redirect('app/pages/admin/dashboard.php');
    }
    if (isset($_SESSION['guru_logged_in']) && $_SESSION['guru_logged_in'] === true) {
        redirect('app/pages/guru/dashboard.php');
    }
    if (isset($_SESSION['siswa_logged_in']) && $_SESSION['siswa_logged_in'] === true) {
        redirect('app/pages/siswa/dashboard.php');
    }
}

function check_login($required_role = null) {
    if ($required_role === 'admin') {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            redirect('app/pages/auth/login.php');
        }
    } elseif ($required_role === 'guru') {
        if (!isset($_SESSION['guru_logged_in']) || $_SESSION['guru_logged_in'] !== true) {
            redirect('app/pages/auth/login.php');
        }
    } elseif ($required_role === 'siswa') {
        if (!isset($_SESSION['siswa_logged_in']) || $_SESSION['siswa_logged_in'] !== true) {
            redirect('app/pages/auth/login.php');
        }
    } else {
        // Fallback or generic check (not recommended with strict namespacing)
        if (
            (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) &&
            (!isset($_SESSION['guru_logged_in']) || !$_SESSION['guru_logged_in']) &&
            (!isset($_SESSION['siswa_logged_in']) || !$_SESSION['siswa_logged_in'])
        ) {
            redirect('app/pages/auth/login.php');
        }
    }
}
