<?php
require_once '../../../../config/database.php';
require_once '../../../../config/midtrans_config.php';

$json_result = file_get_contents('php://input');
$result = json_decode($json_result, true);

// LOGGING UNTUK DEBUGGING
file_put_contents('midtrans_log.txt', date('Y-m-d H:i:s') . " - Receive: " . $json_result . PHP_EOL, FILE_APPEND);

if (!$result) {
    http_response_code(404);
    exit;
}

$order_id = $result['order_id'];
$transaction_status = $result['transaction_status'];
$fraud_status = $result['fraud_status'];
$status_code = $result['status_code'];
$gross_amount = $result['gross_amount'];
$payment_type = $result['payment_type'];

// Signature Verification (Optional)
// Signature Verification (Optional)
$input_signature = $result['signature_key'];
$calculated_signature = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);

if ($input_signature !== $calculated_signature) {
    http_response_code(403);
    exit('Invalid Signature');
}

// Get Transaction from DB
$stmt = $pdo->prepare("SELECT * FROM tb_transaksi_midtrans WHERE order_id = ?");
$stmt->execute([$order_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    // Allow Test Notifications to pass green in Dashboard
    if (strpos($order_id, 'test') !== false || strpos($order_id, 'example') !== false) {
        http_response_code(200);
        exit('Test notification received');
    }

    http_response_code(404);
    exit('Transaction not found');
}

// Update Transaction Status
$stmt = $pdo->prepare("UPDATE tb_transaksi_midtrans SET transaction_status = ?, payment_type = ?, transaction_time = NOW() WHERE order_id = ?");
$stmt->execute([$transaction_status, $payment_type, $order_id]);

// Handle Status
if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
    if ($transaction['transaction_status'] != 'settlement' && $transaction['transaction_status'] != 'capture') {
        // Success Logic
        $user_id = $transaction['user_id'];
        $role = $transaction['role'];
        $amount = (int)$transaction['gross_amount']; // ensure int
        $tipe_transaksi = $transaction['tipe_transaksi'] ?? 'topup';
        $target_id = $transaction['target_id'] ?? null;

        if ($tipe_transaksi === 'topup') {
            try {
                $pdo->beginTransaction();
                
                // Check Saldo Existing (Lock Row)
                $stmt = $pdo->prepare("SELECT * FROM tb_saldo WHERE user_id = ? AND role = ? FOR UPDATE");
                $stmt->execute([$user_id, $role]);
                $saldo = $stmt->fetch();

                if ($saldo) {
                    // Update Existing Saldo
                    $stmt = $pdo->prepare("UPDATE tb_saldo SET saldo_saat_ini = saldo_saat_ini + ? WHERE id = ?");
                    $stmt->execute([$amount, $saldo['id']]);
                } else {
                    // Create New Saldo Record
                    $stmt = $pdo->prepare("INSERT INTO tb_saldo (user_id, role, saldo_saat_ini) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $role, $amount]);
                }

                // Record History
                $stmt = $pdo->prepare("INSERT INTO tb_riwayat_saldo (user_id, tipe, jumlah, keterangan) VALUES (?, 'masuk', ?, ?)");
                $stmt->execute([$user_id, $amount, 'Topup Midtrans ' . $order_id]);
                
                $pdo->commit();
                file_put_contents('midtrans_log.txt', date('Y-m-d H:i:s') . " - EXPLICIT COMMIT SUCCESS for $order_id" . PHP_EOL, FILE_APPEND);
            
            } catch (Exception $e) {
                $pdo->rollBack();
                file_put_contents('midtrans_log.txt', date('Y-m-d H:i:s') . " - DB ERROR ROLLBACK: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }

        } elseif ($tipe_transaksi === 'spp') {
            // === LOGIKA PEMBAYARAN SPP ===
            
            if ($target_id) {
                // Update Tagihan SPP menjadi LUNAS
                $stmt = $pdo->prepare("UPDATE tb_tagihan_spp SET status_bayar = 'lunas', tanggal_bayar = NOW(), id_transaksi_midtrans = ? WHERE id = ?");
                $stmt->execute([$order_id, $target_id]);
                
                // Opsional: Catat juga di riwayat saldo sebagai log, meski tidak mengurangi saldo (karena bayar langsung)
                // Atau biarkan kosong jika pembayaran langsung tidak masuk history saldo.
                // Jika user ingin, kita bisa masukkan sebagai 'masuk' lalu 'keluar' (tapi redundan).
                // Kita asumsikan pembayaran SPP langsung tidak menyentuh saldo E-Wallet.
            }
        }
    }
}

echo "OK";
?>
