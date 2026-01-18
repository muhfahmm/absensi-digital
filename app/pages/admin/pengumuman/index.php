<?php
// app/pages/admin/pengumuman/index.php
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

// Proteksi halaman admin
check_login('admin');

// Handle Form Submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $judul = clean_input($_POST['judul']);
        $isi = trim($_POST['isi']); 
        $target = clean_input($_POST['target_role']);

        if ($judul && $isi) {
            try {
                $stmt = $pdo->prepare("INSERT INTO tb_pengumuman (judul, isi, target_role, tanggal_publish) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$judul, $isi, $target]);
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Pengumuman berhasil ditambahkan!</div>';
            } catch (PDOException $e) {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Gagal menambahkan: ' . $e->getMessage() . '</div>';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM tb_pengumuman WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Pengumuman berhasil dihapus!</div>';
        } catch (PDOException $e) {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Gagal menghapus: ' . $e->getMessage() . '</div>';
        }
    }
}

// Fetch Data
$stmt = $pdo->query("SELECT * FROM tb_pengumuman ORDER BY tanggal_publish DESC");
$pengumumanList = $stmt->fetchAll();

// --- Header Profile Logic ---
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$kelas_id = $_SESSION['admin_kelas_id'] ?? null;
$initial = substr($admin_name, 0, 1);
$nama_peran = 'Admin Global';

if ($admin_id) {
    $stmtPeran = $pdo->prepare("
        SELECT m.nama_mapel, k.nama_kelas
        FROM tb_admin a 
        LEFT JOIN tb_guru g ON a.nuptk = g.nuptk
        LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id 
        LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id
        WHERE a.id = ?
    ");
    $stmtPeran->execute([$admin_id]);
    $peran = $stmtPeran->fetch();
    
    $roles = [];
    if ($peran) {
        if (!empty($peran['nama_mapel'])) {
            $roles[] = "Guru " . $peran['nama_mapel'];
        }
        if (!empty($peran['nama_kelas'])) {
            $roles[] = "Wali Kelas " . $peran['nama_kelas'];
        } elseif ($kelas_id) {
            $stmtK = $pdo->prepare("SELECT nama_kelas FROM tb_kelas WHERE id = ?");
            $stmtK->execute([$kelas_id]);
            $k = $stmtK->fetch();
            if ($k) {
                $roles[] = "Wali Kelas " . $k['nama_kelas'];
            }
        }
    }
    if (!empty($roles)) {
        $nama_peran = "Admin Global (" . implode(" & ", $roles) . ")";
    }
}
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../../layouts/sidebar.php'; ?>

    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Pengumuman</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <?= $nama_peran ?>
                    </p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

         <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <?= $message ?>

            <!-- Add New Form -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Buat Pengumuman Baru</h3>
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
                        <input type="text" name="judul" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Isi Pengumuman</label>
                        <textarea name="isi" required rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Target Audience</label>
                        <select name="target_role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="semua">Semua (Guru & Siswa)</option>
                            <option value="siswa">Hanya Siswa</option>
                            <option value="guru">Hanya Guru</option>
                        </select>
                    </div>
                     <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">
                        Publikasikan
                    </button>
                </form>
            </div>

            <!-- List -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800">Daftar Pengumuman</h3>
                </div>
                 <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Isi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                         <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($pengumumanList)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada pengumuman.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pengumumanList as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($item['judul']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($item['isi']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?= ucfirst($item['target_role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $item['tanggal_publish'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
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
