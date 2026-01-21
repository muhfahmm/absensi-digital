<?php
// app/api/materi.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Suppress PHP errors to avoid breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config/database.php';

try {
    // Basic query to fetch materials
    // You might want to filter this by class later if needed
    $sql = "
        SELECT m.*, g.nama_lengkap as nama_guru, mp.nama_mapel, k.nama_kelas,
        (SELECT COUNT(*) FROM tb_komentar_elearning WHERE materi_id = m.id) as total_komentar
        FROM tb_materi m
        JOIN tb_guru g ON m.id_guru = g.id
        LEFT JOIN tb_mata_pelajaran mp ON m.id_mapel = mp.id
        LEFT JOIN tb_kelas k ON m.id_kelas = k.id
        ORDER BY m.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $materials
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
