<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../functions/helpers.php';
require_once __DIR__ . '/../../../functions/auth.php';

check_login('guru');
$guru_id = $_SESSION['guru_id'];

// Get Guru's Data for Mapel
$stmt = $pdo->prepare("SELECT g.guru_mapel_id, m.nama_mapel FROM tb_guru g LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id WHERE g.id = ?");
$stmt->execute([$guru_id]);
$guru_data = $stmt->fetch();
$default_mapel_id = $guru_data['guru_mapel_id'];
$default_mapel_nama = $guru_data['nama_mapel'] ?? 'Umum';

// Fetch Kelas
$kelas_list = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC")->fetchAll();

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_materi'])) {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $id_kelas = $_POST['id_kelas'] ?: NULL;
    $id_mapel = $default_mapel_id;

    $file = $_FILES['file_materi'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

    if (in_array($file_ext, $allowed)) {
        if ($file['size'] <= 10 * 1024 * 1024) { // 10MB
            $new_name = 'MATERI_' . time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = '../../../../uploads/materi/';
            
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path . $new_name)) {
                $stmt = $pdo->prepare("INSERT INTO tb_materi (id_guru, id_mapel, id_kelas, judul, deskripsi, file_path, tipe_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$guru_id, $id_mapel, $id_kelas, $judul, $deskripsi, $new_name, $file_ext])) {
                    echo "<script>alert('Materi berhasil diupload'); window.location.href='index.php';</script>";
                } else {
                    $error = "Database Error.";
                }
            } else {
                $error = "Gagal mengupload file.";
            }
        } else {
            $error = "Ukuran file terlalu besar (Max 10MB).";
        }
    } else {
        $error = "Format file tidak diizinkan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Materi - Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <?php include __DIR__ . '/../../../layouts/sidebar_guru.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
                <h1 class="text-xl font-bold text-gray-800">Upload Materi Baru</h1>
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

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Mata Pelajaran</label>
                            <input type="text" value="<?= htmlspecialchars($default_mapel_nama) ?>" readonly class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 text-gray-500 cursor-not-allowed">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Judul Materi</label>
                            <input type="text" name="judul" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Modul Bab 1">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Target Kelas</label>
                            <select name="id_kelas" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach($kelas_list as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika materi untuk semua kelas yang Anda ajar.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">File Materi</label>
                            <div class="flex items-center justify-center w-full">
                                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl mb-2"></i>
                                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span></p>
                                        <p class="text-xs text-gray-500">PDF, DOCX, PPTX, JPG (Max 10MB)</p>
                                    </div>
                                    <input id="dropzone-file" name="file_materi" type="file" class="hidden" required />
                                </label>
                            </div> 
                            <div id="file-name" class="text-sm text-gray-600 mt-2 text-center"></div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi (Opsional)</label>
                            <textarea name="deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow transition">
                                Upload Materi
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.getElementById('dropzone-file').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            document.getElementById('file-name').textContent = 'File terpilih: ' + fileName;
        });
    </script>
</body>
</html>
