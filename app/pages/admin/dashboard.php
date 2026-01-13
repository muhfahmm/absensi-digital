<?php
// app/pages/admin/dashboard.php
require_once '../../functions/helpers.php';
require_once '../../layouts/header.php';

// Cek session admin di sini nanti (middleware)
?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../layouts/sidebar.php'; ?>
    
    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header usually here -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Admin Dashboard</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Selamat datang, Admin</span>
                <div class="h-8 w-8 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold">A</div>
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
