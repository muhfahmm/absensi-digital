<?php
// app/pages/admin/kelas/edit.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

$id = $_GET['id'] ?? null;
if (!$id) redirect('app/pages/admin/kelas/index.php');

// Fetch Data
$stmt = $pdo->prepare("SELECT * FROM tb_kelas WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) redirect('app/pages/admin/kelas/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kelas = htmlspecialchars($_POST['nama_kelas']);
    $jumlah_siswa = (int) $_POST['jumlah_siswa'];
    
    // Token tidak diupdate agar QR Code tidak berubah sembarangan

    try {
        $sql = "UPDATE tb_kelas SET nama_kelas = :nama, jumlah_siswa = :jml WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama' => $nama_kelas, 
            ':jml' => $jumlah_siswa,
            ':id' => $id
        ]);
        
        echo "<script>alert('Data Kelas Berhasil Diupdate!'); window.location.href='index.php';</script>";
        exit;

    } catch (PDOException $e) {
        $error = "Gagal mengupdate: " . $e->getMessage();
    }
}
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4">
            <h2 class="text-xl font-semibold text-gray-800">Edit Data Kelas</h2>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                        <input type="text" name="nama_kelas" value="<?= htmlspecialchars($data['nama_kelas']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas / Jumlah Siswa</label>
                        <input type="number" name="jumlah_siswa" value="<?= $data['jumlah_siswa'] ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    
                    <div>
                        <input type="hidden" name="token_dummy">
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">Update Data</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
