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
let detectionCount = 0;

function playBeep() {
    // Create beep sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
}

function showLockOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'lock-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 255, 0.3);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: flash 0.5s ease-in-out;
    `;
    overlay.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div style="font-size: 60px; margin-bottom: 10px;">🔒</div>
            <div style="font-size: 24px; font-weight: bold; color: #2563eb;">LOCKED!</div>
            <div style="font-size: 14px; color: #6b7280; margin-top: 10px;">Scanner terkunci, memproses QR...</div>
        </div>
    `;
    document.body.appendChild(overlay);
    
    setTimeout(() => {
        overlay.remove();
    }, 2000);
}

function startScanner() {
    const startBtn = document.getElementById('start-scan');
    const statusDiv = document.getElementById('scan-status');

    statusDiv.innerHTML = `
        <div class="bg-blue-600 bg-opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center animate-pulse">
            <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
            <span>Membuka Scanner Python...</span>
        </div>
    `;

    fetch('<?= base_url('app/pages/admin/scanner/launch_python.php') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.innerHTML = `
                    <div class="bg-green-600 bg-opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                        <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
                        <span>✅ Scanner Desktop Berjalan! Silakan cek taskbar.</span>
                    </div>
                `;
                console.log('Scanner launched:', data);
                
                // Auto reload recent scans every 5 seconds to show updates from python
                setInterval(loadRecentScans, 5000);
            } else {
                alert('Gagal membuka scanner: ' + data.message);
                statusDiv.innerHTML = `
                    <div class="bg-red-600 bg-opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                        <span>❌ Gagal: ${data.message}</span>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error connecting to server');
        });
}

function stopScanner() {
    alert('Silakan tutup jendela Python Scanner manual (Tekan Q pada window kamera).');
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
                    <div class="p-5 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg font-bold text-gray-900">${scan.nama}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${scan.type === 'Masuk' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'}">
                                        ${scan.type}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500 font-medium">
                                    ${scan.role} • ${scan.waktu}
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="px-4 py-1.5 rounded-full text-xs font-bold ${
                                    scan.status === 'Hadir' 
                                    ? 'bg-green-100 text-green-700' 
                                    : (scan.status === 'Terlambat' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700')
                                }">
                                    ${scan.type === 'Pulang' ? 'Selesai' : scan.status}
                                </span>
                            </div>
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
