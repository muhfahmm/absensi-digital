<?php
// app/pages/admin/keuangan/buat_tagihan.php
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../layouts/header.php';
require_once '../../../config/database.php';

// Proteksi halaman admin
check_login('admin');

$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$initial = substr($admin_name, 0, 1);
$success_msg = '';
$error_msg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];
    $nominal = str_replace(['.', ','], '', $_POST['nominal']); // Remove formatting

    if (empty($user_id) || empty($bulan) || empty($tahun) || empty($nominal)) {
        $error_msg = "Semua field harus diisi.";
    } else {
        try {
            // Cek apakah tagihan sudah ada untuk siswa & periode tersebut
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_tagihan_spp WHERE user_id = ? AND bulan = ? AND tahun = ?");
            $stmt->execute([$user_id, $bulan, $tahun]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = "Tagihan untuk siswa ini pada periode tersebut sudah ada.";
            } else {
                // Insert Tagihan Baru
                $stmt = $pdo->prepare("INSERT INTO tb_tagihan_spp (user_id, bulan, tahun, nominal_tagihan, status_bayar) VALUES (?, ?, ?, ?, 'belum')");
                if ($stmt->execute([$user_id, $bulan, $tahun, $nominal])) {
                    $success_msg = "Tagihan berhasil dibuat!";
                } else {
                    $error_msg = "Gagal membuat tagihan.";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

// Ambil Data Siswa untuk Dropdown
try {
    $stmt = $pdo->query("SELECT id, nama_lengkap, nis FROM tb_siswa ORDER BY nama_lengkap ASC");
    $siswa_list = $stmt->fetchAll();
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
            <h2 class="text-xl font-semibold text-gray-800">Buat Tagihan Baru</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm p-8">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Form Tagihan SPP</h3>
                    <a href="index.php" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali
                    </a>
                </div>

                <?php if ($success_msg): ?>
                    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                        <?= $success_msg ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="space-y-4">
                        <!-- Pilih Siswa -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Siswa</label>
                            <select name="user_id" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm p-2 border">
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach ($siswa_list as $siswa): ?>
                                    <option value="<?= $siswa['id'] ?>"><?= htmlspecialchars($siswa['nama_lengkap']) ?> (NIS: <?= $siswa['nis'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Periode (Bulan & Tahun) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                                <select name="bulan" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm p-2 border">
                                    <?php 
                                    $months = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                    foreach ($months as $num => $name): ?>
                                        <option value="<?= $num ?>" <?= date('n') == $num ? 'selected' : '' ?>><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                                <input type="number" name="tahun" value="<?= date('Y') ?>" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm p-2 border">
                            </div>
                        </div>

                        <!-- Nominal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Tagihan (Rp)</label>
                            <input type="number" name="nominal" placeholder="Contoh: 150000" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm p-2 border">
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow transition duration-200">
                                Buat Tagihan
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
