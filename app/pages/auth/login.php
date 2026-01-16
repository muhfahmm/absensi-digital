<?php
// app/pages/auth/login.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../config/database.php';

// Get Flash Messages
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error']);
unset($_SESSION['success']);

require_once '../../layouts/header.php';

// Fetch data kelas untuk dropdown wali kelas
$stmt = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
$kelas_list = $stmt->fetchAll();
?>

<div class="min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    
    <div class="relative w-full max-w-md p-8 glass rounded-2xl shadow-2xl mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Absensi Digital</h1>
            <p class="text-gray-200">Silakan login untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 bg-opacity-80 text-white p-3 rounded-lg mb-4 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500 bg-opacity-80 text-white p-3 rounded-lg mb-4 text-center">
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= base_url('app/auth/api-login.php') ?>" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-200 mb-1">Username / NIS</label>
                <input type="text" id="username" name="username" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Masukkan ID Anda" required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-200 mb-1">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="••••••••" required>
            </div>

            <div>
                <label for="id_kelas" class="block text-sm font-medium text-gray-200 mb-1">Masuk Sebagai Wali Kelas (Pilih Kelas - Opsional)</label>
                <select id="id_kelas" name="id_kelas" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition appearance-none">
                    <option value="" class="bg-gray-800">Semua Kelas (Default)</option>
                    <?php foreach($kelas_list as $kelas): ?>
                        <option value="<?= $kelas['id'] ?>" class="bg-gray-800"><?= $kelas['nama_kelas'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-lg transform transition hover:scale-[1.02] shadow-lg">
                Masuk Sekarang
            </button>
            
            <div class="text-center mt-6">
                <a href="#" class="text-sm text-gray-300 hover:text-white transition block mb-3">Lupa password? Hubungi Admin.</a>
                
                <div class="border-t border-white/20 pt-4">
                    <p class="text-xs text-gray-300 mb-2">Belum punya akun Admin?</p>
                    <a href="<?= base_url('app/pages/auth/register.php') ?>" class="text-sm font-bold text-white hover:underline transition">Daftar Admin</a>
                    <p class="text-xs text-gray-400 mt-3">Siswa & Guru didaftarkan oleh Admin</p>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../layouts/footer.php'; ?>
