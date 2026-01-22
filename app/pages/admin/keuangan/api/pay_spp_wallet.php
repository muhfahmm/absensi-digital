<?php
// app/api/payment/pay_spp_wallet.php
header('Content-Type: application/json');
require_once '../../../../config/database.php';

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;
$tagihan_id = $input['tagihan_id'] ?? null;

if (!$user_id || !$tagihan_id) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Get Tagihan Detail (Lock Row)
    $stmt = $pdo->prepare("SELECT * FROM tb_tagihan_spp WHERE id = ? FOR UPDATE");
    $stmt->execute([$tagihan_id]);
    $tagihan = $stmt->fetch();

    if (!$tagihan) {
        throw new Exception("Tagihan tidak ditemukan");
    }

    if ($tagihan['status_bayar'] === 'lunas') {
        throw new Exception("Tagihan sudah lunas");
    }
    
    // Authorization check
    if ($tagihan['user_id'] != $user_id) {
        throw new Exception("Tagihan ini bukan milik anda");
    }

    $amount = $tagihan['nominal_tagihan'];

    // 2. Get User Saldo (Lock Row)
    $stmt = $pdo->prepare("SELECT * FROM tb_saldo WHERE user_id = ? AND role = 'siswa' FOR UPDATE");
    $stmt->execute([$user_id]);
    $saldoData = $stmt->fetch();

    if (!$saldoData) {
        throw new Exception("Saldo wallet belum aktif. Silakan Top Up terlebih dahulu.");
    }

    $currentSaldo = $saldoData['saldo_saat_ini'];

    if ($currentSaldo < $amount) {
        throw new Exception("Saldo E-Wallet tidak mencukupi. Sisa saldo: Rp " . number_format($currentSaldo, 0, ',', '.'));
    }

    // 3. Deduct Saldo
    $stmt = $pdo->prepare("UPDATE tb_saldo SET saldo_saat_ini = saldo_saat_ini - ? WHERE id = ?");
    $stmt->execute([$amount, $saldoData['id']]);

    // 4. Record Saldo History
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $bulanNama = $months[$tagihan['bulan']];
    $keterangan = "Pembayaran SPP $bulanNama {$tagihan['tahun']}";

    $stmt = $pdo->prepare("INSERT INTO tb_riwayat_saldo (user_id, tipe, jumlah, keterangan) VALUES (?, 'keluar', ?, ?)");
    $stmt->execute([$user_id, $amount, $keterangan]);

    // 5. Mark Tagihan as Paid
    $stmt = $pdo->prepare("UPDATE tb_tagihan_spp SET status_bayar = 'lunas', tanggal_bayar = NOW(), id_transaksi_midtrans = 'WALLET' WHERE id = ?");
    $stmt->execute([$tagihan_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Pembayaran Berhasil!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
