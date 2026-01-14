<?php
// app/pages/admin/siswa/print-qr.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

// Get siswa ID from URL
$siswa_id = $_GET['id'] ?? null;

if (!$siswa_id) {
    redirect('app/pages/admin/siswa/index.php');
    exit;
}

// Fetch siswa data
$stmt = $pdo->prepare("
    SELECT tb_siswa.*, tb_kelas.nama_kelas 
    FROM tb_siswa 
    LEFT JOIN tb_kelas ON tb_siswa.id_kelas = tb_kelas.id 
    WHERE tb_siswa.id = ?
");
$stmt->execute([$siswa_id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    redirect('app/pages/admin/siswa/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download QR Code - <?= htmlspecialchars($siswa['nama_lengkap']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">
    <div class="max-w-2xl mx-auto p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <a href="<?= base_url('app/pages/admin/siswa/index.php') ?>" 
               class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white text-center">
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-2">QR Code Absensi</h1>
                <p class="text-indigo-100"><?= htmlspecialchars($siswa['nama_lengkap']) ?></p>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Student Info -->
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Informasi Siswa
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">NIS:</span>
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($siswa['nis']) ?></p>
                        </div>
                        <div>
                            <span class="text-gray-600">Kelas:</span>
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-600">Kode QR:</span>
                            <p class="font-mono text-xs bg-white px-3 py-2 rounded mt-1"><?= htmlspecialchars($siswa['kode_qr']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- QR Code Display -->
                <div class="bg-white border-4 border-indigo-200 rounded-2xl p-8 mb-6">
                    <div class="flex justify-center mb-4">
                        <div id="qrcode" class="bg-white p-4 rounded-xl shadow-lg"></div>
                    </div>
                    <p class="text-center text-sm text-gray-600">Scan QR code ini untuk absensi</p>
                </div>

                <!-- Download Button -->
                <button onclick="downloadQR()" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-4 px-6 rounded-xl font-bold text-lg shadow-xl transition transform hover:scale-105 flex items-center justify-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span id="btn-text">Download QR Code</span>
                </button>

                <!-- Instructions -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h4 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cara Menggunakan:
                    </h4>
                    <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
                        <li>Klik tombol "Download QR Code" di atas</li>
                        <li>Simpan file QR code ke HP siswa</li>
                        <li>Saat absensi, buka gambar QR di HP</li>
                        <li>Atur brightness HP ke maksimal</li>
                        <li>Tampilkan QR ke kamera scanner (jarak 15-20cm)</li>
                        <li>Tunggu konfirmasi: Beep + "LOCKED!"</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Generate QR Code
    const qrCode = new QRCode(document.getElementById("qrcode"), {
        text: "<?= $siswa['kode_qr'] ?>",
        width: 300,
        height: 300,
        colorDark: "#4F46E5",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    function downloadQR() {
        const canvas = document.querySelector('#qrcode canvas');
        
        if (!canvas) {
            alert('QR Code belum siap. Silakan refresh halaman.');
            return;
        }
        
        // Create download link
        const link = document.createElement('a');
        link.download = 'QR-<?= $siswa['nis'] ?>-<?= str_replace(' ', '-', $siswa['nama_lengkap']) ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        
        // Show success feedback
        const btnText = document.getElementById('btn-text');
        const originalText = btnText.textContent;
        btnText.textContent = '✓ Downloaded!';
        
        setTimeout(() => {
            btnText.textContent = originalText;
        }, 2000);
    }
    </script>
</body>
</html>

