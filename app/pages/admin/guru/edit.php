<?php
// app/pages/admin/guru/edit.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

$id = $_GET['id'] ?? null;
if (!$id) redirect('app/pages/admin/guru/index.php');

// Fetch Guru
$stmt = $pdo->prepare("SELECT * FROM tb_guru WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) redirect('app/pages/admin/guru/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = htmlspecialchars($_POST['nip']);
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    
    // Password (Update jika diisi saja)
    $password_query = "";
    $params = [
        ':nip' => $nip, 
        ':nama' => $nama,
        ':id' => $id
    ];

    if (!empty($_POST['password'])) {
        $password_query = ", password = :pass";
        $params[':pass'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    try {
        $sql = "UPDATE tb_guru SET nip = :nip, nama_lengkap = :nama $password_query WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo "<script>alert('Data Guru Berhasil Diupdate!'); window.location.href='index.php';</script>";
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
            <h2 class="text-xl font-semibold text-gray-800">Edit Data Guru</h2>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <input type="number" name="nip" value="<?= htmlspecialchars($data['nip']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                        <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Kosongkan jika tidak ingin mengganti">
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">Update Guru</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
