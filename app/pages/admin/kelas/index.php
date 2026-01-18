<?php
// app/pages/admin/kelas/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

// Cek Session Admin (sederhana)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
     // Kalau belum login admin, redirect
     // redirect('app/pages/auth/login.php');
     // Untuk sementara di-comment dulu agar bisa di-preview tanpa login admin
}

// Fetch Data Kelas
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT k.*, 
    (SELECT COUNT(DISTINCT a.user_id) 
     FROM tb_absensi a 
     JOIN tb_siswa s ON a.user_id = s.id 
     WHERE s.id_kelas = k.id 
     AND a.role = 'siswa' 
     AND a.tanggal = ? 
     AND a.status IN ('hadir', 'terlambat')
    ) as jumlah_hadir
    FROM tb_kelas k 
    ORDER BY k.created_at DESC
");
$stmt->execute([$today]);
$kelas = $stmt->fetchAll();
// --- Header Profile Logic ---
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$nama_peran = 'Admin Global';
$initial = substr($admin_name, 0, 1);

if ($admin_id) {
    $stmtPeran = $pdo->prepare("SELECT m.nama_mapel, k.nama_kelas FROM tb_admin a LEFT JOIN tb_guru g ON a.nuptk = g.nuptk LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id WHERE a.id = ?");
    $stmtPeran->execute([$admin_id]);
    $peran = $stmtPeran->fetch();
    
    $roles = [];
    if (!empty($peran['nama_mapel'])) $roles[] = "Guru " . $peran['nama_mapel'];
    if (!empty($peran['nama_kelas'])) $roles[] = "Wali Kelas " . $peran['nama_kelas'];
    elseif (isset($_SESSION['admin_kelas_id']) && $_SESSION['admin_kelas_id']) {
        $stmtKelas = $pdo->prepare("SELECT nama_kelas FROM tb_kelas WHERE id = ?");
        $stmtKelas->execute([$_SESSION['admin_kelas_id']]);
        if ($k = $stmtKelas->fetch()) $roles[] = "Wali Kelas " . $k['nama_kelas'];
    }
    if (!empty($roles)) $nama_peran = "Admin Global (" . implode(" & ", $roles) . ")";
}
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Data Kelas</h2>
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

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-gray-700 text-3xl font-medium">Daftar Kelas</h3>
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Kelas
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                No
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Nama Kelas
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Jumlah Siswa (Kapasitas)
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Siswa Hadir (Hari Ini)
                            </th>
                            
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($kelas) > 0): ?>
                            <?php foreach($kelas as $index => $row): ?>
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= $index + 1 ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap font-semibold"><?= htmlspecialchars($row['nama_kelas']) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                        <span aria-hidden="true" class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                        <span class="relative"><?= $row['jumlah_siswa'] ?> Siswa</span>
                                    </span>
                                </td>

                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <span class="relative inline-block px-3 py-1 font-semibold text-indigo-900 leading-tight">
                                        <span aria-hidden="true" class="absolute inset-0 bg-indigo-100 opacity-50 rounded-full"></span>
                                        <span class="relative"><?= $row['jumlah_hadir'] ?> Hadir</span>
                                    </span>
                                </td>
                                
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md transition hover:bg-blue-200">Edit</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus kelas ini? Data siswa di dalamnya akan kehilangan relasi kelas.')" class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded-md transition hover:bg-red-200">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                                    Belum ada data kelas. Silakan tambah data baru.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
