<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../config/midtrans_config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['amount']) || !isset($data['type'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$user_id = $data['user_id'];
$role = isset($data['role']) ? $data['role'] : 'siswa'; // default siswa
$amount = $data['amount'];
$type = $data['type']; // 'topup', 'spp', etc.
$target_id = $data['target_id'] ?? null;
$order_id = 'TRX-' . time() . '-' . rand(100, 999);

// Get User Info
try {
    if ($role === 'siswa') {
        $stmt = $pdo->prepare("SELECT * FROM tb_siswa WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tb_guru WHERE id = ?");
    }
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }

    $customer_details = [
        'first_name' => $user['nama_lengkap'],
        'email' => preg_replace('/[^a-zA-Z0-9]/', '', $user['username']) . '@school.com', // Ensure valid email format
        'phone' => '080000000000', // Placeholder
    ];

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

// Prepare Transaction Data
$transaction_details = [
    'order_id' => $order_id,
    'gross_amount' => (int)$amount,
];

// Item Details (Optional but good for display)
$item_details = [
    [
        'id' => $type . '-01',
        'price' => (int)$amount,
        'quantity' => 1,
        'name' => ucfirst($type) . ' Payment',
    ]
];

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
// Dynamic path construction
$current_dir = dirname($_SERVER['SCRIPT_NAME']); // e.g., /absensi-digital 3/app/api/payment
// Ensure spaces are encoded for URL
$current_dir_url = str_replace(' ', '%20', $current_dir); // /absensi-digital%203/app/api/payment
$finish_redirect_url = $base_url . $current_dir_url . "/payment_finish.php?order_id=$order_id";

$params = [
    'transaction_details' => $transaction_details,
    'item_details' => $item_details,
    'customer_details' => $customer_details,
    'callbacks' => [
        'finish' => $finish_redirect_url
    ]
];

if (MIDTRANS_IS_3DS) {
    $params['credit_card'] = ['secure' => true];
}

// Insert into Database
try {
    $stmt = $pdo->prepare("INSERT INTO tb_transaksi_midtrans (order_id, user_id, role, gross_amount, tipe_transaksi, target_id, transaction_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$order_id, $user_id, $role, $amount, $type, $target_id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// Call Midtrans API
$url = MIDTRANS_IS_PRODUCTION 
    ? 'https://app.midtrans.com/snap/v1/transactions' 
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_USERPWD, MIDTRANS_SERVER_KEY . ':');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 201) {
    $response = json_decode($result, true);
    $token = $response['token'];
    $redirect_url = $response['redirect_url'];

    // Update Token in DB
    $stmt = $pdo->prepare("UPDATE tb_transaksi_midtrans SET snap_token = ? WHERE order_id = ?");
    $stmt->execute([$token, $order_id]);

    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'redirect_url' => $redirect_url,
        'order_id' => $order_id
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Midtrans Error: ' . $result]);
}
?>
