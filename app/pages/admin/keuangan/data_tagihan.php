<?php
// app/pages/admin/keuangan/data_tagihan.php
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../layouts/header.php';
require_once '../../../config/database.php';

// Proteksi halaman admin
check_login('admin');

$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$initial = substr($admin_name, 0, 1);

// Handle Delete
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_tagihan_spp WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = "Tagihan berhasil dihapus.";
    } catch (PDOException $e) {
        $error_msg = "Gagal menghapus: " . $e->getMessage();
    }
}

// Ambil Data Tagihan
try {
    $query = "
        SELECT 
            t.*, 
            s.nama_lengkap, 
            s.nis,
            k.nama_kelas
        FROM tb_tagihan_spp t
        JOIN tb_siswa s ON t.user_id = s.id
        LEFT JOIN tb_kelas k ON s.id_kelas = k.id
        ORDER BY t.created_at DESC
    ";
    $stmt = $pdo->query($query);
    $tagihan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}

// Helper nama bulan
function getBulan($num) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $months[$num] ?? '';
}
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Data Tagihan SPP</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="mb-6 flex justify-between items-center">
                <a href="index.php" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Dashboard
                </a>
                <a href="buat_tagihan.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                    + Buat Tagihan Baru
                </a>
            </div>

            <?php if (isset($success_msg)): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    <?= $success_msg ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error_msg ?>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                <th class="p-4 font-semibold border-b">No</th>
                                <th class="p-4 font-semibold border-b">Nama Siswa</th>
                                <th class="p-4 font-semibold border-b">Kelas</th>
                                <th class="p-4 font-semibold border-b">Periode</th>
                                <th class="p-4 font-semibold border-b">Nominal</th>
                                <th class="p-4 font-semibold border-b">Status</th>
                                <th class="p-4 font-semibold border-b">Tanggal Bayar</th>
                                <th class="p-4 font-semibold border-b text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            <?php if(empty($tagihan_list)): ?>
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-gray-400">Belum ada data tagihan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($tagihan_list as $i => $row): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-gray-500 text-center"><?= $i+1 ?></td>
                                    <td class="p-4">
                                        <div class="font-medium text-gray-800"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                        <div class="text-xs text-gray-500">NIS: <?= htmlspecialchars($row['nis']) ?></div>
                                    </td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                                    <td class="p-4 text-gray-800 font-medium">
                                        <?= getBulan($row['bulan']) ?> <?= $row['tahun'] ?>
                                    </td>
                                    <td class="p-4 text-gray-800 font-bold">Rp <?= number_format($row['nominal_tagihan'], 0, ',', '.') ?></td>
                                    <td class="p-4">
                                        <?php if($row['status_bayar'] == 'lunas'): ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">Lunas</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-gray-500 text-xs">
                                        <?= $row['tanggal_bayar'] ? date('d M Y H:i', strtotime($row['tanggal_bayar'])) : '-' ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tagihan ini?');">
                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
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
