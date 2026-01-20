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

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
    <div class="w-full max-w-md p-8 bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl shadow-2xl mx-4 border border-white border-opacity-20">
        <div class="text-center mb-8">
            <div class="inline-block p-3 bg-white bg-opacity-20 rounded-full mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Absensi Digital</h1>
            <p class="text-gray-100 text-sm">Silakan login untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 bg-opacity-90 text-white p-3 rounded-lg mb-4 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500 bg-opacity-90 text-white p-3 rounded-lg mb-4 text-center">
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= base_url('app/auth/api-login.php') ?>" method="POST" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-medium text-white mb-2">Username / NIS / NUPTK</label>
                <input type="text" id="username" name="username" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:bg-opacity-30 transition" placeholder="Masukkan ID Anda" required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-white mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:bg-opacity-30 transition" placeholder="••••••••" required>
            </div>

            <div>
                <label for="id_kelas" class="block text-sm font-medium text-white mb-2">Masuk Sebagai Wali Kelas (Opsional)</label>
                <select id="id_kelas" name="id_kelas" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 transition appearance-none">
                    <option value="" class="bg-gray-800">Semua Kelas (Default)</option>
                    <?php foreach($kelas_list as $kelas): ?>
                        <option value="<?= $kelas['id'] ?>" class="bg-gray-800"><?= $kelas['nama_kelas'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-lg transform transition hover:scale-105 shadow-lg">
                Masuk Sekarang
            </button>
            
            <div class="text-center mt-6">
                <a href="#" class="text-sm text-gray-200 hover:text-white transition block mb-3">Lupa password? Hubungi Admin</a>
                
                <div class="border-t border-white border-opacity-20 pt-4">
                    <p class="text-xs text-gray-200 mb-2">Belum punya akun Admin?</p>
                    <a href="<?= base_url('app/pages/auth/register.php') ?>" class="text-sm font-bold text-white hover:underline transition">Daftar Admin</a>
                    <p class="text-xs text-gray-300 mt-3">Siswa & Guru didaftarkan oleh Admin</p>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../layouts/footer.php'; ?>
