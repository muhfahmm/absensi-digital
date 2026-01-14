<?php
// app/pages/siswa/pembayaran.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/auth.php';
require_once '../../config/database.php';
require_once '../../layouts/header.php';

check_login('siswa');

$siswa_id = $_SESSION['user_id'];
$siswa_nama = $_SESSION['nama'];
$siswa_kode_qr = $_SESSION['kode_qr'] ?? 'SISWA-DEFAULT';
?>

<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 pb-24">
    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Pembayaran SPP</h1>
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
        <!-- Coming Soon Card -->
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Fitur Pembayaran</h2>
            <p class="text-gray-600 mb-6">Fitur pembayaran SPP sedang dalam pengembangan</p>
            
            <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold">
                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Coming Soon
            </div>
        </div>

        <!-- Info -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Untuk informasi pembayaran SPP, silakan hubungi bagian administrasi atau tata usaha sekolah.
                    </p>
                </div>
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
