<?php
// app/pages/admin/keuangan/index.php
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../layouts/header.php';
require_once '../../../config/database.php';

// Proteksi halaman admin
check_login('admin');

$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$initial = substr($admin_name, 0, 1);

// === DATA STATISTIK ===
try {
    // 1. Total Saldo Masuk (Semua transaksi sukses)
    $stmt = $pdo->query("SELECT SUM(gross_amount) as total FROM tb_transaksi_midtrans WHERE transaction_status IN ('settlement', 'capture')");
    $total_masuk = $stmt->fetchColumn() ?: 0;

    // 2. Tagihan SPP Lunas
    $stmt = $pdo->query("SELECT COUNT(*) FROM tb_tagihan_spp WHERE status_bayar = 'lunas'");
    $spp_lunas = $stmt->fetchColumn() ?: 0;

    // 3. Tagihan SPP Belum Bayar
    $stmt = $pdo->query("SELECT COUNT(*) FROM tb_tagihan_spp WHERE status_bayar = 'belum'");
    $spp_pending = $stmt->fetchColumn() ?: 0;

    // 4. Riwayat Transaksi Terakhir (10)
    $stmt = $pdo->query("
        SELECT t.*, s.nama_lengkap as nama_siswa 
        FROM tb_transaksi_midtrans t
        LEFT JOIN tb_siswa s ON t.user_id = s.id AND t.role = 'siswa'
        ORDER BY t.created_at DESC 
        LIMIT 10
    ");
    $transactions = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Keuangan & SPP</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">Admin Global</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Transaksi Masuk</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">Rp <?= number_format($total_masuk, 0, ',', '.') ?></h3>
                        </div>
                        <div class="p-3 bg-green-50 rounded-full text-green-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">SPP Lunas</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $spp_lunas ?> <span class="text-sm text-gray-400 font-normal">Siswa</span></h3>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">SPP Belum Lunas</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $spp_pending ?> <span class="text-sm text-gray-400 font-normal">Tagihan</span></h3>
                        </div>
                        <div class="p-3 bg-yellow-50 rounded-full text-yellow-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Area -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <a href="payment_settings.php" class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition flex items-center justify-center text-purple-600 font-semibold border border-purple-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Payment Gateway
                </a>
                <a href="#" class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition flex items-center justify-center text-indigo-600 font-semibold border border-indigo-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Setting Biaya SPP (Gedung)
                </a>
                <a href="#" class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition flex items-center justify-center text-blue-600 font-semibold border border-blue-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Data Tagihan
                </a>
                <a href="#" class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition flex items-center justify-center text-green-600 font-semibold border border-green-100">
                     <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Buat Tagihan Baru
                </a>
            </div>

            <!-- Transaction Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Transaksi Terakhir</h3>
                    <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Lihat Semua</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                <th class="p-4 font-semibold border-b">No</th>
                                <th class="p-4 font-semibold border-b">Order ID</th>
                                <th class="p-4 font-semibold border-b">Siswa</th>
                                <th class="p-4 font-semibold border-b">Tipe</th>
                                <th class="p-4 font-semibold border-b">Jumlah</th>
                                <th class="p-4 font-semibold border-b">Status</th>
                                <th class="p-4 font-semibold border-b">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            <?php if(empty($transactions)): ?>
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-400">Belum ada data transaksi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($transactions as $i => $row): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-gray-500 text-center"><?= $i+1 ?></td>
                                    <td class="p-4 text-gray-700 font-mono text-xs"><?= $row['order_id'] ?></td>
                                    <td class="p-4 text-gray-800 font-medium"><?= htmlspecialchars($row['nama_siswa'] ?? 'Unknown') ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded-md text-xs font-bold uppercase <?= $row['tipe_transaksi'] == 'topup' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' ?>">
                                            <?= $row['tipe_transaksi'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-800 font-bold">Rp <?= number_format($row['gross_amount'], 0, ',', '.') ?></td>
                                    <td class="p-4">
                                        <?php 
                                            $status_color = 'bg-gray-100 text-gray-600';
                                            if($row['transaction_status'] == 'settlement' || $row['transaction_status'] == 'capture') $status_color = 'bg-green-100 text-green-600';
                                            if($row['transaction_status'] == 'pending') $status_color = 'bg-yellow-100 text-yellow-600';
                                            if($row['transaction_status'] == 'expire' || $row['transaction_status'] == 'cancel') $status_color = 'bg-red-100 text-red-600';
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $status_color ?>">
                                            <?= ucfirst($row['transaction_status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-500 text-xs">
                                        <?= date('d M Y H:i', strtotime($row['transaction_time'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
