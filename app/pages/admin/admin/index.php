<?php
// app/pages/admin/admin/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// Fetch Data Admin
$stmt = $pdo->query("SELECT a.*, k.nama_kelas FROM tb_admin a LEFT JOIN tb_kelas k ON a.id_kelas = k.id ORDER BY a.created_at DESC");
$admins = $stmt->fetchAll();
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Data Admin</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Admin Panel</span>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="mb-6 flex justify-between items-center">
                <div class="text-gray-600 text-sm">Kelola data administrator sistem.</div>
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Admin
                </a>
            </div>

            <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Foto</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">NIP</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">QR Code</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($admins) > 0): ?>
                            <?php foreach($admins as $index => $row): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm"><?= $index + 1 ?></td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                    <?php if(!empty($row['foto_profil']) && file_exists("../../../../uploads/admin/" . $row['foto_profil'])): ?>
                                        <img src="<?= base_url('uploads/admin/' . $row['foto_profil']) ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover shadow-sm bg-gray-100">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-[10px] font-bold">ADM</div>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-gray-400">
                                    <?= !empty($row['nip']) ? htmlspecialchars($row['nip']) : '-' ?>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                    <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-mono text-xs font-bold"><?= htmlspecialchars($row['username']) ?></span>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm font-bold text-gray-800"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                    <?php if($row['nama_kelas']): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-blue-200"><?= $row['nama_kelas'] ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">Admin Global</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-center">
                                    <?php if(!empty($row['kode_qr'])): ?>
                                        <div id="qr-admin-<?= $index ?>" class="flex justify-center"></div>
                                        <script>
                                            new QRCode(document.getElementById("qr-admin-<?= $index ?>"), {
                                                text: "<?= $row['kode_qr'] ?>",
                                                width: 50,
                                                height: 50
                                            });
                                        </script>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-indigo-100">Edit</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus data admin?')" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-red-100">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-5 py-10 border-b border-gray-100 bg-white text-sm text-center text-gray-400 italic">
                                    Belum ada data admin.
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
