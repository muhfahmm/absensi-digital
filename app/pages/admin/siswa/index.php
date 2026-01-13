<?php
// app/pages/admin/siswa/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// Fetch Data Siswa JOIN Kelas
$sql = "SELECT tb_siswa.*, tb_kelas.nama_kelas 
        FROM tb_siswa 
        LEFT JOIN tb_kelas ON tb_siswa.id_kelas = tb_kelas.id 
        ORDER BY tb_siswa.created_at DESC";
$stmt = $pdo->query($sql);
$siswa = $stmt->fetchAll();
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Data Siswa</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Admin Panel</span>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-gray-700 text-3xl font-medium">Daftar Siswa</h3>
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Siswa
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Foto</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIS</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kelas</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">QR Code</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($siswa) > 0): ?>
                            <?php foreach($siswa as $index => $row): ?>
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= $index + 1 ?></td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <?php if(!empty($row['foto']) && file_exists("../../../../uploads/siswa/" . $row['foto'])): ?>
                                        <img src="<?= base_url('uploads/siswa/' . $row['foto']) ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">No Img</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= htmlspecialchars($row['nis']) ?></td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-semibold"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"><?= $row['nama_kelas'] ?? 'Belum ada kelas' ?></span>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= $row['kode_qr'] ?>" alt="QR" class="w-10 h-10 inline-block">
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md transition hover:bg-blue-200">Edit</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data siswa ini?')" class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded-md transition hover:bg-red-200">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">Belum ada data siswa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
