<?php
// app/pages/auth/register.php
session_start();
require_once '../../functions/helpers.php';
require_once '../../config/database.php';
require_once '../../layouts/header.php';

// Fetch data kelas untuk dropdown siswa
$stmt = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
$kelas_list = $stmt->fetchAll();

// Get Flash Messages
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<div class="min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    
    <div class="relative w-full max-w-lg p-8 glass rounded-2xl shadow-2xl mx-4 my-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Daftar Akun Baru</h1>
            <p class="text-gray-200">Buat akun untuk mengakses Absensi Digital</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 bg-opacity-80 text-white p-3 rounded-lg mb-4 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500 bg-opacity-80 text-white p-3 rounded-lg mb-4 text-center">
                <?= $success ?> <br>
                <a href="<?= base_url('app/pages/auth/login.php') ?>" class="underline font-bold mt-2 inline-block">Ke Halaman Login</a>
            </div>
        <?php endif; ?>
        
        <!-- Info: Admin Only Registration -->
        <div class="mb-6 p-4 bg-white/10 rounded-lg border border-white/20">
            <p class="text-white text-sm text-center">
                <strong>Registrasi Admin</strong><br>
                Guru dan Siswa didaftarkan oleh Admin melalui panel admin.
            </p>
        </div>

        <form action="<?= base_url('app/auth/api-register.php') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="role" id="roleInput" value="admin">
            
            <!-- Nama Lengkap -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Contoh: Budi Santoso" required>
            </div>

            <!-- Form Khusus Admin -->
            <div>
                 <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-200 mb-1">Username</label>
                    <input type="text" name="username_admin" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Buat Username" required>
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Password</label>
                <input type="password" name="password" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Buat password aman" required>
            </div>
            
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white font-bold rounded-lg transform transition hover:scale-[1.02] shadow-lg mt-4">
                Daftar Sekarang
            </button>
            
            <div class="text-center mt-6">
                <span class="text-sm text-gray-300">Sudah punya akun? </span>
                <a href="<?= base_url('app/pages/auth/login.php') ?>" class="text-sm font-bold text-white hover:underline transition">Login disini</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../layouts/footer.php'; ?>
