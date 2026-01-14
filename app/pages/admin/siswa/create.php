<?php
// app/pages/admin/siswa/create.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// Fetch Kelas untuk Dropdown
$stmt = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
$kelas_list = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = htmlspecialchars($_POST['nis']);
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $id_kelas = $_POST['id_kelas'];
    
    // Generate QR Token
    $kode_qr = "SISWA-" . $nis . "-" . uniqid();

    // Upload Foto
    $foto_name = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../../../uploads/siswa/";
        // Buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_filename = "SISWA_" . $nis . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Validasi tipe file
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($file_extension), $allowed)) {
             if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                 $foto_name = $new_filename;
             } else {
                 $error = "Gagal mengupload foto.";
             }
        } else {
            $error = "Hanya file JPG, JPEG, dan PNG yang diperbolehkan.";
        }
    }

    if (!$error) {
        try {
            // Password Default jika tidak diisi atau input password
            $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO tb_siswa (nis, nama_lengkap, id_kelas, kode_qr, foto_profil, password) VALUES (:nis, :nama, :kelas, :qr, :foto, :pass)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nis' => $nis,
                ':nama' => $nama,
                ':kelas' => $id_kelas,
                ':qr' => $kode_qr,
                ':foto' => $foto_name,
                ':pass' => $pass_hash
            ]);
            
            echo "<script>alert('Siswa Berhasil Ditambahkan!'); window.location.href='index.php';</script>";
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "NIS sudah terdaftar!";
            } else {
                $error = "Gagal menyimpan: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4">
            <h2 class="text-xl font-semibold text-gray-800">Tambah Data Siswa</h2>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
                        <input type="number" name="nis" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nomor Induk Siswa" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama Siswa" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select name="id_kelas" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach($kelas_list as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Masukkan password" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Siswa (Opsional)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maks 2MB.</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-md">Simpan Siswa</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
