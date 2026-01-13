<?php
// app/pages/siswa/dashboard.php
require_once '../../functions/helpers.php';
require_once '../../layouts/header.php';
?>

<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Mobile Header -->
    <header class="bg-indigo-600 pb-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:max-w-7xl lg:px-8">
            <div class="relative py-5 flex items-center justify-center lg:justify-between">
                <div class="absolute left-0 flex-shrink-0 lg:static">
                    <span class="text-white font-bold text-xl">Absensi Siswa</span>
                </div>
                <!-- Menu Logout -->
                <div class="absolute right-0 flex-shrink-0 lg:static">
                     <a href="<?= base_url('app/pages/auth/logout.php') ?>" class="text-white opacity-80 hover:opacity-100">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="-mt-24 pb-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:max-w-7xl lg:px-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 text-center">
                    <h2 class="text-lg font-medium text-gray-900">QR Code Anda</h2>
                    <p class="mt-1 text-sm text-gray-500">Tunjukkan QR Code ini kepada guru piket / scanner.</p>
                    
                    <div class="mt-6 flex justify-center">
                        <div class="p-4 border-2 border-dashed border-gray-300 rounded-lg">
                            <!-- Placeholder QR Code -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ContohQRSiswa123" alt="QR Code" class="h-48 w-48 mx-auto">
                        </div>
                    </div>
                    
                    <div class="mt-6 border-t border-gray-100 pt-6">
                        <h3 class="text-md font-medium text-gray-900">Riwayat Absensi Hari Ini</h3>
                        <div class="mt-2">
                             <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Belum Absen</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../../layouts/footer.php'; ?>
