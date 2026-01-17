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
    $nuptk = htmlspecialchars($_POST['nuptk']);
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $id_kelas_wali = !empty($_POST['id_kelas_wali']) ? $_POST['id_kelas_wali'] : null;
    $guru_mapel_id = !empty($_POST['guru_mapel_id']) ? $_POST['guru_mapel_id'] : null;
    
    // Password (Update jika diisi saja)
    $password_query = "";
    $params = [
        ':nuptk' => $nuptk, 
        ':nama' => $nama,
        ':wali' => $id_kelas_wali,
        ':mapel' => $guru_mapel_id,
        ':id' => $id
    ];

    if (!empty($_POST['password'])) {
        $password_query = ", password = :pass";
        $params[':pass'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // Handle Photo Upload
    $foto_query = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../../../uploads/guru/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_filename = "GURU_" . $nuptk . "_" . time() . "." . $file_extension;
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
        $sql = "UPDATE tb_guru SET nuptk = :nuptk, nama_lengkap = :nama, id_kelas_wali = :wali, guru_mapel_id = :mapel $password_query $foto_query WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Handle Admin Sync
        if (isset($_POST['is_admin'])) {
            $username = $data['username']; // Username tidak berubah
            
            // Cek exist
            $stmt_ck = $pdo->prepare("SELECT id, foto_profil FROM tb_admin WHERE username = ?");
            $stmt_ck->execute([$username]);
            $existing_admin = $stmt_ck->fetch();

            // Prepare Data for Admin
            $admin_foto = $existing_admin['foto_profil'] ?? null;
            
            // If new photo uploaded for Guru, copy to Admin
            // $new_filename variable comes from the block above
            if (isset($new_filename) && !empty($new_filename)) {
                $dir_admin = "../../../../uploads/admin/";
                if (!file_exists($dir_admin)) mkdir($dir_admin, 0777, true);
                if (copy($target_dir . $new_filename, $dir_admin . $new_filename)) {
                    $admin_foto = $new_filename;
                }
            }

            if ($existing_admin) {
                // Update Existing Admin
                $pass_sql_adm = "";
                $params_adm = [
                    ':nama' => $nama,
                    ':nuptk' => $nuptk,
                    ':foto' => $admin_foto,
                    ':uid' => $existing_admin['id']
                ];
                
                if (!empty($_POST['password'])) {
                    $pass_sql_adm = ", password = :pass";
                    $params_adm[':pass'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                $pdo->prepare("UPDATE tb_admin SET nama_lengkap = :nama, nuptk = :nuptk, foto_profil = :foto $pass_sql_adm WHERE id = :uid")->execute($params_adm);
            } else {
                // Insert New Admin
                // We need the password. If not changin password ($password_query is empty), use existing hash? 
                // We can't get existing hash easily if we didn't fetch it, but we can fetch it from guru table again or just assume if user wants to make admin they should set password?
                // Or better, fetch password from Guru (current)
                $current_pass = $data['password']; 
                if (!empty($_POST['password'])) {
                    $current_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                $pdo->prepare("INSERT INTO tb_admin (username, password, nama_lengkap, nuptk, kode_qr, foto_profil) VALUES (?, ?, ?, ?, ?, ?)")->execute([
                    $username,
                    $current_pass,
                    $nama,
                    $nuptk,
                    $data['kode_qr'],
                    $admin_foto
                ]);
            }
        }
        
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

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NUPTK</label>
                        <input type="number" name="nuptk" value="<?= htmlspecialchars($data['nuptk']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Guru (Opsional)</label>
                        <?php if(!empty($data['foto_profil'])): ?>
                            <div class="mb-2">
                                <img src="<?= base_url('uploads/guru/' . $data['foto_profil']) ?>" class="w-20 h-20 rounded-lg object-cover border">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Upload foto baru untuk mengganti.</p>
                    </div>

                    <!-- Input Wali Kelas (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tugas Wali Kelas (Opsional)</label>
                        <select name="id_kelas_wali" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Bukan Wali Kelas --</option>
                            <?php 
                            $stmt_kelas = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
                            while($kelas = $stmt_kelas->fetch()): 
                                $selected = ($data['id_kelas_wali'] == $kelas['id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $kelas['id'] ?>" <?= $selected ?>><?= $kelas['nama_kelas'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih kelas jika guru ini adalah wali kelas.</p>
                    </div>  

                    <!-- Input Mata Pelajaran (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran Diampu (Opsional)</label>
                        <select name="guru_mapel_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Tidak Mengampu Mapel --</option>
                            <?php 
                            $stmt_mapel = $pdo->query("SELECT * FROM tb_mata_pelajaran ORDER BY nama_mapel ASC");
                            while($mapel = $stmt_mapel->fetch()): 
                                $selected_mapel = ($data['guru_mapel_id'] == $mapel['id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $mapel['id'] ?>" <?= $selected_mapel ?>><?= $mapel['nama_mapel'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih mata pelajaran yang diajar oleh guru ini.</p>
                    </div>

                    <div class="flex items-center space-x-2 bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                        <?php
                            // Cek status admin
                            $stmt_admin_check = $pdo->prepare("SELECT id FROM tb_admin WHERE username = ?");
                            $stmt_admin_check->execute([$data['username']]);
                            $is_admin = $stmt_admin_check->rowCount() > 0;
                        ?>
                        <input type="checkbox" name="is_admin" id="is_admin" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" <?= $is_admin ? 'checked' : '' ?>>
                        <label for="is_admin" class="text-sm font-semibold text-gray-700 cursor-pointer">
                            <?= $is_admin ? 'Update Data Admin juga?' : 'Jadikan sebagai Admin juga?' ?>
                        </label>
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
