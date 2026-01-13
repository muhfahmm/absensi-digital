<?php
// app/pages/guru/dashboard.php
require_once '../../functions/helpers.php';
require_once '../../layouts/header.php';
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar (Mungkin perlu sidebar khusus guru) -->
    <?php include '../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Guru Dashboard</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Halo, Guru</span>
                <div class="h-8 w-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold">G</div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Scan QR Siswa</h3>
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200 rounded-lg flex items-center justify-center">
                        <p class="text-gray-500">Area Kamera Scan QR</p>
                    </div>
                    <button class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                        Buka Scanner
                    </button>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../layouts/footer.php'; ?>
