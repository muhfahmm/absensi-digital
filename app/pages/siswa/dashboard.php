<?php
// app/pages/siswa/dashboard.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/auth.php';
require_once '../../config/database.php';
require_once '../../layouts/header.php';

check_login('siswa');

$siswa_id = $_SESSION['siswa_id'];
$siswa_nama = $_SESSION['siswa_nama'];
$siswa_kode_qr = $_SESSION['siswa_kode_qr'] ?? 'SISWA-DEFAULT';

// Ambil NIS dan foto siswa
$stmt = $pdo->prepare("SELECT nis, foto_profil FROM tb_siswa WHERE id = ?");
$stmt->execute([$siswa_id]);
$siswa_data = $stmt->fetch();
$siswa_nis = $siswa_data['nis'] ?? '-';
$siswa_foto = $siswa_data['foto_profil'] ?? null;

// Ambil data kelas siswa
$kelas_nama = 'Belum ada kelas';
if (isset($_SESSION['siswa_kelas_id']) && $_SESSION['siswa_kelas_id']) {
    $stmt = $pdo->prepare("SELECT nama_kelas FROM tb_kelas WHERE id = ?");
    $stmt->execute([$_SESSION['siswa_kelas_id']]);
    $kelas = $stmt->fetch();
    if ($kelas) {
        $kelas_nama = $kelas['nama_kelas'];
    }
}

// Cek absensi hari ini
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM tb_absensi WHERE user_id = ? AND role = 'siswa' AND tanggal = ?");
$stmt->execute([$siswa_id, $today]);
$absensi_hari_ini = $stmt->fetch();
?>

