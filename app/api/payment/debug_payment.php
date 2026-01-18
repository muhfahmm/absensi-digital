<?php
// app/api/payment/debug_payment.php
header('Content-Type: text/plain'); // Plain text for easy reading in browser
require_once '../../config/database.php';
require_once '../../config/midtrans_config.php';

$order_id = $_GET['order_id'] ?? null;

echo "=== DIAGNOSTIC TOOL: PAYMENT DEBUG ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n\n";

// 1. Check Configuration
echo "[1] CHECKING CONFIGURATION\n";
echo "MIDTRANS_IS_ACTIVE: " . (MIDTRANS_IS_ACTIVE ? 'YES' : 'NO') . "\n";
echo "MIDTRANS_IS_PRODUCTION: " . (MIDTRANS_IS_PRODUCTION ? 'YES' : 'NO') . "\n";
// Show partial key for verification
$key = MIDTRANS_SERVER_KEY;
$hidden_key = substr($key, 0, 8) . '...' . substr($key, -4);
echo "MIDTRANS_SERVER_KEY: " . $hidden_key . "\n";

if (MIDTRANS_IS_PRODUCTION && strpos($key, 'SB-') === 0) {
    echo "WARNING: Production Mode is ON but Key starts with 'SB-' (Sandbox). This is invalid.\n";
} elseif (!MIDTRANS_IS_PRODUCTION && strpos($key, 'SB-') !== 0) {
    echo "WARNING: Sandbox Mode is ON but Key does NOT start with 'SB-'. This is invalid.\n";
} else {
    echo "Key Format: OK\n";
}
echo "\n";

if (!$order_id) {
    echo "ERROR: No 'order_id' provided in URL.\n";
    echo "Usage: debug_payment.php?order_id=TRX-123456789\n";
    exit;
}

// 2. Check Database Record
echo "[2] CHECKING DATABASE RECORD\n";
$stmt = $pdo->prepare("SELECT * FROM tb_transaksi_midtrans WHERE order_id = ?");
$stmt->execute([$order_id]);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trx) {
    echo "ERROR: Transaction '$order_id' NOT FOUND in database.\n";
    exit;
}

echo "Found Transaction:\n";
print_r($trx);
echo "\n";

// 3. Simulate Midtrans Check
echo "[3] SIMULATING MIDTRANS API CHECK\n";
$url = MIDTRANS_IS_PRODUCTION 
    ? 'https://api.midtrans.com/v2/' . $order_id . '/status'
    : 'https://api.sandbox.midtrans.com/v2/' . $order_id . '/status';

echo "Request URL: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, MIDTRANS_SERVER_KEY . ':');
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: $result\n";

if ($http_code != 200 && $http_code != 201) {
    echo "ERROR: Midtrans API returned error. Check your Server Key.\n";
} else {
    $midtrans = json_decode($result, true);
    echo "Midtrans Status: " . ($midtrans['transaction_status'] ?? 'Unknown') . "\n";
}
echo "\n";

// 4. Check Saldo Logic
echo "[4] CHECKING SALDO LOGIC (DRY RUN)\n";
$user_id = $trx['user_id'];
$role = $trx['role'];
$tipe = $trx['tipe_transaksi'] ?? 'topup';

if ($tipe == 'topup') {
    $stmt = $pdo->prepare("SELECT * FROM tb_saldo WHERE user_id = ? AND role = ?");
    $stmt->execute([$user_id, $role]);
    $saldo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($saldo) {
        echo "Exiting Saldo Found: ID " . $saldo['id'] . ", Amount: " . $saldo['saldo_saat_ini'] . "\n";
        echo "PLAN: UPDATE tb_saldo SET saldo_saat_ini = saldo_saat_ini + " . $trx['gross_amount'] . "\n";
    } else {
        echo "No Existing Saldo.\n";
        echo "PLAN: INSERT INTO tb_saldo (user_id=$user_id, role=$role, amount=" . $trx['gross_amount'] . ")\n";
    }
} else {
    echo "Type is '$tipe', skipping saldo check.\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
?>
