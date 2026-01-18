<?php
// app/layouts/sidebar_guru.php

// Helper untuk mengecek active menu
$current_uri = $_SERVER['REQUEST_URI'];

function is_active($keyword) {
    global $current_uri;
    if (strpos($current_uri, $keyword) !== false) {
        return 'bg-indigo-600 text-white shadow-md'; 
    }
    return 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-600';
}
?>
<aside class="w-64 bg-white border-r border-gray-200 min-h-screen hidden md:block">
    <div class="p-6">
        <h2 class="text-3xl font-bold text-indigo-600">Absensi<span class="text-gray-800">App</span></h2>
    </div>
    
    <nav class="mt-4">
        <ul class="space-y-2">
            <!-- Menu Dashboard -->
            <li>
                <a href="<?= base_url('app/pages/guru/dashboard.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/guru/dashboard.php') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
            </li>

            <!-- Materi Pembelajaran -->
             <li>
                <a href="<?= base_url('app/pages/guru/materi/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/guru/materi/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Materi Pembelajaran
                </a>
            </li>

             <!-- Manajemen Nilai -->
             <li>
                <a href="<?= base_url('app/pages/guru/nilai/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/guru/nilai/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Manajemen Nilai
                </a>
            </li>

            <!-- Jadwal -->
             <li>
                <a href="<?= base_url('app/pages/guru/jadwal/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/guru/jadwal/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Jadwal Mengajar
                </a>
            </li>



            <li>
                <a href="<?= base_url('app/pages/auth/logout.php?role=guru') ?>" class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50 hover:text-red-700 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </li>
        </ul>
    </nav>
</aside>
