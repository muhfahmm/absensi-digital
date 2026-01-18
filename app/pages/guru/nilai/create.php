<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../functions/helpers.php';
require_once __DIR__ . '/../../../functions/auth.php';

check_login('guru');
$guru_id = $_SESSION['guru_id'];

// Get Guru's Mapel
$stmt = $pdo->prepare("SELECT g.guru_mapel_id, m.nama_mapel FROM tb_guru g LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id WHERE g.id = ?");
$stmt->execute([$guru_id]);
$guru_data = $stmt->fetch();
$default_mapel_id = $guru_data['guru_mapel_id'];
$default_mapel_nama = $guru_data['nama_mapel'];

// Fetch Students
$students = $pdo->query("SELECT s.id, s.nama_lengkap, k.nama_kelas, k.id as id_kelas FROM tb_siswa s JOIN tb_kelas k ON s.id_kelas = k.id ORDER BY k.nama_kelas, s.nama_lengkap")->fetchAll();

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_siswa = $_POST['id_siswa'];
    $tipe_nilai = $_POST['tipe_nilai'];
    $nilai = $_POST['nilai'];
    $keterangan = $_POST['keterangan'];
    
    // Mapel is either default or from select if implemented (but we stick to default for now)
    $id_mapel = $default_mapel_id; 

    // Get id_kelas from selected student
    $stmt = $pdo->prepare("SELECT id_kelas FROM tb_siswa WHERE id = ?");
    $stmt->execute([$id_siswa]);
    $student = $stmt->fetch();
    $id_kelas = $student['id_kelas'];

    if ($id_kelas && $id_mapel) {
        $stmt = $pdo->prepare("INSERT INTO tb_nilai (id_siswa, id_guru, id_mapel, id_kelas, tipe_nilai, nilai, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$id_siswa, $guru_id, $id_mapel, $id_kelas, $tipe_nilai, $nilai, $keterangan])) {
            echo "<script>alert('Nilai berhasil disimpan'); window.location.href='index.php';</script>";
        } else {
            $error = "Gagal menyimpan data.";
        }
    } else {
        $error = "Data tidak valid. Pastikan Anda memiliki mata pelajaran yang diampu.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <?php include __DIR__ . '/../../../layouts/sidebar_guru.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
                <h1 class="text-xl font-bold text-gray-800">Input Nilai</h1>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-500 hover:text-indigo-600">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </header>

            <main class="w-full flex-grow p-6 overflow-y-auto">
                <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <?php if(isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?= $error ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- Identity Card -->
                        <div class="mb-6 bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Identitas Penginput</span>
                                <div class="flex items-center space-x-1 text-indigo-500">
                                    <i class="fas fa-check-circle"></i>
                                    <span class="text-[10px] font-bold">Terverifikasi</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-500 text-[10px] uppercase font-bold mb-1">Guru Pemberi Nilai</label>
                                    <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($_SESSION['guru_nama']) ?></p>
                                </div>
                                <div>
                                    <label class="block text-gray-500 text-[10px] uppercase font-bold mb-1">Mata Pelajaran</label>
                                    <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($default_mapel_nama ?? 'Umum') ?></p>
                                </div>
                            </div>
                            <?php if(!$default_mapel_id): ?>
                                <p class="text-red-500 text-[10px] mt-2 italic font-medium">* Hubungi admin untuk mengatur mata pelajaran Anda.</p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Siswa</label>
                            <select name="id_siswa" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach($students as $s): ?>
                                    <option value="<?= $s['id'] ?>">[<?= $s['nama_kelas'] ?>] <?= $s['nama_lengkap'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tipe Nilai</label>
                                <select name="tipe_nilai" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="TUGAS">TUGAS</option>
                                    <option value="UH">UH (Ulangan Harian)</option>
                                    <option value="UTS">UTS</option>
                                    <option value="UAS">UAS</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nilai (0-100)</label>
                                <input type="number" name="nilai" min="0" max="100" step="0.01" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: 85.50">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan (Opsional)</label>
                            <textarea name="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow transition" <?= !$default_mapel_id ? 'disabled' : '' ?>>
                                Simpan Nilai
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
