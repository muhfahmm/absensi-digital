<?php
// app/pages/admin/materi/delete.php
session_start();
require_once '../../../config/database.php';
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';

check_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $filename = $_POST['file'];
    
    // Authorization Check:
    // Super Admin (ID 13) can delete anything.
    // Others can only delete their own.
    if ($_SESSION['admin_id'] != 13) {
        $stmtCheck = $pdo->prepare("
            SELECT m.id 
            FROM tb_materi m
            JOIN tb_guru g ON m.id_guru = g.id
            JOIN tb_admin a ON g.nuptk = a.nuptk
            WHERE m.id = ? AND a.id = ?
        ");
        $stmtCheck->execute([$id, $_SESSION['admin_id']]);
        if (!$stmtCheck->fetch()) {
            die("Error: Anda tidak memiliki izin untuk menghapus materi ini.");
        }
    }

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
