<?php
// app/functions/security_helper.php

require_once __DIR__ . '/../config/security_config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Set Security Headers
 */
function setSecurityHeaders() {
    // Set security headers from config
    foreach (SECURITY_HEADERS as $header => $value) {
        header("$header: $value");
    }
    
    // Set CSP
    header("Content-Security-Policy: " . CSP_POLICY);
    
    // Set HSTS if HTTPS is enabled
    if (ENABLE_HTTPS_ONLY && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}

/**
 * Get Client IP Address
 * 
 * @return string IP address
 */
function getClientIP() {
    $ipAddress = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    return $ipAddress;
}

/**
 * Get User Agent
 * 
 * @return string User agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Check Rate Limit
 * 
 * @param string $identifier Identifier (username or IP)
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if rate limit exceeded
 */
function checkRateLimit($identifier, $maxAttempts = RATE_LIMIT_MAX_ATTEMPTS, $timeWindow = RATE_LIMIT_TIME_WINDOW) {
    global $pdo;
    
    try {
        $cutoffTime = date('Y-m-d H:i:s', time() - $timeWindow);
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as attempt_count 
            FROM tb_failed_login_attempts 
            WHERE (username = :identifier OR ip_address = :identifier) 
            AND attempt_time > :cutoff
        ");
        
        $stmt->execute([
            ':identifier' => $identifier,
            ':cutoff' => $cutoffTime
        ]);
        
        $result = $stmt->fetch();
        
        return $result['attempt_count'] >= $maxAttempts;
        
    } catch (PDOException $e) {
        logSecurityEvent('rate_limit_check_error', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Record Failed Login Attempt
 * 
 * @param string $username Username
 * @param string $ipAddress IP address
 */
function recordFailedLogin($username, $ipAddress = null) {
    global $pdo;
    
    if ($ipAddress === null) {
        $ipAddress = getClientIP();
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tb_failed_login_attempts (username, ip_address, attempt_time, user_agent) 
            VALUES (:username, :ip, NOW(), :agent)
        ");
        
        $stmt->execute([
            ':username' => $username,
            ':ip' => $ipAddress,
            ':agent' => getUserAgent()
        ]);
        
        // Update failed login count in user table
        updateFailedLoginCount($username);
        
    } catch (PDOException $e) {
        logSecurityEvent('record_failed_login_error', ['error' => $e->getMessage()]);
    }
}

/**
 * Update Failed Login Count in User Table
 * 
 * @param string $username Username
 */
function updateFailedLoginCount($username) {
    global $pdo;
    
    try {
        // Try siswa table
        $stmt = $pdo->prepare("
            UPDATE tb_siswa 
            SET failed_login_count = failed_login_count + 1 
            WHERE nis = :username
        ");
        $stmt->execute([':username' => $username]);
        
        if ($stmt->rowCount() === 0) {
            // Try guru table
            $stmt = $pdo->prepare("
                UPDATE tb_guru 
                SET failed_login_count = failed_login_count + 1 
                WHERE nuptk = :username
            ");
            $stmt->execute([':username' => $username]);
        }
        
        if ($stmt->rowCount() === 0) {
            // Try admin table
            $stmt = $pdo->prepare("
                UPDATE tb_admin 
                SET failed_login_count = failed_login_count + 1 
                WHERE username = :username
            ");
            $stmt->execute([':username' => $username]);
        }
        
        // Check if account should be locked
        checkAndLockAccount($username);
        
    } catch (PDOException $e) {
        logSecurityEvent('update_failed_count_error', ['error' => $e->getMessage()]);
    }
}

/**
 * Check and Lock Account if needed
 * 
 * @param string $username Username
 */
function checkAndLockAccount($username) {
    global $pdo;
    
    try {
        $lockUntil = date('Y-m-d H:i:s', time() + ACCOUNT_LOCKOUT_DURATION);
        
        // Check siswa
        $stmt = $pdo->prepare("
            UPDATE tb_siswa 
            SET account_locked_until = :lock_until 
            WHERE nis = :username 
            AND failed_login_count >= :max_attempts
        ");
        $stmt->execute([
            ':username' => $username,
            ':lock_until' => $lockUntil,
            ':max_attempts' => MAX_FAILED_LOGIN_ATTEMPTS
        ]);
        
        // Check guru
        $stmt = $pdo->prepare("
            UPDATE tb_guru 
            SET account_locked_until = :lock_until 
            WHERE nuptk = :username 
            AND failed_login_count >= :max_attempts
        ");
        $stmt->execute([
            ':username' => $username,
            ':lock_until' => $lockUntil,
            ':max_attempts' => MAX_FAILED_LOGIN_ATTEMPTS
        ]);
        
        // Check admin
        $stmt = $pdo->prepare("
            UPDATE tb_admin 
            SET account_locked_until = :lock_until 
            WHERE username = :username 
            AND failed_login_count >= :max_attempts
        ");
        $stmt->execute([
            ':username' => $username,
            ':lock_until' => $lockUntil,
            ':max_attempts' => MAX_FAILED_LOGIN_ATTEMPTS
        ]);
        
    } catch (PDOException $e) {
        logSecurityEvent('lock_account_error', ['error' => $e->getMessage()]);
    }
}

/**
 * Check if Account is Locked
 * 
 * @param string $username Username
 * @param string $table Table name (tb_siswa, tb_guru, tb_admin)
 * @return bool True if locked
 */
function isAccountLocked($username, $table = null) {
    global $pdo;
    
    try {
        $tables = $table ? [$table] : ['tb_siswa', 'tb_guru', 'tb_admin'];
        $identifierFields = [
            'tb_siswa' => 'nis',
            'tb_guru' => 'nuptk',
            'tb_admin' => 'username'
        ];
        
        foreach ($tables as $tbl) {
            $field = $identifierFields[$tbl];
            
            $stmt = $pdo->prepare("
                SELECT account_locked_until 
                FROM $tbl 
                WHERE $field = :username
            ");
            $stmt->execute([':username' => $username]);
            $result = $stmt->fetch();
            
            if ($result && $result['account_locked_until']) {
                $lockUntil = strtotime($result['account_locked_until']);
                if ($lockUntil > time()) {
                    return true;
                }
            }
        }
        
        return false;
        
    } catch (PDOException $e) {
        logSecurityEvent('check_lock_error', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Reset Failed Login Count
 * 
 * @param string $username Username
 * @param string $table Table name
 */
function resetFailedLoginCount($username, $table) {
    global $pdo;
    
    try {
        $identifierFields = [
            'tb_siswa' => 'nis',
            'tb_guru' => 'nuptk',
            'tb_admin' => 'username'
        ];
        
        $field = $identifierFields[$table];
        
        $stmt = $pdo->prepare("
            UPDATE $table 
            SET failed_login_count = 0, account_locked_until = NULL 
            WHERE $field = :username
        ");
        $stmt->execute([':username' => $username]);
        
    } catch (PDOException $e) {
        logSecurityEvent('reset_failed_count_error', ['error' => $e->getMessage()]);
    }
}

/**
 * Generate CSRF Token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    
    return $token;
}

/**
 * Validate CSRF Token
 * 
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check expiration
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
        return false;
    }
    
    // Check token match
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize Input
 * 
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    // Remove whitespace
    $data = trim($data);
    
    // Remove backslashes
    $data = stripslashes($data);
    
    // Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * Validate Password Strength
 * 
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    // Check minimum length
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password minimal " . PASSWORD_MIN_LENGTH . " karakter";
    }
    
    // Check uppercase
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 huruf besar";
    }
    
    // Check lowercase
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 huruf kecil";
    }
    
    // Check number
    if (PASSWORD_REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 angka";
    }
    
    // Check special character
    if (PASSWORD_REQUIRE_SPECIAL_CHAR && !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 karakter spesial";
    }
    
    if (empty($errors)) {
        return ['valid' => true, 'message' => 'Password valid'];
    }
    
    return ['valid' => false, 'message' => implode(', ', $errors)];
}

/**
 * Log Security Event
 * 
 * @param string $eventType Event type
 * @param array $details Event details
 */
function logSecurityEvent($eventType, $details = []) {
    global $pdo;
    
    if (!LOG_SECURITY_EVENTS) {
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tb_security_logs (event_type, username, ip_address, details, created_at) 
            VALUES (:type, :username, :ip, :details, NOW())
        ");
        
        $stmt->execute([
            ':type' => $eventType,
            ':username' => $details['username'] ?? 'unknown',
            ':ip' => getClientIP(),
            ':details' => json_encode($details)
        ]);
        
    } catch (PDOException $e) {
        // Silent fail for logging errors
        error_log("Security log error: " . $e->getMessage());
    }
}

/**
 * Clean Old Failed Login Attempts
 * Call this periodically to clean up old records
 */
function cleanOldFailedAttempts() {
    global $pdo;
    
    try {
        $cutoffTime = date('Y-m-d H:i:s', time() - (RATE_LIMIT_TIME_WINDOW * 2));
        
        $stmt = $pdo->prepare("
            DELETE FROM tb_failed_login_attempts 
            WHERE attempt_time < :cutoff
        ");
        $stmt->execute([':cutoff' => $cutoffTime]);
        
    } catch (PDOException $e) {
        logSecurityEvent('cleanup_error', ['error' => $e->getMessage()]);
    }
}

/**
 * Validate Email Format
 * 
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if request is from allowed origin
 * 
 * @return bool True if allowed
 */
function isAllowedOrigin() {
    if (in_array('*', ALLOWED_ORIGINS)) {
        return true;
    }
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    return in_array($origin, ALLOWED_ORIGINS);
}
