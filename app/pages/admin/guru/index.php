<?php
// app/pages/admin/guru/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// Fetch Data Guru
$stmt = $pdo->query("SELECT g.*, k.nama_kelas as kelas_wali FROM tb_guru g LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id ORDER BY g.created_at DESC");
$guru = $stmt->fetchAll();
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Data Guru</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Admin Panel</span>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <?php 
                $view = $_GET['view'] ?? 'guru'; 
            ?>
            
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex bg-white p-1 rounded-xl shadow-sm border border-gray-200">
                    <a href="?view=guru" class="px-6 py-2 rounded-lg text-sm font-bold transition <?= $view == 'guru' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-indigo-600' ?>">
                        Data Guru
                    </a>
                    <a href="?view=mapel" class="px-6 py-2 rounded-lg text-sm font-bold transition <?= $view == 'mapel' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-indigo-600' ?>">
                        Mata Pelajaran
                    </a>
                </div>

                <?php if($view == 'guru'): ?>
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Guru
                </a>
                <?php endif; ?>

                <?php if($view == 'mapel'): ?>
                <a href="create_mapel.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Mapel
                </a>
                <?php endif; ?>
            </div>

            <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                            <?php if($view == 'mapel'): ?>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Mata Pelajaran</th>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Guru</th>
                            <?php else: ?>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Foto</th>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">NIP</th>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                                <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">QR Code</th>
                            <?php endif; ?>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($view == 'mapel') {
                            // Join dengan tb_guru untuk mengambil nama guru pengampu
                            // Menggunakan GROUP_CONCAT jika satu mapel diajar oleh lebih dari satu guru
                            $stmt = $pdo->query("
                                SELECT m.id, m.nama_mapel, 
                                GROUP_CONCAT(g.nama_lengkap SEPARATOR ', ') as nama_guru 
                                FROM tb_mata_pelajaran m 
                                LEFT JOIN tb_guru g ON m.id = g.guru_mapel_id 
                                GROUP BY m.id 
                                ORDER BY m.nama_mapel ASC
                            ");
                            $data = $stmt->fetchAll();
                        } else {
                            $data = $guru;
                        }
                        ?>

                        <?php if(count($data) > 0): ?>
                            <?php foreach($data as $index => $row): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm"><?= $index + 1 ?></td>
                                
                                <?php if($view == 'mapel'): ?>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm font-bold text-gray-700">
                                        <?= htmlspecialchars($row['nama_mapel'], ENT_QUOTES, 'UTF-8', false) ?>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-left">
                                        <?php if (!empty($row['nama_guru'])): ?>
                                            <span class="text-gray-700 text-sm"><?= htmlspecialchars($row['nama_guru']) ?></span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs italic">Belum ada guru</span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                        <?php if(!empty($row['foto_profil']) && file_exists("../../../../uploads/guru/" . $row['foto_profil'])): ?>
                                            <img src="<?= base_url('uploads/guru/' . $row['foto_profil']) ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover shadow-sm bg-gray-100">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-[10px] font-bold">GURU</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm"><?= htmlspecialchars($row['nip']) ?></td>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm font-semibold text-gray-700"><?= htmlspecialchars($row['username'] ?? '-') ?></td>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                        <div class="font-bold text-gray-800"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                        <?php if($row['kelas_wali']): ?>
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-blue-200"><?= $row['kelas_wali'] ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-center">
                                        <div id="qr-guru-<?= $index ?>" class="flex justify-center"></div>
                                        <script>
                                            new QRCode(document.getElementById("qr-guru-<?= $index ?>"), {
                                                text: "<?= $row['kode_qr'] ?>",
                                                width: 50,
                                                height: 50
                                            });
                                        </script>
                                    </td>
                                <?php endif; ?>

                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-center">
                                    <div class="flex justify-center space-x-2">
                                        <?php if($view == 'mapel'): ?>
                                            <a href="edit_mapel.php?id=<?= $row['id'] ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-indigo-100">Edit</a>
                                            <a href="delete_mapel.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus Mata Pelajaran?')" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-red-100">Hapus</a>
                                        <?php else: ?>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-indigo-100">Edit</a>
                                            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus data guru?')" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-red-100">Hapus</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-5 py-10 border-b border-gray-100 bg-white text-sm text-center text-gray-400 italic">
                                    Belum ada data <?= $view ?>.
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
