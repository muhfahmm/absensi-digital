<?php
// app/config/notification_config.php

// Expo Push Notification Configuration
define('EXPO_PUSH_API_URL', 'https://exp.host/--/api/v2/push/send');

// Notification Schedules (24-hour format)
define('MORNING_REMINDER_TIME', '06:00');
define('MIDDAY_WARNING_TIME', '12:00');
define('AFTERNOON_REMINDER_TIME', '15:00');

// Notification Messages
define('NOTIFICATION_MESSAGES', [
    'morning_reminder' => [
        'title' => '☀️ Selamat Pagi!',
        'body' => 'Jangan lupa absen masuk hari ini ya! 📚'
    ],
    'midday_warning' => [
        'title' => '⚠️ Reminder Absensi',
        'body' => 'Kamu belum absen masuk! Segera absen sekarang'
    ],
    'afternoon_reminder' => [
        'title' => '🏠 Waktu Pulang',
        'body' => 'Jangan lupa absen keluar sebelum pulang ya! 👋'
    ],
    'attendance_success' => [
        'title' => '✅ Absensi Berhasil',
        'body' => 'Absensi kamu sudah tercatat. Terima kasih!'
    ],
    'attendance_late' => [
        'title' => '⏰ Terlambat',
        'body' => 'Kamu terlambat! Absensi sudah tercatat sebagai terlambat'
    ],
    'not_yet_checkout' => [
        'title' => '📝 Belum Absen Keluar',
        'body' => 'Kamu belum absen keluar. Jangan lupa absen keluar ya!'
    ]
]);

// Notification Types
define('NOTIFICATION_TYPE_REMINDER', 'reminder');
define('NOTIFICATION_TYPE_CONFIRMATION', 'confirmation');
define('NOTIFICATION_TYPE_WARNING', 'warning');
define('NOTIFICATION_TYPE_INFO', 'info');

// Notification Priority
define('NOTIFICATION_PRIORITY_DEFAULT', 'default');
define('NOTIFICATION_PRIORITY_HIGH', 'high');

// Rate Limiting
define('MAX_NOTIFICATIONS_PER_USER_PER_DAY', 10);

// Retry Settings
define('NOTIFICATION_RETRY_ATTEMPTS', 3);
define('NOTIFICATION_RETRY_DELAY', 5); // seconds
