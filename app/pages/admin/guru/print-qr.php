<?php
// app/pages/admin/guru/print-qr.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

$guru_id = $_GET['id'] ?? null;

if (!$guru_id) {
    redirect('app/pages/admin/guru/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tb_guru WHERE id = ?");
$stmt->execute([$guru_id]);
$guru = $stmt->fetch();

if (!$guru) {
    redirect('app/pages/admin/guru/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Card - <?= htmlspecialchars($guru['nama_lengkap']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none; }
            .print-card { 
                page-break-after: always;
                width: 8.5cm;
                height: 5.4cm;
            }
        }
        
        .id-card {
            width: 8.5cm;
            height: 5.4cm;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="no-print max-w-4xl mx-auto mb-6 flex justify-between items-center">
        <a href="<?= base_url('app/pages/admin/guru/index.php') ?>" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            ← Kembali
        </a>
        <button onclick="window.print()" 
                class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold">
            🖨️ Print Kartu
        </button>
    </div>

    <div class="no-print max-w-4xl mx-auto mb-6 bg-pink-50 border border-pink-200 rounded-lg p-4">
        <h3 class="font-semibold text-pink-900 mb-2">📋 Informasi:</h3>
        <ul class="text-sm text-pink-800 space-y-1">
            <li>• Kartu Guru ukuran standar ID card (8.5cm x 5.4cm)</li>
            <li>• Gunakan kertas glossy untuk hasil terbaik</li>
            <li>• QR Code: <code class="bg-pink-100 px-2 py-1 rounded"><?= htmlspecialchars($guru['kode_qr']) ?></code></li>
        </ul>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="print-card id-card">
            <div class="text-center mb-3">
                <h2 class="text-xl font-bold">KARTU GURU</h2>
                <p class="text-xs opacity-90">Sistem Absensi Digital</p>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <?php if($guru['foto_profil'] && file_exists("../../../../uploads/guru/" . $guru['foto_profil'])): ?>
                        <img src="<?= base_url('uploads/guru/' . $guru['foto_profil']) ?>" 
                             alt="Foto" 
                             class="w-20 h-20 rounded-lg object-cover border-2 border-white">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-white bg-opacity-20 rounded-lg flex items-center justify-center border-2 border-white">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex-1">
                    <h3 class="font-bold text-lg mb-1"><?= htmlspecialchars($guru['nama_lengkap']) ?></h3>
                    <div class="text-sm space-y-0.5 opacity-90">
                        <p>NUPTK: <?= htmlspecialchars($guru['nuptk']) ?></p>
                        <p>Guru</p>
                    </div>
                </div>

                <div class="flex-shrink-0">
                    <div id="qrcode" class="bg-white p-1 rounded"></div>
                </div>
            </div>

            <div class="text-center mt-3 text-xs opacity-75">
                <p>Tunjukkan QR code ini untuk absensi</p>
            </div>
        </div>
    </div>

    <div class="no-print max-w-4xl mx-auto mt-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Opsi Cetak:</h3>
            <div class="grid grid-cols-3 gap-4">
                <button onclick="printMultiple(1)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    1 Kartu
                </button>
                <button onclick="printMultiple(3)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    3 Kartu
                </button>
                <button onclick="printMultiple(5)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    5 Kartu
                </button>
            </div>
        </div>
    </div>

    <script>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= $guru['kode_qr'] ?>",
        width: 80,
        height: 80,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    function printMultiple(count) {
        const originalCard = document.querySelector('.print-card');
        const container = originalCard.parentElement;
        
        document.querySelectorAll('.cloned-card').forEach(el => el.remove());
        
        for (let i = 1; i < count; i++) {
            const clone = originalCard.cloneNode(true);
            clone.classList.add('cloned-card');
            container.appendChild(clone);
            
            const qrDiv = clone.querySelector('#qrcode');
            qrDiv.id = 'qrcode-' + i;
            qrDiv.innerHTML = '';
            
            new QRCode(qrDiv, {
                text: "<?= $guru['kode_qr'] ?>",
                width: 80,
                height: 80,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
        
        setTimeout(() => {
            window.print();
        }, 500);
    }
    </script>
</body>
</html>
