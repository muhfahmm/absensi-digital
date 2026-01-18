<?php
// Cek apakah $pdo tersedia (dari database.php)
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tb_pengaturan_pembayaran WHERE provider = 'midtrans' LIMIT 1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $settings = null;
    }
}

if (!empty($settings)) {
    // Gunakan setting dari Database
    define('MIDTRANS_SERVER_KEY', $settings['server_key']);
    define('MIDTRANS_CLIENT_KEY',  $settings['client_key']);
    define('MIDTRANS_MERCHANT_ID', $settings['merchant_id']);
    define('MIDTRANS_IS_PRODUCTION', $settings['is_production'] == 1);
    define('MIDTRANS_IS_ACTIVE', $settings['is_active'] == 1);
} else {
    // Fallback Hardcoded (Default/Rescue)
    define('MIDTRANS_SERVER_KEY', 'your-server-key-here');
    define('MIDTRANS_CLIENT_KEY', 'your-client-key-here');
    define('MIDTRANS_MERCHANT_ID', 'your-merchant-id-here');
    define('MIDTRANS_IS_PRODUCTION', true); 
    define('MIDTRANS_IS_ACTIVE', false);
}
// define('MIDTRANS_IS_SANITIZED', true); // already defined below

define('MIDTRANS_IS_SANITIZED', true);
define('MIDTRANS_IS_3DS', true);
?>
