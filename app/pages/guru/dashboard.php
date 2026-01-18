<?php
// app/pages/guru/dashboard.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/auth.php';
require_once '../../config/database.php';
require_once '../../layouts/header.php';

check_login('guru');

$guru_id = $_SESSION['guru_id'];
$guru_nama = $_SESSION['guru_nama'];
$initial = substr($guru_nama, 0, 1);

// Fetch Role Details (Mapel & Wali Kelas)
// Gunakan query yang aman
$stmtRole = $pdo->prepare("
    SELECT m.nama_mapel, k.nama_kelas 
    FROM tb_guru g 
    LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id 
    LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
    WHERE g.id = ?
");
$stmtRole->execute([$guru_id]);
$roleInfo = $stmtRole->fetch();

$roles = [];
if (!empty($roleInfo['nama_mapel'])) $roles[] = "Guru " . $roleInfo['nama_mapel'];
if (!empty($roleInfo['nama_kelas'])) $roles[] = "Wali Kelas " . $roleInfo['nama_kelas'];

$nama_peran = empty($roles) ? "Guru Pengajar" : implode(" & ", $roles);

// Ambil data absensi hari ini per kelas
$today = date('Y-m-d');
$sql = "SELECT 
            tb_kelas.id as kelas_id,
            tb_kelas.nama_kelas,
            COUNT(DISTINCT tb_siswa.id) as total_siswa,
            COUNT(DISTINCT CASE 
                WHEN tb_absensi.user_id IS NOT NULL 
                AND tb_absensi.role = 'siswa' 
                AND tb_absensi.tanggal = :today 
                THEN tb_siswa.id 
            END) as total_hadir
        FROM tb_kelas
        LEFT JOIN tb_siswa ON tb_kelas.id = tb_siswa.id_kelas
        LEFT JOIN tb_absensi ON tb_siswa.id = tb_absensi.user_id 
            AND tb_absensi.role = 'siswa'
            AND tb_absensi.tanggal = :today
        GROUP BY tb_kelas.id, tb_kelas.nama_kelas
        ORDER BY tb_kelas.nama_kelas ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':today' => $today]);
$kelas_absensi = $stmt->fetchAll();

// Calculate Totals for Stats Cards
$total_siswa_all = 0;
$total_hadir_all = 0;
foreach ($kelas_absensi as $k) {
    $total_siswa_all += $k['total_siswa'];
    $total_hadir_all += $k['total_hadir'];
}
$total_absen_all = $total_siswa_all - $total_hadir_all;
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../layouts/sidebar_guru.php'; ?>
    
    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Dashboard Guru</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($guru_nama) ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <?= htmlspecialchars($nama_peran) ?>
                    </p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <!-- Stats Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Card 1: Total Siswa -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-50 text-indigo-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Siswa Ajar</p>
                            <p class="text-3xl font-bold text-gray-800"><?= $total_siswa_all ?></p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Hadir -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-50 text-green-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Hadir Hari Ini</p>
                            <p class="text-3xl font-bold text-gray-800"><?= $total_hadir_all ?></p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Tidak Hadir -->
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-50 text-red-500">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Belum Absen</p>
                            <p class="text-3xl font-bold text-gray-800"><?= $total_absen_all ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Class Attendance List -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Absensi Kelas Hari Ini</h3>
                        <span class="text-sm text-gray-500"><?= date('d F Y') ?></span>
                    </div>

                    <?php if(count($kelas_absensi) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach($kelas_absensi as $kelas): ?>
                                <?php 
                                    $persentase = $kelas['total_siswa'] > 0 
                                        ? round(($kelas['total_hadir'] / $kelas['total_siswa']) * 100) 
                                        : 0;
                                    
                                    $color_class = $persentase >= 80 ? 'bg-green-500' : ($persentase >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                                ?>
                                <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-bold text-gray-700"><?= htmlspecialchars($kelas['nama_kelas']) ?></h4>
                                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            <?= $kelas['total_hadir'] ?>/<?= $kelas['total_siswa'] ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="<?= $color_class ?> h-2 rounded-full transition-all duration-500" style="width: <?= $persentase ?>%"></div>
                                    </div>
                                    
                                    <div class="mt-2 text-right">
                                        <a href="<?= base_url('app/pages/guru/detail-kelas.php?id=' . $kelas['kelas_id']) ?>" 
                                           class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center justify-end">
                                            Detail
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <p class="text-gray-500 text-sm">Belum ada data kelas yang terhubung.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: QR Code & Misc -->
                <div class="space-y-6">
                    <!-- QR Code Card -->
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">QR Code Guru</h3>
                        <p class="text-sm text-gray-500 mb-4">Gunakan untuk absensi mandiri</p>
                        
                        <div class="flex justify-center mb-4">
                            <div class="p-3 border-2 border-dashed border-indigo-200 bg-indigo-50 rounded-lg inline-block">
                                <div id="qr-guru" class="bg-white p-2"></div>
                            </div>
                        </div>
                        <div class="text-xs font-mono bg-gray-100 px-2 py-1 rounded inline-block text-gray-600">
                            <?= $_SESSION['guru_kode_qr'] ?? '...' ?>
                        </div>

                        <script>
                            new QRCode(document.getElementById("qr-guru"), {
                                text: "<?= $_SESSION['guru_kode_qr'] ?? 'GURU-DEFAULT' ?>",
                                width: 140,
                                height: 140,
                                colorDark : "#000000",
                                colorLight : "#ffffff",
                                correctLevel : QRCode.CorrectLevel.H
                            });
                        </script>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs text-blue-700 font-medium">
                                    Tips: Pantau kehadiran siswa secara real-time. Klik "Detail" untuk melihat daftar nama siswa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php require_once '../../layouts/footer.php'; ?>
