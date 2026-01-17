<?php
// app/pages/admin/dashboard.php
require_once '../../functions/helpers.php';
require_once '../../functions/auth.php';
require_once '../../layouts/header.php';

// Proteksi halaman admin
check_login('admin');

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = $_SESSION['nama'] ?? 'Admin';
$kelas_id = $_SESSION['kelas_id'] ?? null;
$nama_peran = 'Admin Global';

if ($admin_id) {
    require_once '../../config/database.php';
    
    // Cek Peran (Guru Mapel & Wali Kelas) melalui NUPTK
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
    if (!empty($peran['nama_mapel'])) {
        $roles[] = "Guru " . $peran['nama_mapel'];
    }
    if (!empty($peran['nama_kelas'])) { // Prioritas dari tabel guru
        $roles[] = "Wali Kelas " . $peran['nama_kelas'];
    } elseif ($kelas_id) { // Fallback session
        // Cek Wali Kelas dari session jika tidak ketemu di join guru
        $stmtKelas = $pdo->prepare("SELECT nama_kelas FROM tb_kelas WHERE id = ?");
        $stmtKelas->execute([$kelas_id]);
        $kelas = $stmtKelas->fetch();
        if ($kelas) {
            $roles[] = "Wali Kelas " . $kelas['nama_kelas'];
        }
    }

    if (!empty($roles)) {
        if (count($roles) > 0) {
            // Jika ada peran mapel/wali kelas, tampilkan.
            // Format: "Guru Matematika & Wali Kelas X-A" atau "Admin Global - Guru Matematika" ??
            // User request: "sekalian tambahkan kelasnya"
            // Let's prepend "Admin Global" if user wants it, or just show the specific roles since they are admin.
            // Existing logic was "Admin Global - [Mapel]".
            // Let's make it: "Admin Global - " . implode(" & ", $roles);
            $nama_peran = "Admin Global (" . implode(" & ", $roles) . ")";
        }
    }
}

$initial = substr($admin_name, 0, 1);
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../layouts/sidebar.php'; ?>
    
    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Admin Dashboard</h2>
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Card Statistik -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-50 text-indigo-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Siswa</p>
                            <p class="text-lg font-semibold text-gray-800">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-50 text-green-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Hadir Hari Ini</p>
                            <p class="text-lg font-semibold text-gray-800">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-50 text-red-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Tidak Hadir</p>
                            <p class="text-lg font-semibold text-gray-800">0</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
                <p class="text-gray-500 text-sm">Belum ada data absensi.</p>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../layouts/footer.php'; ?>
