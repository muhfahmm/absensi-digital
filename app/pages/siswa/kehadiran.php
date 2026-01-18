<?php
// app/pages/siswa/kehadiran.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/auth.php';
require_once '../../config/database.php';
require_once '../../layouts/header.php';

check_login('siswa');

$siswa_id = $_SESSION['siswa_id'];
$siswa_nama = $_SESSION['siswa_nama'];
$siswa_kode_qr = $_SESSION['siswa_kode_qr'] ?? 'SISWA-DEFAULT';

// Ambil riwayat absensi siswa
$stmt = $pdo->prepare("
    SELECT * FROM tb_absensi 
    WHERE user_id = ? AND role = 'siswa' 
    ORDER BY tanggal DESC, created_at DESC 
    LIMIT 30
");
$stmt->execute([$siswa_id]);
$riwayat_absensi = $stmt->fetchAll();

// Hitung statistik
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa
    FROM tb_absensi 
    WHERE user_id = ? AND role = 'siswa'
");
$stmt_stats->execute([$siswa_id]);
$stats = $stmt_stats->fetch();
?>

<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 pb-24">
    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Riwayat Kehadiran</h1>
                    <p class="text-indigo-100 text-sm mt-1"><?= htmlspecialchars($siswa_nama) ?></p>
                </div>
                <a href="<?= base_url('app/pages/siswa/dashboard.php') ?>" class="text-white hover:text-indigo-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistik Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-lg p-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900"><?= $stats['total'] ?></div>
                    <div class="text-xs text-gray-500 mt-1">Total</div>
                </div>
            </div>
            <div class="bg-green-50 rounded-xl shadow-lg p-4 border-2 border-green-200">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600"><?= $stats['hadir'] ?></div>
                    <div class="text-xs text-green-600 mt-1">Hadir</div>
                </div>
            </div>
            <div class="bg-blue-50 rounded-xl shadow-lg p-4 border-2 border-blue-200">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600"><?= $stats['sakit'] ?></div>
                    <div class="text-xs text-blue-600 mt-1">Sakit</div>
                </div>
            </div>
            <div class="bg-yellow-50 rounded-xl shadow-lg p-4 border-2 border-yellow-200">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600"><?= $stats['izin'] ?></div>
                    <div class="text-xs text-yellow-600 mt-1">Izin</div>
                </div>
            </div>
        </div>

        <!-- Riwayat List -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">30 Hari Terakhir</h3>
            </div>
            
            <div class="divide-y divide-gray-200">
                <?php if(count($riwayat_absensi) > 0): ?>
                    <?php foreach($riwayat_absensi as $absen): ?>
                        <?php
                            $status_colors = [
                                'hadir' => 'bg-green-100 text-green-800',
                                'sakit' => 'bg-blue-100 text-blue-800',
                                'izin' => 'bg-yellow-100 text-yellow-800',
                                'alpa' => 'bg-red-100 text-red-800',
                                'terlambat' => 'bg-orange-100 text-orange-800'
                            ];
                            $color = $status_colors[$absen['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">
                                        <?= date('d F Y', strtotime($absen['tanggal'])) ?>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <?php if($absen['jam_masuk']): ?>
                                            Jam Masuk: <?= $absen['jam_masuk'] ?>
                                        <?php endif; ?>
                                        <?php if($absen['jam_keluar']): ?>
                                            • Jam Keluar: <?= $absen['jam_keluar'] ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($absen['keterangan']): ?>
                                        <div class="text-xs text-gray-400 mt-1 italic">
                                            <?= htmlspecialchars($absen['keterangan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="px-3 py-1 <?= $color ?> rounded-full text-xs font-semibold uppercase">
                                    <?= $absen['status'] ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>Belum ada riwayat absensi</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-2xl z-50">
        <div class="max-w-md mx-auto px-6">
            <div class="flex justify-around items-center h-20">
                <a href="<?= base_url('app/pages/siswa/dashboard.php') ?>" class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs">Home</span>
                </a>

                <button onclick="openQRModal()" class="flex flex-col items-center justify-center -mt-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl transition transform hover:scale-110">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 mt-1">QR Code</span>
                </button>

                <a href="<?= base_url('app/pages/siswa/profil.php') ?>" class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs">Profil</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- QR Code Modal -->
    <div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full transform transition-all scale-95 opacity-0" id="qrModalContent">
            <div class="p-8 text-center">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">QR Code Absensi</h3>
                    <button onclick="closeQRModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <p class="text-sm text-gray-500 mb-6">Tunjukkan QR Code ini untuk absensi</p>
                
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-6 rounded-2xl mb-6">
                    <div id="qr-siswa-modal" class="flex justify-center"></div>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-600 font-mono"><?= $siswa_kode_qr ?></p>
                </div>
                
                <p class="text-xs text-gray-400 mt-4">Pastikan QR Code terlihat jelas</p>
            </div>
        </div>
    </div>
</div>

<script>
let qrCodeGenerated = false;

function openQRModal() {
    const modal = document.getElementById('qrModal');
    const content = document.getElementById('qrModalContent');
    
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
    
    if (!qrCodeGenerated) {
        new QRCode(document.getElementById("qr-siswa-modal"), {
            text: "<?= $siswa_kode_qr ?>",
            width: 250,
            height: 250,
            colorDark: "#4F46E5",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        qrCodeGenerated = true;
    }
}

function closeQRModal() {
    const modal = document.getElementById('qrModal');
    const content = document.getElementById('qrModalContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});
</script>

<?php require_once '../../layouts/footer.php'; ?>
