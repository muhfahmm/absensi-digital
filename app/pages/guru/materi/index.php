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
    $stmt = $pdo->prepare("SELECT file_path FROM tb_materi WHERE id = ? AND id_guru = ?");
    $stmt->execute([$id, $guru_id]);
    $file = $stmt->fetch();
    
    if ($file) {
        $stmt = $pdo->prepare("DELETE FROM tb_materi WHERE id = ?");
        if ($stmt->execute([$id])) {
            if (file_exists(__DIR__ . '/../../../../uploads/materi/' . $file['file_path'])) {
                unlink(__DIR__ . '/../../../../uploads/materi/' . $file['file_path']);
            }
            echo "<script>alert('Materi berhasil dihapus'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Akses ditolak atau data tidak ditemukan'); window.location.href='index.php';</script>";
    }
}

// Fetch Materi with Strict Isolation
// Hanya menampilkan materi yang diupload oleh guru yang sedang login (Session: guru_id)
// Isolation Logic: (m.id_guru = ? OR m.id_kelas IS NULL)
$stmt = $pdo->prepare("SELECT m.*, mp.nama_mapel, k.nama_kelas 
                       FROM tb_materi m 
                       LEFT JOIN tb_mata_pelajaran mp ON m.id_mapel = mp.id 
                       LEFT JOIN tb_kelas k ON m.id_kelas = k.id 
                       WHERE m.id_guru = ? OR m.id_kelas IS NULL 
                       ORDER BY m.created_at DESC");
$stmt->execute([$guru_id]);
$materi_list = $stmt->fetchAll();

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
            <h2 class="text-xl font-semibold text-gray-800">Materi Pembelajaran</h2>
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
            <!-- Action Button -->
            <div class="mb-6 flex justify-end">
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl shadow transition-all flex items-center transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Upload Materi Baru
                </a>
            </div>

            <!-- List Materi -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(count($materi_list) > 0): ?>
                    <?php foreach($materi_list as $row): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col h-full">
                        <div class="p-5 flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="relative">
                                    <a href="index.php?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Hapus materi ini?')" class="text-gray-300 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50 block">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </div>
                            </div>
                            <h3 class="font-bold text-gray-800 text-lg mb-2 line-clamp-2"><?= htmlspecialchars($row['judul']) ?></h3>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?= htmlspecialchars($row['nama_mapel'] ?? 'Umum') ?>
                                </span>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?= htmlspecialchars($row['nama_kelas'] ?? 'Semua Kelas') ?>
                                </span>
                            </div>
                            <p class="text-gray-500 text-sm mb-4 line-clamp-3">
                                <?= htmlspecialchars($row['deskripsi'] ?: 'Tidak ada deskripsi.') ?>
                            </p>
                        </div>
                        <div class="bg-gray-50 px-5 py-3 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-xs text-gray-400 font-medium"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                            <a href="<?= base_url('uploads/materi/' . $row['file_path']) ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center group">
                                Download 
                                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-16 bg-white rounded-xl shadow-sm border-2 border-dashed border-gray-200">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                        <h3 class="text-lg font-medium text-gray-900">Belum ada materi</h3>
                        <p class="text-gray-500 mt-1">Mulai dengan mengupload materi untuk siswa Anda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
