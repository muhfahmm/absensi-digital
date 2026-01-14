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
                    <h1 class="text-2xl font-bold text-white">Scanner QR Code</h1>
                    <p class="text-blue-100 text-sm mt-1">Scan QR untuk absensi</p>
                </div>
                <a href="<?= base_url('app/pages/admin/dashboard.php') ?>" class="text-white hover:text-blue-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Scanner Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
            <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                <h2 class="text-xl font-bold">Kamera Scanner</h2>
                <p class="text-sm text-blue-100 mt-1">Arahkan kamera ke QR Code siswa/guru</p>
            </div>
            
            <div class="p-6">
                <!-- Scanner Container -->
                <div class="relative bg-gray-900 rounded-xl overflow-hidden mb-4">
                    <div id="qr-reader" style="width: 100%;"></div>
                    
                    <!-- Status Indicator -->
                    <div id="scan-status" class="absolute top-4 left-4 right-4 z-10">
                        <div class="bg-black bg-opacity-70 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                            <div class="w-2 h-2 bg-gray-500 rounded-full mr-2"></div>
                            <span>Klik tombol untuk mulai scan</span>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <div class="flex gap-3">
                    <button id="start-scan" onclick="startScanner()" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 px-6 rounded-xl font-semibold shadow-lg transition transform hover:scale-105">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Mulai Scan
                    </button>
                    <button id="stop-scan" onclick="stopScanner()" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg transition transform hover:scale-105 hidden">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                        Stop
                    </button>
                </div>
            </div>
        </div>

        <!-- Result Card -->
        <div id="result-card" class="bg-white rounded-2xl shadow-xl p-6 hidden">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Hasil Scan</h3>
            <div id="result-content"></div>
        </div>

        <!-- Recent Scans -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Scan Terakhir Hari Ini</h3>
            </div>
            <div id="recent-scans" class="divide-y divide-gray-200">
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <p>Belum ada scan hari ini</p>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
@keyframes scan {
    0%, 100% { top: 0; }
    50% { top: calc(100% - 4px); }
}
</style>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let isScanning = false;
let isProcessing = false;
let scanCallback = null;  // Store callback reference

function startScanner() {
    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');
    const statusDiv = document.getElementById('scan-status');
    
    if (isScanning) {
        console.log('Scanner already running');
        return;
    }
    
    if (html5QrCode) {
        html5QrCode.clear();
    }
    
    isProcessing = false;
    html5QrCode = new Html5Qrcode("qr-reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 350, height: 350 }
    };
    
    statusDiv.innerHTML = `
        <div class="bg-yellow-600 bg-opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center">
            <div class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></div>
            <span>Meminta akses kamera...</span>
        </div>
    `;
    
    // Define callback
    scanCallback = (decodedText, decodedResult) => {
        // IMMEDIATE BLOCK - cegah callback dipanggil lagi
        if (isProcessing) {
            return false;  // Return false untuk stop processing
        }
        
        isProcessing = true;
        console.log(`🔒 QR LOCKED: ${decodedText}`);
        
        // Update UI immediately
        statusDiv.innerHTML = `
            <div class="bg-blue-600 bg-opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
                <span>🔒 LOCKED! Memproses...</span>
            </div>
        `;
        
        // Force stop immediately - don't wait
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                console.log('Scanner stopped');
                isScanning = false;
                startBtn.classList.remove('hidden');
                stopBtn.classList.add('hidden');
            }).catch(err => {
                console.error('Stop error:', err);
            });
        }
        
        // Process QR
        processQRCode(decodedText);
        
        return false;  // Tell library to stop calling this callback
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        scanCallback,
        (errorMessage) => {
            // Ignore scan errors
        }
    ).then(() => {
        isScanning = true;
        startBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
        statusDiv.innerHTML = `
            <div class="bg-green-600 bg-opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                <div class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></div>
                <span>Scanning... Arahkan ke QR Code</span>
            </div>
        `;
    }).catch((err) => {
        console.error('Error starting scanner:', err);
        statusDiv.innerHTML = `
            <div class="bg-red-600 bg-opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
                <span>Gagal akses kamera. Izinkan akses kamera di browser.</span>
            </div>
        `;
        alert('Gagal mengakses kamera.\n\nPastikan:\n1. Browser memiliki izin kamera\n2. Kamera tidak digunakan aplikasi lain\n3. Menggunakan HTTPS atau localhost');
    });
}

function stopScanner() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().then(() => {
            isScanning = false;
            document.getElementById('start-scan').classList.remove('hidden');
            document.getElementById('stop-scan').classList.add('hidden');
            document.getElementById('scan-status').innerHTML = `
                <div class="bg-black bg-opacity-70 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                    <div class="w-2 h-2 bg-gray-500 rounded-full mr-2"></div>
                    <span>Scanner stopped</span>
                </div>
            `;
        }).catch((err) => {
            console.error('Error stopping scanner:', err);
        });
    }
}

function processQRCode(qrData) {
    fetch('<?= base_url('app/pages/admin/scanner/process.php') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ qr_code: qrData })
    })
    .then(response => response.json())
    .then(data => {
        showResult(data);
        loadRecentScans();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memproses QR Code');
    });
}

function showResult(data) {
    const resultCard = document.getElementById('result-card');
    const resultContent = document.getElementById('result-content');
    
    if (data.success) {
        resultContent.innerHTML = `
            <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-xl font-bold text-green-900">Absensi Berhasil!</h4>
                        <p class="text-sm text-green-600">${new Date().toLocaleString('id-ID')}</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nama:</span>
                        <span class="font-semibold text-gray-900">${data.nama}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Role:</span>
                        <span class="font-semibold text-gray-900 capitalize">${data.role}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-3 py-1 ${data.status === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'} rounded-full text-sm font-semibold capitalize">${data.status}</span>
                    </div>
                </div>
            </div>
        `;
    } else {
        resultContent.innerHTML = `
            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-xl font-bold text-red-900">Gagal!</h4>
                        <p class="text-sm text-red-600">${data.message || 'QR Code tidak valid'}</p>
                    </div>
                </div>
            </div>
        `;
    }
    
    resultCard.classList.remove('hidden');
    
    setTimeout(() => {
        resultCard.classList.add('hidden');
    }, 5000);
}

function loadRecentScans() {
    fetch('<?= base_url('app/pages/admin/scanner/recent.php') ?>')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recent-scans');
            if (data.length > 0) {
                container.innerHTML = data.map(scan => `
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-semibold text-gray-900">${scan.nama}</div>
                                <div class="text-sm text-gray-500">${scan.role} • ${scan.waktu}</div>
                            </div>
                            <span class="px-3 py-1 ${scan.status === 'Hadir' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'} rounded-full text-xs font-semibold">
                                ${scan.status}
                            </span>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading recent scans:', error));
}

document.addEventListener('DOMContentLoaded', () => {
    loadRecentScans();
});
</script>

<?php require_once '../../../layouts/footer.php'; ?>
