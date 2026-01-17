<?php
// app/pages/admin/guru/create.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

$error = '';

// Fetch Admins for Dropdown
$stmt_admins = $pdo->query("SELECT * FROM tb_admin ORDER BY nama_lengkap ASC");
$admins = $stmt_admins->fetchAll();

// Fetch Mapel for Dropdown
$stmt_mapel = $pdo->query("SELECT * FROM tb_mata_pelajaran ORDER BY nama_mapel ASC");
$mapels = $stmt_mapel->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nuptk = htmlspecialchars($_POST['nuptk']);
    $id_admin_source = $_POST['id_admin_source'] ?? '';
    $id_kelas_wali = !empty($_POST['id_kelas_wali']) ? $_POST['id_kelas_wali'] : null;
    $guru_mapel_id = !empty($_POST['guru_mapel_id']) ? $_POST['guru_mapel_id'] : null;
    $kode_guru = htmlspecialchars($_POST['kode_guru'] ?? '');
    
    // Default variables (will be overridden if admin source selected)
    $username = null;
    $nama = null;
    $pass_hash = null;
    $foto_name = null;

    if (!empty($id_admin_source)) {
        // AMBIL DATA DARI ADMIN
        $stmt_src = $pdo->prepare("SELECT * FROM tb_admin WHERE id = ?");
        $stmt_src->execute([$id_admin_source]);
        $adm_data = $stmt_src->fetch();
        
        if ($adm_data) {
            $username = $adm_data['username'];
            $nama = $adm_data['nama_lengkap'];
            $pass_hash = $adm_data['password']; // Copy existing hash
            
            // Copy Foto Profile if exists
            if (!empty($adm_data['foto_profil']) && file_exists("../../../../uploads/admin/" . $adm_data['foto_profil'])) {
                $ext = pathinfo($adm_data['foto_profil'], PATHINFO_EXTENSION);
                $foto_name = "GURU_" . $nuptk . "_" . time() . "." . $ext;
                // Create guru dir if not exists
                if (!file_exists("../../../../uploads/guru/")) {
                    mkdir("../../../../uploads/guru/", 0777, true);
                }
                copy("../../../../uploads/admin/" . $adm_data['foto_profil'], "../../../../uploads/guru/" . $foto_name);
            }
        }
    } else {
        // MANUAL INPUT
        $username = htmlspecialchars($_POST['username']);
        $nama = htmlspecialchars($_POST['nama_lengkap']);
        $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    
    // Generate QR Token
    $kode_qr = "GURU-" . $nuptk . "-" . uniqid();

    // Password
    $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Upload Foto (Only if manual or override)
    // Note: If using Admin source, we already copied photo above.
    // But if user uploads a file manually even when admin selected (optional override?), we can handle it.
    // For now, let's stick to: If Manual -> Upload. If Admin -> Copy.
    if (empty($id_admin_source) && isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../../../../uploads/guru/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_filename = "GURU_" . $nuptk . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($file_extension), $allowed)) {
             if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                 $foto_name = $new_filename;
             }
        }
    }

    try {
        $sql = "INSERT INTO tb_guru (nuptk, username, nama_lengkap, kode_qr, password, foto_profil, id_kelas_wali, guru_mapel_id, kode_guru) VALUES (:nuptk, :username, :nama, :qr, :pass, :foto, :wali, :mapel, :kode)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nuptk' => $nuptk,
            ':username' => $username,
            ':nama' => $nama,
            ':qr' => $kode_qr,
            ':pass' => $pass_hash,
            ':foto' => $foto_name,
            ':wali' => $id_kelas_wali,
            ':mapel' => $guru_mapel_id,
            ':kode' => $kode_guru
        ]);
        
        if (isset($_POST['is_admin'])) {
            // Cek apakah username sudah ada di admin
            $stmt_check = $pdo->prepare("SELECT id FROM tb_admin WHERE username = :user");
            $stmt_check->execute([':user' => $username]);
            
            if ($stmt_check->rowCount() == 0) {
                // Copy foto to admin folder check
                $foto_admin = null;
                if ($foto_name) {
                    $dir_admin = "../../../../uploads/admin/";
                    if (!file_exists($dir_admin)) {
                        mkdir($dir_admin, 0777, true);
                    }
                    if (copy($target_dir . $foto_name, $dir_admin . $foto_name)) {
                        $foto_admin = $foto_name;
                    }
                }

                // Insert to tb_admin
                $sql_admin = "INSERT INTO tb_admin (username, password, nama_lengkap, nuptk, kode_qr, foto_profil) VALUES (:user, :pass, :nama, :nuptk, :qr, :foto)";
                $stmt_admin = $pdo->prepare($sql_admin);
                $stmt_admin->execute([
                    ':user' => $username,
                    ':pass' => $pass_hash,
                    ':nama' => $nama,
                    ':nuptk' => $nuptk,
                    ':qr' => $kode_qr,
                    ':foto' => $foto_admin
                ]);
            }
        }
        
        echo "<script>alert('Guru Berhasil Ditambahkan!'); window.location.href='index.php';</script>";
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "NUPTK atau Username sudah terdaftar!";
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
            <h2 class="text-xl font-semibold text-gray-800">Tambah Data Guru</h2>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-md">
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Pilihan Sumber Data -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-6">
                        <label class="block text-sm font-bold text-blue-800 mb-2">Ambil Data dari Admin (Opsional)</label>
                        <select name="id_admin_source" id="id_admin_source" onchange="toggleForm(this)" class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">-- Manual (Input Baru) --</option>
                            <?php foreach($admins as $adm): ?>
                                <option value="<?= $adm['id'] ?>">
                                    <?= $adm['nama_lengkap'] ?> (<?= $adm['username'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-blue-600 mt-2">
                            * Jika dipilih, Username, Nama, Password, dan Foto akan diambil otomatis dari data Admin tersebut.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NUPTK (Nomor Unik Pendidik dan Tenaga Kependidikan)</label>
                        <input type="number" name="nuptk" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="1xxxxxxxxxxxxxxx" required>
                    </div>

                    <div id="manual_fields" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" name="username" id="username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Username untuk Login">
                        </div>
    
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama Guru beserta Gelar">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Guru (Inisial)</label>
                            <input type="text" name="kode_guru" id="kode_guru" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Ap, Na, Rs" maxlength="5">
                        </div>
    
                        <div id="div_make_admin" class="flex items-center space-x-2 bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                            <input type="checkbox" name="is_admin" id="is_admin" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="is_admin" class="text-sm font-semibold text-gray-700 cursor-pointer">Jadikan sebagai Admin juga?</label>
                        </div>
    
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Masukkan password">
                        </div>
    
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Guru (Opsional)</label>
                            <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maks 2MB.</p>
                        </div>
                    </div>

                    <!-- Input Wali Kelas (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tugas Wali Kelas (Opsional)</label>
                        <select name="id_kelas_wali" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Bukan Wali Kelas --</option>
                            <?php 
                            $stmt_kelas = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
                            while($kelas = $stmt_kelas->fetch()): 
                            ?>
                                <option value="<?= $kelas['id'] ?>"><?= $kelas['nama_kelas'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih kelas jika guru ini adalah wali kelas.</p>
                    </div>

                    <!-- Input Mata Pelajaran (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran Diampu (Opsional)</label>
                        <select name="guru_mapel_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Tidak Mengampu Mapel --</option>
                            <?php foreach($mapels as $mapel): ?>
                                <option value="<?= $mapel['id'] ?>"><?= $mapel['nama_mapel'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih mata pelajaran yang diajar oleh guru ini.</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-md">Simpan Guru</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

                </form>
            </div>
        </main>
    </div>
</div>

<script>
    function toggleForm(selectInfo) {
        const manualFields = document.getElementById('manual_fields');
        const username = document.getElementById('username');
        const nama = document.getElementById('nama_lengkap');
        const password = document.getElementById('password');
        const is_admin_div = document.getElementById('div_make_admin');

        if (selectInfo.value !== "") {
            // Jika memilih admin, sembunyikan form manual
            manualFields.style.display = 'none';
            
            // Disable requirement checks
            username.removeAttribute('required');
            nama.removeAttribute('required');
            password.removeAttribute('required');
        } else {
            // Jika manual, tampilkan form
            manualFields.style.display = 'block';
            
            // Enable requirements
            username.setAttribute('required', 'required');
            nama.setAttribute('required', 'required');
            password.setAttribute('required', 'required');
            is_admin_div.style.display = 'flex'; // Reset display
        }
    }
</script>
