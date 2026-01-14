<?php
// app/pages/guru/dashboard.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/auth.php';
require_once '../../config/database.php';
require_once '../../layouts/header.php';

check_login('guru');

$guru_id = $_SESSION['user_id'];
$guru_nama = $_SESSION['nama'];

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
?>

<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Mobile Header -->
    <header class="bg-green-600 pb-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:max-w-7xl lg:px-8">
            <div class="relative py-5 flex items-center justify-between">
                <div class="flex-shrink-0">
                    <span class="text-white font-bold text-xl">Dashboard Guru</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white text-sm"><?= htmlspecialchars($guru_nama) ?></span>
                    <a href="<?= base_url('app/pages/auth/logout.php') ?>" class="text-white opacity-80 hover:opacity-100 text-sm">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="-mt-24 pb-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:max-w-7xl lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900">Selamat Datang, <?= htmlspecialchars($guru_nama) ?>!</h2>
                    <p class="mt-1 text-sm text-gray-500">Pantau absensi siswa di semua kelas</p>
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="p-6 text-center">
                    <h2 class="text-lg font-medium text-gray-900">QR Code Anda</h2>
                    <p class="mt-1 text-sm text-gray-500">Tunjukkan QR Code ini untuk absensi guru</p>
                    
                    <div class="mt-6 flex justify-center">
                        <div class="p-4 border-2 border-dashed border-gray-300 rounded-lg">
                            <div id="qr-guru" class="flex justify-center"></div>
                            <script>
                                new QRCode(document.getElementById("qr-guru"), {
                                    text: "<?= $_SESSION['kode_qr'] ?? 'GURU-DEFAULT' ?>",
                                    width: 200,
                                    height: 200,
                                    colorDark : "#000000",
                                    colorLight : "#ffffff",
                                    correctLevel : QRCode.CorrectLevel.H
                                });
                            </script>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-xs text-gray-500">Kode QR: <?= $_SESSION['kode_qr'] ?? 'Tidak tersedia' ?></p>
                    </div>
                </div>
            </div>

            <!-- Statistik Hari Ini -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Absensi Hari Ini - <?= date('d F Y') ?></h3>
                    
                    <?php if(count($kelas_absensi) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach($kelas_absensi as $kelas): ?>
                                <?php 
                                    $persentase = $kelas['total_siswa'] > 0 
                                        ? round(($kelas['total_hadir'] / $kelas['total_siswa']) * 100) 
                                        : 0;
                                    
                                    $color_class = $persentase >= 80 ? 'bg-green-500' : ($persentase >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                                ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($kelas['nama_kelas']) ?></h4>
                                        <span class="text-sm font-medium text-gray-600">
                                            <?= $kelas['total_hadir'] ?> / <?= $kelas['total_siswa'] ?> Siswa
                                        </span>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="<?= $color_class ?> h-2.5 rounded-full transition-all duration-500" style="width: <?= $persentase ?>%"></div>
                                    </div>
                                    
                                    <div class="mt-2 flex justify-between items-center">
                                        <span class="text-xs text-gray-500">Kehadiran: <?= $persentase ?>%</span>
                                        <a href="<?= base_url('app/pages/guru/detail-kelas.php?id=' . $kelas['kelas_id']) ?>" 
                                           class="text-xs text-green-600 hover:text-green-800 font-medium">
                                            Lihat Detail →
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">Belum ada data kelas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Klik "Lihat Detail" untuk melihat daftar siswa yang hadir/tidak hadir di setiap kelas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../../layouts/footer.php'; ?>
