<?php
// app/pages/admin/scanner/launch_python.php
session_start();
require_once '../../../functions/auth.php';

// Check login
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$python_script = realpath(__DIR__ . '/../../../../desktop-scanner/main.py');
$command = "python \"$python_script\"";

// Windows specific: start in background but visible if possible, or just start
// Using 'start' command in Windows to open a new window
// We use pclose(popen(...)) to fire and forget

try {
    // This command attempts to open a new command prompt window running the python script
    // It requires Apache to be allowed to interact with desktop if running as service (rare for XAMPP users)
    // If running XAMPP Control Panel, this usually renders the window visible.
    pclose(popen("start /B cmd /c \"$command\"", "r")); 
    
    echo json_encode([
        'success' => true, 
        'message' => 'Scanner Python Sedang Dibuka...',
        'debug_cmd' => $command
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
