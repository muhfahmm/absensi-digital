<?php
require_once 'app/config/database.php';

$sql = file_get_contents('database/update_payment_table.sql');

try {
    $pdo->exec($sql);
    echo "Tabel Payment berhasil dibuat/diupdate.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
