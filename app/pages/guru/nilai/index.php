<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../functions/helpers.php';
require_once __DIR__ . '/../../../functions/auth.php';

check_login('guru');
$guru_id = $_SESSION['guru_id'];

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Cek kepemilikan
    $stmt = $pdo->prepare("SELECT id FROM tb_nilai WHERE id = ? AND id_guru = ?");
    $stmt->execute([$id, $guru_id]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM tb_nilai WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>alert('Nilai berhasil dihapus'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Akses ditolak'); window.location.href='index.php';</script>";
    }
}

// Fetch Data
$query = "SELECT n.*, s.nama_lengkap as nama_siswa, k.nama_kelas, m.nama_mapel 
          FROM tb_nilai n 
          JOIN tb_siswa s ON n.id_siswa = s.id 
          JOIN tb_kelas k ON n.id_kelas = k.id 
          JOIN tb_mata_pelajaran m ON n.id_mapel = m.id 
          WHERE n.id_guru = ? 
          ORDER BY n.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$guru_id]);
$nilai_list = $stmt->fetchAll();

// Fetch Roles for Guru
$stmtPeran = $pdo->prepare("SELECT m.nama_mapel, k.nama_kelas 
                            FROM tb_guru g 
                            LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id 
                            LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
                            WHERE g.id = ?");
$stmtPeran->execute([$guru_id]);
$peran = $stmtPeran->fetch();

$roles = [];
if (!empty($peran['nama_mapel'])) $roles[] = "Guru " . $peran['nama_mapel'];
if (!empty($peran['nama_kelas'])) $roles[] = "Wali Kelas " . $peran['nama_kelas'];
$nama_peran = empty($roles) ? "Guru Pengajar" : implode(" & ", $roles);
?>

<!DOCTYPE html>
<html lang="id">
<!-- Using Global Standard Pattern -->
<?php require_once __DIR__ . '/../../../layouts/header.php'; ?>

<div class="flex h-screen bg-gray-50">
    <?php include __DIR__ . '/../../../layouts/sidebar_guru.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Nilai</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($_SESSION['guru_nama']) ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <?= htmlspecialchars($nama_peran) ?>
                    </p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= substr($_SESSION['guru_nama'], 0, 1) ?></div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-end">
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl shadow transition-all flex items-center transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Input Nilai
                </a>
            </div>

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
                                    <td class="px-6 py-4 text-sm">
                                        <a href="index.php?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus nilai ini?')" class="text-red-400 hover:text-red-600 transition-colors p-2 rounded-full hover:bg-red-50 inline-block" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Belum ada nilai yang diinput.
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
