<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../functions/helpers.php';
require_once __DIR__ . '/../../../functions/auth.php';

check_login('guru');
?>

<!DOCTYPE html>
<html lang="id">
<!-- Using Global Standard Pattern -->
<?php require_once __DIR__ . '/../../../layouts/header.php'; ?>

<div class="flex h-screen bg-gray-50">
    <?php include __DIR__ . '/../../../layouts/sidebar_guru.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
            <h2 class="text-xl font-semibold text-gray-800">Scanner Absensi Siswa</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($_SESSION['guru_nama']) ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">Guru</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                    <?= substr($_SESSION['guru_nama'], 0, 1) ?>
                </div>
            </div>
        </header>

        <main class="w-full flex-grow p-6 overflow-y-auto">
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Scanner Area -->
                        <div class="flex flex-col items-center">
                            <h2 class="text-lg font-bold text-gray-700 mb-4">Kamera Scanner</h2>
                            <div id="reader" class="w-full rounded-lg overflow-hidden border-2 border-dashed border-gray-300 bg-gray-50 bg-opacity-50" style="min-height: 300px;"></div>
                            <p class="text-sm text-gray-500 mt-2 text-center">Arahkan QR Code Kartu Siswa ke kamera.</p>
                            
                            <div class="mt-4 flex gap-2">
                                <button onclick="startScanner()" id="btn-start" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 transition transform hover:scale-105">
                                    Mulai Kamera
                                </button>
                                <button onclick="stopScanner()" id="btn-stop" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-red-500/30 transition transform hover:scale-105 hidden">
                                    Stop Kamera
                                </button>
                            </div>
                        </div>

                        <!-- Result Area -->
                        <div class="flex flex-col">
                            <h2 class="text-lg font-bold text-gray-700 mb-4">Hasil Scan</h2>
                            <div id="result-container" class="flex-1 bg-gray-50 rounded-xl border border-gray-200 p-6 flex flex-col items-center justify-center text-center">
                                <div id="scan-placeholder" class="text-gray-400">
                                    <i class="fas fa-qrcode text-6xl mb-4 opacity-30"></i>
                                    <p>Belum ada data scan...</p>
                                </div>
                                
                                <div id="scan-result" class="hidden w-full">
                                    <!-- Dynamic Content -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    let html5QrcodeScanner;
    let isScanning = false;

    function onScanSuccess(decodedText, decodedResult) {
        // Prevent multiple scans of same code in short time
        if(isScanning) return;
        isScanning = true;
        
        // Play Beep Sound
        const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-software-interface-start-2574.mp3');
        audio.play().catch(e => console.log('Audio play failed'));

        // Show Processing
        document.getElementById('scan-placeholder').classList.add('hidden');
        document.getElementById('scan-result').innerHTML = `
            <div class="animate-pulse">
                <div class="h-12 w-12 bg-indigo-200 rounded-full mx-auto mb-4"></div>
                <div class="h-4 bg-indigo-200 rounded w-3/4 mx-auto mb-2"></div>
                <p class="text-indigo-600 font-semibold">Memproses Data...</p>
            </div>
        `;
        document.getElementById('scan-result').classList.remove('hidden');

        // Send to Backend
        fetch('process_scan.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ qr_code: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            showResult(data);
            setTimeout(() => { isScanning = false; }, 2000); // Cooldown 2s
        })
        .catch(error => {
            console.error('Error:', error);
            showResult({ success: false, message: 'Gagal menghubungi server' });
            isScanning = false;
        });
    }

    function showResult(data) {
        const container = document.getElementById('scan-result');
        let icon, colorClass, title, message;

        if (data.success) {
            icon = 'fa-check-circle';
            colorClass = 'green';
            title = data.nama_siswa || 'Berhasil';
            message = data.message;
        } else {
            icon = 'fa-times-circle';
            colorClass = 'red';
            title = 'Gagal';
            message = data.message;
        }

        container.innerHTML = `
            <div class="py-4">
                <i class="fas ${icon} text-5xl text-${colorClass}-500 mb-3 block"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-1">${title}</h3>
                <p class="text-${colorClass}-600 font-medium mb-2">${message}</p>
                ${data.waktu ? `<p class="text-gray-500 text-sm bg-gray-100 rounded-full px-3 py-1 inline-block mt-2"><i class="far fa-clock mr-1"></i> ${data.waktu}</p>` : ''}
            </div>
        `;
    }

    function startScanner() {
        document.getElementById('btn-start').classList.add('hidden');
        document.getElementById('btn-stop').classList.remove('hidden');
        
        html5QrcodeScanner = new Html5Qrcode("reader");
        html5QrcodeScanner.start(
            { facingMode: "environment" }, 
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            onScanSuccess,
            (errorMessage) => {
                // parse error, ignore loop
            }
        ).catch(err => {
            console.log(err);
            alert("Gagal mengakses kamera. Pastikan izin diberikan.");
            stopScanner();
        });
    }

    function stopScanner() {
        if(html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                document.getElementById('btn-stop').classList.add('hidden');
                document.getElementById('btn-start').classList.remove('hidden');
            }).catch(err => {
                console.log("Failed to stop scanner");
            });
        }
    }
</script>
</body>
</html>
