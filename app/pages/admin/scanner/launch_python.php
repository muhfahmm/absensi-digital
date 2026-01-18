<?php
// app/pages/admin/scanner/launch_python.php
session_start();
require_once '../../../functions/auth.php';

// Check login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Tentukan path script Python
    $python_script = realpath(__DIR__ . '/../../../../desktop-scanner/main.py');
    
    if (!$python_script || !file_exists($python_script)) {
        throw new Exception("File main.py tidak ditemukan. Cek path: " . $python_script);
    }
    
    $working_dir = dirname($python_script);
    
    // 2. Susun Command Windows
    // "start" membuka window baru (asynchronously)
    // "/d" menentukan working directory (penting agar import/file relatif jalan)
    // "cmd /k" menjaga window tetap terbuka (bisa diganti /c kalau mau auto-close)
    $command = "start /d \"$working_dir\" cmd /k python \"$python_script\"";
    
    // 3. Eksekusi
    pclose(popen($command, "r"));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Scanner Python Sedang Dibuka...',
        'path' => $python_script,
        'cmd' => $command
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
