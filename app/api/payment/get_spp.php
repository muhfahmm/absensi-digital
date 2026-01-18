<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

try {
    // 1. Get Unpaid Bills (Belum Lunas)
    $stmt = $pdo->prepare("SELECT * FROM tb_tagihan_spp WHERE user_id = ? AND status_bayar = 'belum' ORDER BY tahun DESC, bulan DESC");
    $stmt->execute([$user_id]);
    $unpaid = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Paid Bills (Lunas)
    $stmt = $pdo->prepare("SELECT * FROM tb_tagihan_spp WHERE user_id = ? AND status_bayar = 'lunas' ORDER BY tanggal_bayar DESC LIMIT 20");
    $stmt->execute([$user_id]);
    $paid = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format Data (Optional map month number to name)
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    foreach ($unpaid as &$item) {
        $item['bulan_nama'] = $months[$item['bulan']];
        $item['formatted_nominal'] = 'Rp ' . number_format($item['nominal_tagihan'], 0, ',', '.');
    }
    unset($item);

    foreach ($paid as &$item) {
        $item['bulan_nama'] = $months[$item['bulan']];
        $item['formatted_nominal'] = 'Rp ' . number_format($item['nominal_tagihan'], 0, ',', '.');
        $item['formatted_tanggal'] = date('d M Y H:i', strtotime($item['tanggal_bayar']));
    }
    unset($item);

    echo json_encode([
        'success' => true,
        'data' => [
            'unpaid' => $unpaid,
            'paid' => $paid
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
