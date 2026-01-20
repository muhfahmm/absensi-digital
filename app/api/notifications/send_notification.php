<?php
// app/api/notifications/send_notification.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../../config/database.php';
require_once '../../functions/notification_helper.php';
require_once '../../functions/security_helper.php';

setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$userId = $input['user_id'] ?? null;
$role = $input['role'] ?? null;
$title = $input['title'] ?? null;
$body = $input['body'] ?? null;
$type = $input['type'] ?? 'info';
$data = $input['data'] ?? [];

if (!$userId || !$role || !$title || !$body) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: user_id, role, title, body'
    ]);
    exit;
}

// Validate role
if (!in_array($role, ['siswa', 'guru', 'admin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid role'
    ]);
    exit;
}

try {
    $result = sendNotificationToUser($pdo, $userId, $role, $title, $body, $type, $data);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification sent successfully',
            'sent' => $result['sent'],
            'failed' => $result['failed']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send notification',
            'error' => $result['error']
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
