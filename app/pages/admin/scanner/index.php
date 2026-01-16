<?php
// app/pages/admin/scanner/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

$admin_nama = $_SESSION['nama'];
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Monitoring Absensi Real-time</h1>
                    <p class="text-blue-100 text-sm mt-1">Data terkini dari Scanner Desktop Python</p>
                    <div class="mt-3 flex items-center">
                        <span class="px-3 py-1 rounded-full bg-blue-500 bg-opacity-30 border border-blue-400 text-xs font-semibold text-white flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <?= date('d F Y') ?>
                        </span>
                    </div>
                </div>
                <a href="<?= base_url('app/pages/admin/dashboard.php') ?>" class="text-white hover:text-blue-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Status / Instruction Card -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-8 border border-blue-100">
            <div class="p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm animate-pulse">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Scanner Desktop Aktif</h2>
                        <p class="text-gray-500 mt-1">
                            Sistem mendengarkan data absensi dari aplikasi Desktop Python secara otomatis.
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button onclick="launchPythonScanner()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold flex items-center shadow-md transition transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Buka Scanner
                    </button>
                    
                    <div class="px-4 py-2 bg-green-50 text-green-700 rounded-lg text-sm font-semibold flex items-center border border-green-100">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-ping"></span>
                        Live Connection
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Scans Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Card Masuk -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden h-[600px] flex flex-col border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Absen Masuk</h3>
                            <p class="text-xs text-green-600 font-medium tracking-wide uppercase">Kedatangan Terbaru</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1 rounded-full shadow-sm border border-gray-100">TODAY</span>
                </div>
                <div id="recent-masuk" class="divide-y divide-gray-50 overflow-y-auto flex-1 p-2 custom-scrollbar">
                    <div class="h-full flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm">Menunggu data masuk...</p>
                    </div>
                </div>
            </div>

            <!-- Card Pulang -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden h-[600px] flex flex-col border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Absen Pulang</h3>
                            <p class="text-xs text-orange-600 font-medium tracking-wide uppercase">Kepulangan Terbaru</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1 rounded-full shadow-sm border border-gray-100">TODAY</span>
                </div>
                <div id="recent-pulang" class="divide-y divide-gray-50 overflow-y-auto flex-1 p-2 custom-scrollbar">
                    <div class="h-full flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm">Menunggu data pulang...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Custom Scrollbar for cleaner look */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af; 
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out forwards;
}
</style>

<script>
function launchPythonScanner() {
    // Visual feedback button clicked
    const btn = document.querySelector('button[onclick="launchPythonScanner()"]');
    const originalContent = btn.innerHTML;
    btn.innerHTML = 'Membuka...';
    btn.disabled = true;

    fetch('<?= base_url('app/pages/admin/scanner/launch_python.php') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toast or simple alert
                // alert('Scanner Python berhasil dibuka! Cek taskbar jika tidak muncul.');
                // Revert button shortly
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Gagal menghubungi server.');
        })
        .finally(() => {
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }, 3000);
        });
}

function generateItemHTML(scan) {
    const isMasuk = scan.type === 'Masuk';
    const colorClass = isMasuk ? 'green' : 'orange';
    
    // Format Waktu: "Masuk: 17:44 • Keluar: 17:45"
    let timeInfo = `<span class="font-semibold text-green-700">Masuk: ${scan.jam_masuk}</span>`;
    if (scan.jam_keluar && scan.jam_keluar !== '-') {
        timeInfo += ` <span class="text-gray-400 mx-1">•</span> <span class="font-semibold text-orange-700">Keluar: ${scan.jam_keluar}</span>`;
    } else if (!isMasuk) {
         // Fallback logic if needed
    }

    return `
    <div class="p-4 mx-2 my-1 bg-white hover:bg-gray-50 rounded-xl border border-transparent hover:border-gray-100 transition-all duration-200 group animate-fade-in-down">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-xl bg-${colorClass}-50 flex items-center justify-center text-${colorClass}-600 border border-${colorClass}-100 shadow-sm group-hover:shadow-md transition-shadow">
                    <span class="font-bold text-sm">${scan.waktu}</span>
                </div>
            </div>
            
            <div class="flex-1 min-w-0">
                <p class="text-base font-bold text-gray-900 truncate">
                    ${scan.nama}
                </p>
                <div class="flex flex-col gap-1 mt-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded text-[10px] uppercase tracking-wide border border-gray-200">${scan.role}</span>
                        ${isMasuk && scan.status === 'Terlambat' ? '<span class="text-[10px] bg-red-50 text-red-600 px-2 py-0.5 rounded font-bold border border-red-100">TERLAMBAT</span>' : ''}
                    </div>
                    <div class="text-xs text-gray-600 mt-0.5">
                        ${timeInfo}
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;
}

function loadRecentScans() {
    fetch('<?= base_url('app/pages/admin/scanner/recent.php') ?>')
        .then(response => response.json())
        .then(data => {
            const containerMasuk = document.getElementById('recent-masuk');
            const containerPulang = document.getElementById('recent-pulang');
            
            // Handle Masuk
            if (data.masuk && data.masuk.length > 0) {
                containerMasuk.innerHTML = data.masuk.map(scan => generateItemHTML(scan)).join('');
            } else {
                containerMasuk.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 min-h-[200px]">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-2">
                             <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm">Belum ada absen masuk</p>
                    </div>
                `;
            }

            // Handle Pulang
            if (data.pulang && data.pulang.length > 0) {
                containerPulang.innerHTML = data.pulang.map(scan => generateItemHTML(scan)).join('');
            } else {
                containerPulang.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 min-h-[200px]">
                         <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-2">
                             <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm">Belum ada absen pulang</p>
                    </div>
                `;
            }
        })
        .catch(error => console.error('Error loading recent scans:', error));
}

// Auto update every 3 seconds for fast feedback
setInterval(loadRecentScans, 3000);
document.addEventListener('DOMContentLoaded', loadRecentScans);
</script>

<!-- Restore Section Header -->
<script>
    // Inject header if missing (optional enhancement)
    // Actually best to do it in HTML structure above, but we are editing script range here.
    // We will stick to updating the function.
</script>

<?php require_once '../../../layouts/footer.php'; ?>
