<?php
// app/pages/admin/materi/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// 1. Fetch Data Guru (To filter material by teacher)
$stmtGuru = $pdo->query("SELECT id, nama_lengkap FROM tb_guru ORDER BY nama_lengkap ASC");
$all_guru = $stmtGuru->fetchAll();

// 2. Fetch Materials with Filtering
// 1. Determine User Role (Teacher or Admin)
// 1. Determine User Role & ID
$current_user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? ''; // 'admin' usually

// Logic: 
// - If Role is 'admin' -> Show ALL materials (Allow filtering).
// - If Role is 'guru' (or others) -> Show ONLY their materials.

$is_admin = ($user_role === 'admin');
$params = [];

$sql = "
    SELECT m.*, g.nama_lengkap as nama_guru, mp.nama_mapel, k.nama_kelas 
    FROM tb_materi m
    JOIN tb_guru g ON m.id_guru = g.id
    LEFT JOIN tb_mata_pelajaran mp ON m.id_mapel = mp.id
    LEFT JOIN tb_kelas k ON m.id_kelas = k.id
";

if ($is_admin) {
    // Admin Mode: Allow Filter
    $filter_guru = $_GET['guru'] ?? '';
    if ($filter_guru) {
        $sql .= " WHERE m.id_guru = ?";
        $params[] = $filter_guru;
    }
} else {
    // Guru Mode: Strict Privacy
    $sql .= " WHERE m.id_guru = ?";
    $params[] = $current_user_id;
}

$sql .= " ORDER BY m.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll();

// --- Header Profile Logic ---
$admin_name = $_SESSION['nama'] ?? 'Admin';
$nama_peran = 'Admin Global';
$initial = substr($admin_name, 0, 1);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Materi Pembelajaran</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        <?= $nama_peran ?>
                    </p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <!-- Actions & Filter -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-2">
                    <?php if ($is_admin): ?>
                        <form action="" method="GET" class="flex items-center space-x-2">
                            <select name="guru" class="form-select text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="this.form.submit()">
                                <option value="">-- Semua Guru --</option>
                                <?php foreach($all_guru as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ($filter_guru ?? '') == $g['id'] ? 'selected' : '' ?>>
                                        <?= $g['nama_lengkap'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php else: ?>
                        <span class="text-gray-700 font-bold text-lg">Materi Saya</span>
                    <?php endif; ?>
                </div>

                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Upload Materi Baru
                </a>
            </div>

            <!-- Material Cards Grid -->
            <?php if(count($materials) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($materials as $m): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition overflow-hidden flex flex-col">
                            <div class="p-5 flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-semibold uppercase tracking-wide">
                                        <?= $m['tipe_file'] ? strtoupper($m['tipe_file']) : 'FILE' ?>
                                    </span>
                                    <div class="text-right text-xs text-gray-500">
                                        <?= date('d M Y', strtotime($m['created_at'])) ?>
                                    </div>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-1 leading-tight"><?= htmlspecialchars($m['judul']) ?></h3>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($m['deskripsi']) ?></p>
                                
                                <div class="flex items-center mb-4 text-xs text-gray-500 space-x-4">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        <?= $m['nama_guru'] ?>
                                    </div>
                                    <?php if($m['nama_kelas']): ?>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <?= $m['nama_kelas'] ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-5 py-3 border-t border-gray-100 flex justify-between items-center">
                                <a href="<?= base_url('uploads/materi/' . $m['file_path']) ?>" download class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download
                                </a>
                                <form action="delete.php" method="POST" onsubmit="return confirm('Hapus materi ini?');">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="file" value="<?= $m['file_path'] ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm p-10 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-gray-500 text-lg">Belum ada materi pembelajaran yang diunggah.</p>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
