<?php
// app/config/security_config.php

// JWT Configuration
define('JWT_SECRET_KEY', 'AbsensiDigital2024!SecureKey#ChangeMeInProduction');
define('JWT_ALGORITHM', 'HS256');
define('JWT_ACCESS_TOKEN_EXPIRY', 3600); // 1 hour
define('JWT_REFRESH_TOKEN_EXPIRY', 604800); // 7 days

// Rate Limiting Configuration
define('RATE_LIMIT_MAX_ATTEMPTS', 5); // Maximum login attempts
define('RATE_LIMIT_TIME_WINDOW', 900); // 15 minutes in seconds
define('RATE_LIMIT_LOCKOUT_DURATION', 1800); // 30 minutes in seconds

// Account Lockout Configuration
define('MAX_FAILED_LOGIN_ATTEMPTS', 5);
define('ACCOUNT_LOCKOUT_DURATION', 1800); // 30 minutes in seconds

// Password Policy
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL_CHAR', false);

// CSRF Token Configuration
define('CSRF_TOKEN_LENGTH', 32);
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Security Headers
define('SECURITY_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
]);

// Content Security Policy (adjust based on your needs)
define('CSP_POLICY', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;");

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_REGENERATE_INTERVAL', 300); // 5 minutes

// IP Tracking
define('TRACK_IP_ADDRESSES', true);
define('TRACK_USER_AGENTS', true);

// Security Logging
define('LOG_SECURITY_EVENTS', true);
define('LOG_FAILED_LOGINS', true);
define('LOG_SUCCESSFUL_LOGINS', true);
define('LOG_REGISTRATION_ATTEMPTS', true);

// Allowed Origins for CORS (add your mobile app domains if needed)
define('ALLOWED_ORIGINS', [
    '*', // Allow all for development, restrict in production
]);

// Environment
define('ENVIRONMENT', 'development'); // 'development' or 'production'
define('ENABLE_HTTPS_ONLY', false); // Set to true in production with HTTPS

return [
    'jwt' => [
        'secret' => JWT_SECRET_KEY,
        'algorithm' => JWT_ALGORITHM,
        'access_expiry' => JWT_ACCESS_TOKEN_EXPIRY,
        'refresh_expiry' => JWT_REFRESH_TOKEN_EXPIRY,
    ],
    'rate_limit' => [
        'max_attempts' => RATE_LIMIT_MAX_ATTEMPTS,
        'time_window' => RATE_LIMIT_TIME_WINDOW,
        'lockout_duration' => RATE_LIMIT_LOCKOUT_DURATION,
    ],
    'password' => [
        'min_length' => PASSWORD_MIN_LENGTH,
        'require_uppercase' => PASSWORD_REQUIRE_UPPERCASE,
        'require_lowercase' => PASSWORD_REQUIRE_LOWERCASE,
        'require_number' => PASSWORD_REQUIRE_NUMBER,
        'require_special' => PASSWORD_REQUIRE_SPECIAL_CHAR,
    ],
    'security_headers' => SECURITY_HEADERS,
    'csp_policy' => CSP_POLICY,
];
