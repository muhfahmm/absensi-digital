<?php
session_start();
require_once __DIR__ . '/../../../functions/helpers.php';
require_once __DIR__ . '/../../../functions/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Cek Logged In Admin
check_login('admin');

// Fetch Options
$gurus = $pdo->query("SELECT id, nama_lengkap FROM tb_guru ORDER BY nama_lengkap")->fetchAll();
$mapels = $pdo->query("SELECT id, nama_mapel FROM tb_mata_pelajaran ORDER BY nama_mapel")->fetchAll();
$students = $pdo->query("SELECT s.id, s.nama_lengkap, k.nama_kelas, k.id as id_kelas FROM tb_siswa s JOIN tb_kelas k ON s.id_kelas = k.id ORDER BY k.nama_kelas, s.nama_lengkap")->fetchAll();

// --- Auto Detect Guru & Mapel Logic ---
$admin_id = $_SESSION['admin_id'];
$stmtMe = $pdo->prepare("
    SELECT g.id as guru_id, g.nama_lengkap, m.id as mapel_id, m.nama_mapel 
    FROM tb_admin a 
    JOIN tb_guru g ON a.nuptk = g.nuptk 
    LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id 
    WHERE a.id = ?
");
$stmtMe->execute([$admin_id]);
$myData = $stmtMe->fetch();

// Master Admin Fallback logic (id 13)
$is_master = ($admin_id == 13);
$my_guru_id = $myData['guru_id'] ?? null;
$my_mapel_id = $myData['mapel_id'] ?? null;

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_guru = $_POST['id_guru'] ?? $my_guru_id;
    $id_mapel = $_POST['id_mapel'] ?? $my_mapel_id;
    $id_siswa = $_POST['id_siswa'];
    $tipe_nilai = $_POST['tipe_nilai'];
    $nilai = $_POST['nilai'];
    $keterangan = $_POST['keterangan'];

    if (!$id_guru || !$id_mapel) {
        $error = "Identitas Guru atau Mata Pelajaran tidak terdeteksi.";
    } else {
        // Get id_kelas from selected student
        $stmt = $pdo->prepare("SELECT id_kelas FROM tb_siswa WHERE id = ?");
        $stmt->execute([$id_siswa]);
        $student = $stmt->fetch();
        $id_kelas = $student['id_kelas'];

        if ($id_kelas) {
            $stmt = $pdo->prepare("INSERT INTO tb_nilai (id_siswa, id_guru, id_mapel, id_kelas, tipe_nilai, nilai, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$id_siswa, $id_guru, $id_mapel, $id_kelas, $tipe_nilai, $nilai, $keterangan])) {
                echo "<script>alert('Nilai berhasil disimpan'); window.location.href='index.php';</script>";
            } else {
                $error = "Gagal menyimpan data.";
            }
        } else {
            $error = "Data siswa tidak valid.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Nilai - Admin Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../../layouts/sidebar.php'; ?>

        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">Tambah Nilai</h1>
                    <div class="flex items-center space-x-4">
                        <a href="index.php" class="text-gray-500 hover:text-indigo-600">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </header>

            <main class="w-full flex-grow p-6">
                <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <?php if(isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?= $error ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?php if ($my_guru_id && $my_mapel_id && !$is_master): ?>
                            <div class="mb-4 bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Informasi Penginput</span>
                                    <i class="fas fa-check-circle text-indigo-500"></i>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-500 text-xs mb-1">Guru Pemberi Nilai</label>
                                        <p class="font-bold text-gray-800"><?= $myData['nama_lengkap'] ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-gray-500 text-xs mb-1">Mata Pelajaran</label>
                                        <p class="font-bold text-gray-800"><?= $myData['nama_mapel'] ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Guru Pemberi Nilai</label>
                                <select name="id_guru" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Guru --</option>
                                    <?php foreach($gurus as $g): ?>
                                        <option value="<?= $g['id'] ?>" <?= ($my_guru_id == $g['id']) ? 'selected' : '' ?>><?= $g['nama_lengkap'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Mata Pelajaran</label>
                                <select name="id_mapel" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Mapel --</option>
                                    <?php foreach($mapels as $m): ?>
                                        <option value="<?= $m['id'] ?>" <?= ($my_mapel_id == $m['id']) ? 'selected' : '' ?>><?= $m['nama_mapel'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

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
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
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
