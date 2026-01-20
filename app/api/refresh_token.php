<?php
// app/api/refresh_token.php - Refresh JWT Token
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security_config.php';
require_once __DIR__ . '/../functions/jwt_helper.php';
require_once __DIR__ . '/../functions/security_helper.php';

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
    $refreshToken = $input['refresh_token'] ?? '';
    
    if (empty($refreshToken)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Refresh token diperlukan'
        ]);
        exit;
    }
    
    // Refresh the token
    $newTokens = refreshAccessToken($refreshToken);
    
    if ($newTokens) {
        logSecurityEvent('token_refreshed', [
            'ip' => getClientIP()
        ]);
        
        echo json_encode([
            'success' => true,
            'token' => $newTokens['access_token'],
            'refresh_token' => $newTokens['refresh_token'],
            'token_type' => $newTokens['token_type'],
            'expires_in' => $newTokens['expires_in']
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Refresh token tidak valid atau sudah kadaluarsa'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ]);
}
