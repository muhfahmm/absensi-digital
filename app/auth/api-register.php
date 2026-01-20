<?php
// app/auth/api-register.php - Enhanced with Security Features
session_start();

require_once '../config/database.php';
require_once '../config/security_config.php';
require_once '../functions/helpers.php';
require_once '../functions/security_helper.php';

// Set security headers
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'] ?? ''; // siswa / guru / admin
    $nama = $_POST['nama_lengkap'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Get client info
    $ipAddress = getClientIP();
    
    // Input validation
    $errors = [];
    
    if (empty($role) || !in_array($role, ['siswa', 'guru', 'admin'])) {
        $errors[] = "Role tidak valid";
    }
    
    if (empty($nama)) {
        $errors[] = "Nama lengkap harus diisi";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    }
    
    if ($password !== $passwordConfirm) {
        $errors[] = "Password dan konfirmasi password tidak cocok";
    }
    
    // Validate password strength
    $passwordValidation = validatePasswordStrength($password);
    if (!$passwordValidation['valid']) {
        $errors[] = $passwordValidation['message'];
    }
    
    // Role-specific validation
    if ($role === 'siswa') {
        $nis = $_POST['u_id'] ?? '';
        if (empty($nis)) {
            $errors[] = "NIS harus diisi";
        }
        // Validate NIS format (numeric, reasonable length)
        if (!empty($nis) && (!is_numeric($nis) || strlen($nis) < 4 || strlen($nis) > 20)) {
            $errors[] = "Format NIS tidak valid";
        }
    } elseif ($role === 'guru') {
        $nuptk = $_POST['nuptk_guru'] ?? '';
        if (empty($nuptk)) {
            $errors[] = "NUPTK harus diisi";
        }
        // Validate NUPTK format (numeric, typically 16 digits)
        if (!empty($nuptk) && (!is_numeric($nuptk) || strlen($nuptk) < 10 || strlen($nuptk) > 20)) {
            $errors[] = "Format NUPTK tidak valid";
        }
    } elseif ($role === 'admin') {
        $username = $_POST['username_admin'] ?? '';
        if (empty($username)) {
            $errors[] = "Username harus diisi";
        }
        // Validate username format (alphanumeric, underscore, 3-50 chars)
        if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $errors[] = "Username harus 3-50 karakter (huruf, angka, underscore)";
        }
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        
        logSecurityEvent('registration_failed', [
            'role' => $role,
            'reason' => 'validation_error',
            'errors' => $errors,
            'ip' => $ipAddress
        ]);
        
        redirect('app/pages/auth/register.php');
        exit;
    }
    
    // Sanitize inputs
    $nama = sanitizeInput($nama);
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        if ($role == 'siswa') {
            $nis = sanitizeInput($_POST['u_id']);
            $id_kelas = !empty($_POST['id_kelas']) ? intval($_POST['id_kelas']) : null;
            
            // Check if NIS already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_siswa WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "NIS sudah terdaftar!";
                logSecurityEvent('registration_failed', [
                    'role' => $role,
                    'nis' => $nis,
                    'reason' => 'duplicate_nis',
                    'ip' => $ipAddress
                ]);
                redirect('app/pages/auth/register.php');
                exit;
            }
            
            // Generate QR code
            $kode_qr = "SISWA-" . $nis . "-" . uniqid();

            $sql = "INSERT INTO tb_siswa (nis, nama_lengkap, password, id_kelas, kode_qr, failed_login_count) 
                    VALUES (:id, :nama, :pass, :kelas, :qr, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $nis, 
                ':nama' => $nama, 
                ':pass' => $hashedPassword, 
                ':kelas' => $id_kelas, 
                ':qr' => $kode_qr
            ]);
            
            logSecurityEvent('registration_success', [
                'role' => $role,
                'nis' => $nis,
                'nama' => $nama,
                'ip' => $ipAddress
            ]);
            
        } else if ($role == 'guru') {
            $nuptk = sanitizeInput($_POST['nuptk_guru']);
            $guru_mapel_id = !empty($_POST['guru_mapel_id']) ? intval($_POST['guru_mapel_id']) : null;
            
            // Check if NUPTK already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_guru WHERE nuptk = :nuptk");
            $stmt->execute([':nuptk' => $nuptk]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "NUPTK sudah terdaftar!";
                logSecurityEvent('registration_failed', [
                    'role' => $role,
                    'nuptk' => $nuptk,
                    'reason' => 'duplicate_nuptk',
                    'ip' => $ipAddress
                ]);
                redirect('app/pages/auth/register.php');
                exit;
            }

            // Generate QR code
            $kode_qr = "GURU-" . $nuptk . "-" . uniqid();

            $sql = "INSERT INTO tb_guru (nuptk, nama_lengkap, password, kode_qr, guru_mapel_id, failed_login_count) 
                    VALUES (:id, :nama, :pass, :qr, :mapel, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $nuptk, 
                ':nama' => $nama, 
                ':pass' => $hashedPassword, 
                ':qr' => $kode_qr, 
                ':mapel' => $guru_mapel_id
            ]);
            
            logSecurityEvent('registration_success', [
                'role' => $role,
                'nuptk' => $nuptk,
                'nama' => $nama,
                'ip' => $ipAddress
            ]);
        
        } else if ($role == 'admin') {
            $username = sanitizeInput($_POST['username_admin']);
            $id_kelas = !empty($_POST['id_kelas']) ? intval($_POST['id_kelas']) : null;
            
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_admin WHERE username = :username");
            $stmt->execute([':username' => $username]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Username sudah terdaftar!";
                logSecurityEvent('registration_failed', [
                    'role' => $role,
                    'username' => $username,
                    'reason' => 'duplicate_username',
                    'ip' => $ipAddress
                ]);
                redirect('app/pages/auth/register.php');
                exit;
            }
            
            $sql = "INSERT INTO tb_admin (username, password, nama_lengkap, id_kelas, failed_login_count) 
                    VALUES (:user, :pass, :nama, :kelas, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user' => $username, 
                ':pass' => $hashedPassword, 
                ':nama' => $nama, 
                ':kelas' => $id_kelas
            ]);
            
            logSecurityEvent('registration_success', [
                'role' => $role,
                'username' => $username,
                'nama' => $nama,
                'ip' => $ipAddress
            ]);
        }
        
        // Success message
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        redirect('app/pages/auth/login.php');

    } catch (PDOException $e) {
        // Log error
        logSecurityEvent('registration_error', [
            'role' => $role,
            'error' => $e->getMessage(),
            'ip' => $ipAddress
        ]);
        
        // User-friendly error message
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "Data sudah terdaftar!";
        } else {
            $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
        }
        redirect('app/pages/auth/register.php');
    }
} else {
    redirect('app/pages/auth/register.php');
}
