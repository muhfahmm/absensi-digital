<?php
// app/pages/admin/guru/create_mapel.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_mapel = htmlspecialchars($_POST['nama_mapel']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tb_mata_pelajaran (nama_mapel) VALUES (:nama)");
        $stmt->execute([':nama' => $nama_mapel]);
        
        echo "<script>alert('Mata Pelajaran Berhasil Ditambahkan!'); window.location.href='index.php?view=mapel';</script>";
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Mata Pelajaran sudah ada!";
        } else {
            $error = "Gagal menyimpan: " . $e->getMessage();
        }
    }
}
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4">
            <h2 class="text-xl font-semibold text-gray-800">Tambah Mata Pelajaran</h2>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-md">
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Pelajaran</label>
                        <input type="text" name="nama_mapel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Matematika" required>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php?view=mapel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-md">Simpan Mapel</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
