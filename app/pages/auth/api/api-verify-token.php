<?php
// app/api/verify_token.php - Verify JWT Token
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/security_config.php';
require_once __DIR__ . '/../../../../functions/jwt_helper.php';
require_once __DIR__ . '/../../../../functions/security_helper.php';

// Set security headers
setSecurityHeaders();
header('Content-Type: application/json');

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verify token from request
$payload = verifyTokenFromRequest();

if ($payload) {
    echo json_encode([
        'success' => true,
        'valid' => true,
        'user_id' => $payload['user_id'],
        'role' => $payload['role'],
        'data' => $payload['data'],
        'expires_at' => $payload['exp']
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Token tidak valid atau sudah kadaluarsa'
    ]);
}
