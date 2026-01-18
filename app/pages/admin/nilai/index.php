<?php
session_start();
require_once __DIR__ . '/../../../functions/helpers.php';
require_once __DIR__ . '/../../../functions/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Cek Logged In Admin
check_login('admin');

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM tb_nilai WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "<script>alert('Data berhasil dihapus'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data'); window.location.href='index.php';</script>";
    }
}

// Fetch Data
$query = "SELECT n.*, s.nama_lengkap as nama_siswa, k.nama_kelas, m.nama_mapel, g.nama_lengkap as nama_guru 
          FROM tb_nilai n 
          JOIN tb_siswa s ON n.id_siswa = s.id 
          JOIN tb_kelas k ON n.id_kelas = k.id 
          JOIN tb_mata_pelajaran m ON n.id_mapel = m.id 
          JOIN tb_guru g ON n.id_guru = g.id 
          ORDER BY n.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$nilai_list = $stmt->fetchAll();

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

<!DOCTYPE html>
<html lang="id">
<!-- Using Global Standard Pattern for Font Consistency -->
<?php require_once __DIR__ . '/../../../layouts/header.php'; ?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../../../layouts/sidebar.php'; ?>

    <!-- Content -->
    <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
            <h1 class="text-xl font-semibold text-gray-800">Manajemen Nilai</h1>
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

        <main class="w-full flex-grow p-6">
            <!-- Action Button -->
            <div class="mb-6 flex justify-between items-center">
                <div class="flex space-x-2">
                    <!-- Filter placeholder if needed -->
                </div>
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Nilai
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead class="bg-gray-50 border-b border-gray-100 text-left">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">No</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Siswa</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Kelas</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Mapel</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Tipe</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Nilai</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Guru</th>
                                <th class="px-6 py-3 font-semibold text-gray-600 uppercase text-xs tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(count($nilai_list) > 0): ?>
                                <?php foreach($nilai_list as $index => $row): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-500 text-sm"><?= $index + 1 ?></td>
                                    <td class="px-6 py-4 text-gray-600 text-sm"><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                    <td class="px-6 py-4 text-gray-800 font-medium text-sm"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                    <td class="px-6 py-4 text-gray-600 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">
                                            <?= htmlspecialchars($row['nama_kelas']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 text-sm"><?= htmlspecialchars($row['nama_mapel']) ?></td>
                                    <td class="px-6 py-4 text-gray-600 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $row['tipe_nilai'] == 'UH' ? 'bg-yellow-100 text-yellow-700' : ($row['tipe_nilai'] == 'TUGAS' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700') ?>">
                                            <?= $row['tipe_nilai'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-800 font-bold text-sm"><?= $row['nilai'] ?></td>
                                    <td class="px-6 py-4 text-gray-500 text-sm"><?= htmlspecialchars($row['nama_guru']) ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="index.php?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus nilai ini?')" class="text-red-400 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50 inline-block" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Belum ada data nilai
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
