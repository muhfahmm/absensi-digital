<?php
// app/api/pengumuman.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../../config/database.php';

$role = isset($_GET['role']) ? $_GET['role'] : 'semua';

try {
    $query = "SELECT * FROM tb_pengumuman WHERE target_role = 'semua' OR target_role = :role ORDER BY tanggal_publish DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['role' => $role]);
    $pengumuman = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $pengumuman
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
