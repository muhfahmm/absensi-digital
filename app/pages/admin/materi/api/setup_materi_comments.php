<?php
// app/api/setup_materi_comments.php
header("Content-Type: text/plain");
require_once '../../../../config/database.php';

try {
    // Add is_comment_enabled column to tb_materi
    // Default 1 (enabled)
    $sql = "ALTER TABLE tb_materi ADD COLUMN is_comment_enabled TINYINT(1) DEFAULT 1";
    $pdo->exec($sql);
    echo "Successfully added is_comment_enabled column to tb_materi.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column is_comment_enabled already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
