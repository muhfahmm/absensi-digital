<?php
// app/api/login.php - Enhanced with Security Features
session_start();
error_reporting(0);
ini_set('display_errors', 0);

// Load dependencies
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/security_config.php';
require_once __DIR__ . '/../../../functions/jwt_helper.php';
require_once __DIR__ . '/../../../functions/security_helper.php';

// Set security headers
setSecurityHeaders();
header('Content-Type: application/json');

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    // Get client info
    $ipAddress = getClientIP();
    $userAgent = getUserAgent();

    // Input validation
    if (empty($username) || empty($password)) {
        logSecurityEvent('login_attempt_failed', [
            'username' => $username,
            'reason' => 'empty_credentials',
            'ip' => $ipAddress
        ]);
        
        echo json_encode([
            'success' => false, 
            'message' => 'Username dan Password harus diisi'
        ]);
        exit;
    }
    
    // Sanitize input
    $username = sanitizeInput($username);
    
    // Check rate limit by IP
    if (checkRateLimit($ipAddress)) {
        logSecurityEvent('rate_limit_exceeded', [
            'username' => $username,
            'ip' => $ipAddress
        ]);
        
        echo json_encode([
            'success' => false,
            'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . (RATE_LIMIT_TIME_WINDOW / 60) . ' menit.',
            'locked_until' => time() + RATE_LIMIT_LOCKOUT_DURATION
        ]);
        exit;
    }
    
    // Check if account is locked
    if (isAccountLocked($username)) {
        logSecurityEvent('login_attempt_locked_account', [
            'username' => $username,
            'ip' => $ipAddress
        ]);
        
        echo json_encode([
            'success' => false,
            'message' => 'Akun Anda terkunci karena terlalu banyak percobaan login gagal. Silakan coba lagi dalam ' . (ACCOUNT_LOCKOUT_DURATION / 60) . ' menit.'
        ]);
        exit;
    }
    
    try {
        $loginSuccess = false;
        $userRole = null;
        $userData = null;
        $userTable = null;
        
        // 1. Check Admin Login
        $stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE username = :user");
        $stmt->execute([':user' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $loginSuccess = true;
            $userRole = 'admin';
            $userTable = 'tb_admin';
            $userData = [
                'id' => $admin['id'],
                'nama' => $admin['nama_lengkap'],
                'username' => $admin['username'],
                'id_kelas' => $admin['id_kelas'] ?? null
            ];
        }

        // 2. Check Student Login
        if (!$loginSuccess) {
            $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas 
                                  FROM tb_siswa s 
                                  LEFT JOIN tb_kelas k ON s.id_kelas = k.id 
                                  WHERE s.nis = :user");
            $stmt->execute([':user' => $username]);
            $siswa = $stmt->fetch();

            if ($siswa && password_verify($password, $siswa['password'])) {
                $loginSuccess = true;
                $userRole = 'siswa';
                $userTable = 'tb_siswa';
                $userData = [
                    'id' => $siswa['id'],
                    'nama' => $siswa['nama_lengkap'],
                    'nis' => $siswa['nis'],
                    'kelas_id' => $siswa['id_kelas'],
                    'nama_kelas' => $siswa['nama_kelas'] ?? '-',
                    'kode_qr' => $siswa['kode_qr'],
                    'foto_profil' => $siswa['foto_profil'],
                    'poin' => $siswa['poin'] ?? 100,
                    'created_at' => $siswa['created_at']
                ];
            }
        }

        // 3. Check Teacher Login
        if (!$loginSuccess) {
            $stmt = $pdo->prepare("SELECT g.*, k.nama_kelas 
                                  FROM tb_guru g 
                                  LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
                                  WHERE g.nuptk = :user");
            $stmt->execute([':user' => $username]);
            $guru = $stmt->fetch();

            if ($guru && password_verify($password, $guru['password'])) {
                $loginSuccess = true;
                $userRole = 'guru';
                $userTable = 'tb_guru';
                $userData = [
                    'id' => $guru['id'],
                    'nama' => $guru['nama_lengkap'],
                    'nuptk' => $guru['nuptk'],
                    'nama_kelas' => $guru['nama_kelas'] ? 'Wali Kelas ' . $guru['nama_kelas'] : 'Tenaga Pendidik',
                    'kode_qr' => $guru['kode_qr'],
                    'foto_profil' => $guru['foto_profil'],
                    'created_at' => $guru['created_at']
                ];
            }
        }

        // Handle login result
        if ($loginSuccess) {
            // Reset failed login count
            resetFailedLoginCount($username, $userTable);
            
            // Update last login info
            $identifierFields = [
                'tb_siswa' => 'nis',
                'tb_guru' => 'nuptk',
                'tb_admin' => 'username'
            ];
            $field = $identifierFields[$userTable];
            
            $stmt = $pdo->prepare("
                UPDATE $userTable 
                SET last_login_at = NOW(), last_login_ip = :ip 
                WHERE $field = :username
            ");
            $stmt->execute([':ip' => $ipAddress, ':username' => $username]);
            
            // Generate JWT tokens
            $tokens = generateTokenPair($userData['id'], $userRole, $userData);
            
            // Log successful login
            logSecurityEvent('login_success', [
                'username' => $username,
                'role' => $userRole,
                'ip' => $ipAddress,
                'user_agent' => $userAgent
            ]);
            
            // Clean old failed attempts periodically (10% chance)
            if (rand(1, 10) === 1) {
                cleanOldFailedAttempts();
            }
            
            echo json_encode([
                'success' => true,
                'token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => $tokens['token_type'],
                'expires_in' => $tokens['expires_in'],
                'role' => $userRole,
                'user' => $userData
            ]);
            exit;
        } else {
            // Login failed - record attempt
            recordFailedLogin($username, $ipAddress);
            
            logSecurityEvent('login_failed', [
                'username' => $username,
                'reason' => 'invalid_credentials',
                'ip' => $ipAddress,
                'user_agent' => $userAgent
            ]);
            
            echo json_encode([
                'success' => false, 
                'message' => 'Username atau Password salah'
            ]);
        }

    } catch (PDOException $e) {
        logSecurityEvent('login_error', [
            'username' => $username,
            'error' => $e->getMessage(),
            'ip' => $ipAddress
        ]);
        
        echo json_encode([
            'success' => false, 
            'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid Request Method'
    ]);
}
