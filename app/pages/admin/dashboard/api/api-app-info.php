<?php
// app/api/app_info.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Tentukan versi aplikasi saat ini di server
// Ubah nilai ini jika ada update besar dan ingin memaksa user logout/refresh
$server_version = "1.0.0"; 

echo json_encode([
    'success' => true,
    'version' => $server_version,
    'message' => 'Aplikasi berjalan normal'
]);
?>
