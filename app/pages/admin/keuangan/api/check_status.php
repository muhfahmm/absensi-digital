<?php
header('Content-Type: application/json');
require_once '../../../../config/database.php';
require_once '../../../../config/midtrans_config.php';

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
    exit;
}

// 1. Get Transaction from DB
$stmt = $pdo->prepare("SELECT * FROM tb_transaksi_midtrans WHERE order_id = ?");
$stmt->execute([$order_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit;
}

// If already success, return immediately
if ($transaction['transaction_status'] == 'settlement' || $transaction['transaction_status'] == 'capture') {
    echo json_encode([
        'status' => 'success',
        'transaction_status' => $transaction['transaction_status'],
        'message' => 'Transaction already paid'
    ]);
    exit;
}

// 2. Cek Status ke Midtrans API
$url = MIDTRANS_IS_PRODUCTION 
    ? 'https://api.midtrans.com/v2/' . $order_id . '/status'
    : 'https://api.sandbox.midtrans.com/v2/' . $order_id . '/status';

$log_msg = "Checking Order ID: $order_id\nURL: $url\nServer Key Used: " . substr(MIDTRANS_SERVER_KEY, 0, 5) . "...\n";
file_put_contents('debug_log.txt', $log_msg, FILE_APPEND);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, MIDTRANS_SERVER_KEY . ':');
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

file_put_contents('debug_log.txt', "HTTP Code: $http_code\nResult: $result\n----------------\n", FILE_APPEND);

if ($http_code == 200) {
    $midtrans_data = json_decode($result, true);
    $transaction_status = $midtrans_data['transaction_status'];
    $payment_type = $midtrans_data['payment_type'] ?? $transaction['payment_type'];
    
    // 3. Update Status di Database
    $stmt = $pdo->prepare("UPDATE tb_transaksi_midtrans SET transaction_status = ?, payment_type = ?, transaction_time = NOW() WHERE order_id = ?");
    $stmt->execute([$transaction_status, $payment_type, $order_id]);

    // 4. Logic Saldo / SPP (Copy from notification.php logic)
    if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
        
        // Success Logic
        $user_id = $transaction['user_id'];
        $role = $transaction['role'];
        $amount = (int)$transaction['gross_amount'];
        $tipe_transaksi = $transaction['tipe_transaksi'] ?? 'topup';
        $target_id = $transaction['target_id'] ?? null;

        if ($tipe_transaksi === 'topup') {
            // Cek apakah saldo sudah pernah ditambah untuk order_id ini? (Idempotency check sederhana by riwayat)
            $stmtCek = $pdo->prepare("SELECT id FROM tb_riwayat_saldo WHERE keterangan LIKE ?");
            $stmtCek->execute(['%Topup Midtrans ' . $order_id . '%']);
            if ($stmtCek->rowCount() == 0) {
                try {
                    $pdo->beginTransaction();

                    // Update Saldo
                    $stmt = $pdo->prepare("SELECT * FROM tb_saldo WHERE user_id = ? AND role = ? FOR UPDATE"); // Lock row
                    $stmt->execute([$user_id, $role]);
                    $saldo = $stmt->fetch();

                    if ($saldo) {
                        $stmt = $pdo->prepare("UPDATE tb_saldo SET saldo_saat_ini = saldo_saat_ini + ? WHERE id = ?");
                        $stmt->execute([$amount, $saldo['id']]);
                        file_put_contents('debug_log.txt', "Updated Saldo ID: {$saldo['id']} amount +$amount\n", FILE_APPEND);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO tb_saldo (user_id, role, saldo_saat_ini) VALUES (?, ?, ?)");
                        $stmt->execute([$user_id, $role, $amount]);
                        $newId = $pdo->lastInsertId();
                        file_put_contents('debug_log.txt', "Created New Saldo ID: $newId for User $user_id ($role) amount $amount\n", FILE_APPEND);
                    }

                    // Add History
                    $stmt = $pdo->prepare("INSERT INTO tb_riwayat_saldo (user_id, tipe, jumlah, keterangan) VALUES (?, 'masuk', ?, ?)");
                    $stmt->execute([$user_id, $amount, 'Topup Midtrans ' . $order_id]);
                    file_put_contents('debug_log.txt', "History Added for Order $order_id\n", FILE_APPEND);

                    $pdo->commit();
                    file_put_contents('debug_log.txt', "TRANSACTION COMMITTED\n", FILE_APPEND);

                } catch (Exception $e) {
                    $pdo->rollBack();
                    file_put_contents('debug_log.txt', "DB UPDATE ERROR (Rolled Back): " . $e->getMessage() . "\n", FILE_APPEND);
                }
            } else {
                file_put_contents('debug_log.txt', "Skipping Update - Already Processed ($order_id)\n", FILE_APPEND);
            }

        } elseif ($tipe_transaksi === 'spp') {
            if ($target_id) {
                $stmt = $pdo->prepare("UPDATE tb_tagihan_spp SET status_bayar = 'lunas', tanggal_bayar = NOW(), id_transaksi_midtrans = ? WHERE id = ?");
                $stmt->execute([$order_id, $target_id]);
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'transaction_status' => $transaction_status,
        'data' => $midtrans_data
    ]);

} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to check status from Midtrans',
        'http_code' => $http_code,
        'result' => $result
    ]);
}
?>
