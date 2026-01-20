<?php
// app/functions/notification_helper.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/notification_config.php';

/**
 * Send push notification via Expo Push API
 */
function sendExpoPushNotification($pushToken, $title, $body, $data = [], $priority = 'default') {
    $message = [
        'to' => $pushToken,
        'sound' => 'default',
        'title' => $title,
        'body' => $body,
        'data' => $data,
        'priority' => $priority,
        'channelId' => 'default'
    ];
    
    $ch = curl_init(EXPO_PUSH_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Accept-Encoding: gzip, deflate'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['data'][0]['status']) && $result['data'][0]['status'] === 'ok') {
            return ['success' => true, 'response' => $result];
        }
    }
    
    return ['success' => false, 'error' => $response, 'http_code' => $httpCode];
}

/**
 * Send notification to specific user
 */
function sendNotificationToUser($pdo, $userId, $role, $title, $body, $type = 'info', $data = []) {
    try {
        // Get user's push tokens
        $tokens = getUserPushTokens($pdo, $userId, $role);
        
        if (empty($tokens)) {
            return ['success' => false, 'error' => 'No push tokens found for user'];
        }
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($tokens as $tokenData) {
            $result = sendExpoPushNotification($tokenData['push_token'], $title, $body, $data);
            
            if ($result['success']) {
                $successCount++;
                logNotification($pdo, $userId, $role, $title, $body, $type, 'success');
            } else {
                $failCount++;
                logNotification($pdo, $userId, $role, $title, $body, $type, 'failed', $result['error']);
            }
        }
        
        return [
            'success' => $successCount > 0,
            'sent' => $successCount,
            'failed' => $failCount
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send bulk notifications to multiple users
 */
function sendBulkNotifications($pdo, $userIds, $role, $title, $body, $type = 'info', $data = []) {
    $results = [];
    
    foreach ($userIds as $userId) {
        $result = sendNotificationToUser($pdo, $userId, $role, $title, $body, $type, $data);
        $results[] = [
            'user_id' => $userId,
            'result' => $result
        ];
    }
    
    return $results;
}

/**
 * Get user's push tokens
 */
function getUserPushTokens($pdo, $userId, $role) {
    $stmt = $pdo->prepare("SELECT push_token, device_name FROM tb_push_tokens WHERE user_id = ? AND role = ?");
    $stmt->execute([$userId, $role]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Save or update push token
 */
function savePushToken($pdo, $userId, $role, $pushToken, $deviceName = null) {
    try {
        // Check if token already exists
        $stmt = $pdo->prepare("SELECT id FROM tb_push_tokens WHERE user_id = ? AND role = ? AND push_token = ?");
        $stmt->execute([$userId, $role, $pushToken]);
        
        if ($stmt->fetch()) {
            // Update existing token
            $stmt = $pdo->prepare("UPDATE tb_push_tokens SET device_name = ?, updated_at = NOW() WHERE user_id = ? AND role = ? AND push_token = ?");
            $stmt->execute([$deviceName, $userId, $role, $pushToken]);
        } else {
            // Insert new token
            $stmt = $pdo->prepare("INSERT INTO tb_push_tokens (user_id, role, push_token, device_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $role, $pushToken, $deviceName]);
        }
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Delete push token (for logout)
 */
function deletePushToken($pdo, $pushToken) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_push_tokens WHERE push_token = ?");
        $stmt->execute([$pushToken]);
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Get users who haven't attended yet
 */
function getUnattendedUsers($pdo, $date, $role) {
    $sql = "";
    
    if ($role === 'siswa') {
        $sql = "SELECT s.id, s.nama_lengkap, s.nis 
                FROM tb_siswa s 
                LEFT JOIN tb_absensi a ON s.id = a.user_id AND a.role = 'siswa' AND a.tanggal = ?
                WHERE a.id IS NULL";
    } else if ($role === 'guru') {
        $sql = "SELECT g.id, g.nama_lengkap, g.nuptk 
                FROM tb_guru g 
                LEFT JOIN tb_absensi a ON g.id = a.user_id AND a.role = 'guru' AND a.tanggal = ?
                WHERE a.id IS NULL";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get users who haven't checked out yet
 */
function getNotCheckedOutUsers($pdo, $date, $role) {
    $sql = "";
    
    if ($role === 'siswa') {
        $sql = "SELECT s.id, s.nama_lengkap, s.nis 
                FROM tb_siswa s 
                INNER JOIN tb_absensi a ON s.id = a.user_id AND a.role = 'siswa' AND a.tanggal = ?
                WHERE a.jam_keluar IS NULL";
    } else if ($role === 'guru') {
        $sql = "SELECT g.id, g.nama_lengkap, g.nuptk 
                FROM tb_guru g 
                INNER JOIN tb_absensi a ON g.id = a.user_id AND a.role = 'guru' AND a.tanggal = ?
                WHERE a.jam_keluar IS NULL";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Log notification to database
 */
function logNotification($pdo, $userId, $role, $title, $body, $type, $status, $errorMessage = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO tb_notification_logs (user_id, role, title, body, notification_type, status, error_message) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $role, $title, $body, $type, $status, $errorMessage]);
        return true;
    } catch (PDOException $e) {
        error_log("Failed to log notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get notification statistics
 */
function getNotificationStats($pdo, $startDate = null, $endDate = null) {
    $sql = "SELECT 
                notification_type,
                status,
                COUNT(*) as count
            FROM tb_notification_logs
            WHERE 1=1";
    
    $params = [];
    
    if ($startDate) {
        $sql .= " AND sent_at >= ?";
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $sql .= " AND sent_at <= ?";
        $params[] = $endDate;
    }
    
    $sql .= " GROUP BY notification_type, status";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