<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 pb-24">
    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Dashboard Siswa</h1>
                    <p class="text-indigo-100 text-sm mt-1"><?= htmlspecialchars($siswa_nama) ?> • <?= htmlspecialchars($kelas_nama) ?></p>
                </div>
                <a href="<?= base_url('app/pages/auth/logout.php?role=siswa') ?>" class="text-white hover:text-indigo-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Student Detail Card -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl p-6 mb-6 text-white">
            <div class="flex items-center space-x-4">
                <?php if($siswa_foto && file_exists("../../../uploads/siswa/" . $siswa_foto)): ?>
                    <img src="<?= base_url('uploads/siswa/' . $siswa_foto) ?>" 
                         alt="Foto Siswa" 
                         onclick="openPhotoModal()"
                         class="w-16 h-16 rounded-full object-cover border-4 border-white border-opacity-30 shadow-lg cursor-pointer hover:scale-110 transition transform">
                <?php else: ?>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm border-4 border-white border-opacity-30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <div class="flex-1">
                    <h2 class="text-xl font-bold"><?= htmlspecialchars($siswa_nama) ?></h2>
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center text-sm text-indigo-100">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="font-medium">NIS:</span>
                            <span class="ml-2"><?= htmlspecialchars($siswa_nis) ?></span>
                        </div>
                        <div class="flex items-center text-sm text-indigo-100">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="font-medium">Kelas:</span>
                            <span class="ml-2"><?= htmlspecialchars($kelas_nama) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Absensi Card -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Status Absensi Hari Ini</h3>
            <p class="text-sm text-gray-500 mb-4"><?= date('d F Y') ?></p>
            
            <?php if ($absensi_hari_ini): ?>
                <?php
                    $status_colors = [
                        'hadir' => 'bg-green-100 text-green-800 border-green-200',
                        'sakit' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'izin' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'alpa' => 'bg-red-100 text-red-800 border-red-200',
                        'terlambat' => 'bg-orange-100 text-orange-800 border-orange-200'
                    ];
                    $status = $absensi_hari_ini['status'];
                    $color = $status_colors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                ?>
                <div class="flex items-center justify-between p-4 <?= $color ?> border-2 rounded-xl">
                    <div>
                        <span class="text-lg font-bold uppercase"><?= ucfirst($status) ?></span>
                        <?php if ($absensi_hari_ini['jam_masuk']): ?>
                            <p class="text-sm mt-1">Jam: <?= $absensi_hari_ini['jam_masuk'] ?></p>
                        <?php endif; ?>
                    </div>
                    <svg class="w-12 h-12 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-between p-4 bg-yellow-50 border-2 border-yellow-200 rounded-xl">
                    <div>
                        <span class="text-lg font-bold text-yellow-800">Belum Absen</span>
                        <p class="text-sm text-yellow-600 mt-1">Segera lakukan absensi</p>
                    </div>
                    <svg class="w-12 h-12 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Kehadiran -->
            <a href="<?= base_url('app/pages/siswa/kehadiran.php') ?>" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Kehadiran</h3>
                    <p class="text-xs text-gray-500 mt-1">Riwayat absensi</p>
                </div>
            </a>

            <!-- Profil -->
            <a href="<?= base_url('app/pages/siswa/profil.php') ?>" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Profil</h3>
                    <p class="text-xs text-gray-500 mt-1">Data pribadi</p>
                </div>
            </a>

            <!-- Pembayaran -->
            <a href="<?= base_url('app/pages/siswa/pembayaran.php') ?>" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Pembayaran</h3>
                    <p class="text-xs text-gray-500 mt-1">Status SPP</p>
                </div>
            </a>

            <!-- Pengumuman -->
            <a href="<?= base_url('app/pages/siswa/pengumuman.php') ?>" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Pengumuman</h3>
                    <p class="text-xs text-gray-500 mt-1">Info terbaru</p>
                </div>
            </a>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-2xl z-50">
        <div class="max-w-md mx-auto px-6">
            <div class="flex justify-around items-center h-20">
                <!-- Home -->
                <a href="<?= base_url('app/pages/siswa/dashboard.php') ?>" class="flex flex-col items-center justify-center text-indigo-600">
                    <svg class="w-7 h-7 mb-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="text-xs font-semibold">Home</span>
                </a>

                <!-- QR Code Button -->
                <button onclick="openQRModal()" class="flex flex-col items-center justify-center -mt-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl transition transform hover:scale-110">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 mt-1">QR Code</span>
                </button>

                <!-- Profile -->
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
                
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <p class="text-xs text-gray-600 font-mono"><?= $siswa_kode_qr ?></p>
                </div>
                
                <!-- Download Button -->
                <button onclick="downloadQR()" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-3 px-6 rounded-xl font-semibold shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download QR Code
                </button>
                
                <p class="text-xs text-gray-400 mt-4 text-center">Download QR ke HP, lalu tampilkan saat absensi</p>
            </div>
        </div>
    </div>

    <!-- Photo Preview Modal -->
    <div id="photoModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="max-w-2xl w-full transform transition-all scale-95 opacity-0" id="photoModalContent">
            <div class="relative">
                <button onclick="closePhotoModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <?php if($siswa_foto): ?>
                        <img src="<?= base_url('uploads/siswa/' . $siswa_foto) ?>" 
                             alt="Foto Profil" 
                             class="w-full h-auto max-h-[80vh] object-contain">
                    <?php endif; ?>
                    
                    <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($siswa_nama) ?></h3>
                        <p class="text-sm text-indigo-100 mt-1">NIS: <?= htmlspecialchars($siswa_nis) ?> • <?= htmlspecialchars($kelas_nama) ?></p>
                    </div>
                </div>
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

function downloadQR() {
    // Get QR code canvas
    const qrContainer = document.getElementById('qr-siswa-modal');
    const canvas = qrContainer.querySelector('canvas');
    
    if (!canvas) {
        alert('QR Code belum di-generate. Silakan buka modal QR terlebih dahulu.');
        return;
    }
    
    // Create download link
    const link = document.createElement('a');
    link.download = 'QR-Absensi-<?= $siswa_nama ?>.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
    
    // Show success message
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Downloaded!
    `;
    btn.classList.add('bg-green-600');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('bg-green-600');
    }, 2000);
}


function openPhotoModal() {
    const modal = document.getElementById('photoModal');
    const content = document.getElementById('photoModalContent');
    
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    const content = document.getElementById('photoModalContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Close modals when clicking outside
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});

document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePhotoModal();
    }
});
</script>

<?php require_once '../../layouts/footer.php'; ?>
