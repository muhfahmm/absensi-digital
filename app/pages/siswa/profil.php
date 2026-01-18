<?php
// app/pages/siswa/profil.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/auth.php';
require_once '../../config/database.php';
require_once '../../layouts/header.php';

check_login('siswa');

$siswa_id = $_SESSION['siswa_id'];

// Ambil data lengkap siswa
$stmt = $pdo->prepare("
    SELECT tb_siswa.*, tb_kelas.nama_kelas 
    FROM tb_siswa 
    LEFT JOIN tb_kelas ON tb_siswa.id_kelas = tb_kelas.id 
    WHERE tb_siswa.id = ?
");
$stmt->execute([$siswa_id]);
$siswa = $stmt->fetch();
?>

<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 pb-24">
    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Profil Saya</h1>
                    <p class="text-indigo-100 text-sm mt-1">Informasi data pribadi</p>
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
        <!-- Photo Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="p-8 text-center">
                <?php if($siswa['foto_profil'] && file_exists("../../../uploads/siswa/" . $siswa['foto_profil'])): ?>
                    <img src="<?= base_url('uploads/siswa/' . $siswa['foto_profil']) ?>" 
                         alt="Foto Profil" 
                         class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-indigo-100 shadow-lg">
                <?php else: ?>
                    <div class="w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto border-4 border-indigo-200">
                        <svg class="w-16 h-16 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                <?php endif; ?>
                
                <h2 class="text-2xl font-bold text-gray-900 mt-4"><?= htmlspecialchars($siswa['nama_lengkap']) ?></h2>
                <p class="text-gray-500 mt-1"><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Belum ada kelas') ?></p>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="space-y-4">
            <!-- NIS -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-sm text-gray-500">Nomor Induk Siswa</div>
                        <div class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($siswa['nis']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Kelas -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-sm text-gray-500">Kelas</div>
                        <div class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></div>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-sm text-gray-500">Kode QR</div>
                        <div class="text-sm font-mono text-gray-700"><?= htmlspecialchars($siswa['kode_qr'] ?? '-') ?></div>
                    </div>
                </div>
            </div>

            <!-- Terdaftar Sejak -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-sm text-gray-500">Terdaftar Sejak</div>
                        <div class="text-lg font-semibold text-gray-900"><?= date('d F Y', strtotime($siswa['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Button -->
        <div class="mt-8">
            <a href="<?= base_url('app/pages/auth/logout.php?role=siswa') ?>" 
               class="block w-full bg-red-500 hover:bg-red-600 text-white text-center py-4 rounded-xl font-semibold shadow-lg transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar dari Akun
            </a>
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

                <a href="<?= base_url('app/pages/siswa/profil.php') ?>" class="flex flex-col items-center justify-center text-indigo-600">
                    <svg class="w-7 h-7 mb-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <span class="text-xs font-semibold">Profil</span>
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
                    <p class="text-xs text-gray-600 font-mono"><?= $siswa['kode_qr'] ?? '-' ?></p>
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
            text: "<?= $siswa['kode_qr'] ?? 'SISWA-DEFAULT' ?>",
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
