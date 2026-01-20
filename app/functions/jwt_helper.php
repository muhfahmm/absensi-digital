<?php
// app/functions/jwt_helper.php

require_once __DIR__ . '/../config/security_config.php';

/**
 * Base64 URL Encode
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 URL Decode
 */
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Generate JWT Token
 * 
 * @param int $userId User ID
 * @param string $role User role (admin, siswa, guru)
 * @param array $userData Additional user data
 * @param string $type Token type (access or refresh)
 * @return string JWT token
 */
function generateJWT($userId, $role, $userData = [], $type = 'access') {
    $issuedAt = time();
    $expiry = $type === 'refresh' ? JWT_REFRESH_TOKEN_EXPIRY : JWT_ACCESS_TOKEN_EXPIRY;
    $expirationTime = $issuedAt + $expiry;
    
    // Header
    $header = [
        'typ' => 'JWT',
        'alg' => JWT_ALGORITHM
    ];
    
    // Payload
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'user_id' => $userId,
        'role' => $role,
        'type' => $type,
        'data' => $userData
    ];
    
    // Encode Header and Payload
    $encodedHeader = base64UrlEncode(json_encode($header));
    $encodedPayload = base64UrlEncode(json_encode($payload));
    
    // Create Signature
    $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", JWT_SECRET_KEY, true);
    $encodedSignature = base64UrlEncode($signature);
    
    // Create JWT
    return "$encodedHeader.$encodedPayload.$encodedSignature";
}

/**
 * Validate and Decode JWT Token
 * 
 * @param string $token JWT token
 * @return array|false Decoded payload or false if invalid
 */
function validateJWT($token) {
    if (empty($token)) {
        return false;
    }
    
    // Split token
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    
    list($encodedHeader, $encodedPayload, $encodedSignature) = $parts;
    
    // Verify signature
    $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", JWT_SECRET_KEY, true);
    $validSignature = base64UrlEncode($signature);
    
    if ($encodedSignature !== $validSignature) {
        return false;
    }
    
    // Decode payload
    $payload = json_decode(base64UrlDecode($encodedPayload), true);
    
    if (!$payload) {
        return false;
    }
    
    // Check expiration
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * Extract JWT Token from Authorization Header
 * 
 * @return string|false Token or false if not found
 */
function extractTokenFromHeader() {
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    }
    
    return false;
}

/**
 * Verify Token from Request
 * 
 * @return array|false Payload or false if invalid
 */
function verifyTokenFromRequest() {
    $token = extractTokenFromHeader();
    
    if (!$token) {
        return false;
    }
    
    return validateJWT($token);
}

/**
 * Generate Token Pair (Access + Refresh)
 * 
 * @param int $userId User ID
 * @param string $role User role
 * @param array $userData Additional user data
 * @return array Array with access_token and refresh_token
 */
function generateTokenPair($userId, $role, $userData = []) {
    return [
        'access_token' => generateJWT($userId, $role, $userData, 'access'),
        'refresh_token' => generateJWT($userId, $role, ['user_id' => $userId, 'role' => $role], 'refresh'),
        'token_type' => 'Bearer',
        'expires_in' => JWT_ACCESS_TOKEN_EXPIRY
    ];
}

/**
 * Refresh Access Token using Refresh Token
 * 
 * @param string $refreshToken Refresh token
 * @return array|false New token pair or false if invalid
 */
function refreshAccessToken($refreshToken) {
    $payload = validateJWT($refreshToken);
    
    if (!$payload || $payload['type'] !== 'refresh') {
        return false;
    }
    
    // Generate new token pair
    return generateTokenPair(
        $payload['user_id'],
        $payload['role'],
        $payload['data'] ?? []
    );
}

/**
 * Check if token is expired
 * 
 * @param string $token JWT token
 * @return bool True if expired
 */
function isTokenExpired($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return true;
    }
    
    $payload = json_decode(base64UrlDecode($parts[1]), true);
    
    if (!$payload || !isset($payload['exp'])) {
        return true;
    }
    
    return $payload['exp'] < time();
}
