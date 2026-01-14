<?php
// app/pages/siswa/pengumuman.php
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
                    <h1 class="text-2xl font-bold text-white">Pengumuman</h1>
                    <p class="text-indigo-100 text-sm mt-1">Informasi terbaru untuk siswa</p>
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
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center mb-6">
            <div class="w-24 h-24 bg-gradient-to-br from-orange-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Fitur Pengumuman</h2>
            <p class="text-gray-600 mb-6">Fitur pengumuman sedang dalam pengembangan</p>
            
            <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-semibold">
                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Coming Soon
            </div>
        </div>

        <!-- Sample Announcements (Preview) -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview Pengumuman</h3>
            
            <!-- Announcement 1 -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="text-lg font-semibold text-gray-900">Libur Nasional</h4>
                        <p class="text-sm text-gray-500 mt-1">Diposting 2 hari yang lalu</p>
                        <p class="text-gray-700 mt-3">Sekolah akan libur pada tanggal 17 Agustus 2026 dalam rangka memperingati Hari Kemerdekaan Indonesia.</p>
                        <span class="inline-block mt-3 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Penting</span>
                    </div>
                </div>
            </div>

            <!-- Announcement 2 -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="text-lg font-semibold text-gray-900">Ujian Tengah Semester</h4>
                        <p class="text-sm text-gray-500 mt-1">Diposting 1 minggu yang lalu</p>
                        <p class="text-gray-700 mt-3">Ujian Tengah Semester akan dilaksanakan mulai tanggal 1-5 September 2026. Harap mempersiapkan diri dengan baik.</p>
                        <span class="inline-block mt-3 px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Akademik</span>
                    </div>
                </div>
            </div>

            <!-- Announcement 3 -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="text-lg font-semibold text-gray-900">Perubahan Jadwal</h4>
                        <p class="text-sm text-gray-500 mt-1">Diposting 3 hari yang lalu</p>
                        <p class="text-gray-700 mt-3">Jadwal pelajaran Matematika kelas XII dipindahkan dari hari Senin ke hari Rabu mulai minggu depan.</p>
                        <span class="inline-block mt-3 px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Perubahan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="mt-6 bg-orange-50 border border-orange-200 rounded-xl p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-orange-700">
                        Pengumuman di atas hanya contoh. Fitur pengumuman akan segera tersedia untuk menampilkan informasi resmi dari sekolah.
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
