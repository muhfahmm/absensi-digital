<?php
// app/layouts/sidebar.php

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
                <a href="<?= base_url('app/pages/admin/dashboard.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/dashboard.php') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
            </li>

            <!-- Manajemen Admin -->
            <li>
                <a href="<?= base_url('app/pages/admin/admin/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/admin/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Data Admin
                </a>
            </li>
            
            <!-- Manajemen Kelas -->
             <li>
                <a href="<?= base_url('app/pages/admin/kelas/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/kelas/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Data Kelas
                </a>
            </li>

            <!-- Manajemen Siswa -->
             <li>
                <a href="<?= base_url('app/pages/admin/siswa/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/siswa/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Data Siswa
                </a>
            </li>

            <!-- Manajemen Guru -->
             <li>
                <a href="<?= base_url('app/pages/admin/guru/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/guru/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Data Guru
                </a>
            </li>

            <!-- Jadwal Pembelajaran -->
             <li>
                <a href="<?= base_url('app/pages/admin/jadwal/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/jadwal/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Jadwal Pembelajaran
                </a>
            </li>

            <!-- Materi Pembelajaran -->
             <li>
                <a href="<?= base_url('app/pages/admin/materi/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/materi/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Materi Pembelajaran
                </a>
            </li>

            <!-- Manajemen Nilai -->
             <li>
                <a href="<?= base_url('app/pages/admin/nilai/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/nilai/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Manajemen Nilai
                </a>
            </li>


            
            <!-- Pengumuman -->
             <li>
                <a href="<?= base_url('app/pages/admin/pengumuman/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/pengumuman/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    Pengumuman
                </a>
            </li>

            <!-- Scanner QR -->
            <li>
                <a href="<?= base_url('app/pages/admin/scanner/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/scanner/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Scanner QR
                </a>
            </li>

            <!-- Keuangan (SPP & Midtrans) -->
            <li>
                <a href="<?= base_url('app/pages/admin/keuangan/index.php') ?>" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 <?= is_active('/admin/keuangan/') ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Keuangan
                </a>
            </li>

            <!-- Database (phpMyAdmin) -->
            <li>
                <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=db_absensi_digital" target="_blank" class="flex items-center px-6 py-3 transition rounded-r-full mr-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    Database
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('app/pages/auth/logout.php?role=admin') ?>" class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50 hover:text-red-700 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </li>
        </ul>
    </nav>
</aside>
