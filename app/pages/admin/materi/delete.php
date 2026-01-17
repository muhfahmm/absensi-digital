<?php
// app/pages/admin/materi/delete.php
session_start();
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $filename = $_POST['file'];
    
    // Delete File
    $filepath = "../../../../uploads/materi/" . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    // Delete DB Record
    $stmt = $pdo->prepare("DELETE FROM tb_materi WHERE id = ?");
    $stmt->execute([$id]);
    
    // Redirect
    header("Location: index.php");
    exit;
}
?>
