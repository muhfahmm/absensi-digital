<?php
// app/pages/admin/guru/edit_admin.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

$id = $_GET['id'] ?? null;
if (!$id) redirect('app/pages/admin/guru/index.php?view=admin');

// Fetch Admin
$stmt = $pdo->prepare("SELECT * FROM tb_admin WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) redirect('app/pages/admin/guru/index.php?view=admin');

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

    // Handle Photo Upload
    $foto_query = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../../../uploads/admin/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_filename = "ADM_" . ($nip ? $nip : time()) . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($file_extension), $allowed)) {
             if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                 $foto_query = ", foto_profil = :foto";
                 $params[':foto'] = $new_filename;
                 
                 // Hapus foto lama jika ada
                 if (!empty($data['foto_profil']) && file_exists($target_dir . $data['foto_profil'])) {
                     unlink($target_dir . $data['foto_profil']);
                 }
             }
        }
    }

    try {
        $sql = "UPDATE tb_admin SET nip = :nip, nama_lengkap = :nama $password_query $foto_query WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo "<script>alert('Data Admin Berhasil Diupdate!'); window.location.href='index.php?view=admin';</script>";
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
            <h2 class="text-xl font-semibold text-gray-800">Edit Data Admin</h2>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-md">
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP (Opsional)</label>
                        <input type="number" name="nip" value="<?= htmlspecialchars($data['nip']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username (Read Only)</label>
                        <input type="text" value="<?= htmlspecialchars($data['username']) ?>" class="w-full px-4 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-500 cursor-not-allowed" readonly>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                        <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Kosongkan jika tidak ingin mengganti">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Admin (Opsional)</label>
                        <?php if(!empty($data['foto_profil'])): ?>
                            <div class="mb-2">
                                <img src="<?= base_url('uploads/admin/' . $data['foto_profil']) ?>" class="w-20 h-20 rounded-lg object-cover border">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Upload foto baru untuk mengganti.</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php?view=admin" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">Update Admin</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
