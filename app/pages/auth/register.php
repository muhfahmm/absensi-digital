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
        
        <!-- Toggle Role -->
        <!-- Toggle Role -->
        <div class="flex justify-center mb-6 bg-white bg-opacity-20 rounded-lg p-1" x-data="{ role: 'siswa' }">
            <button type="button" 
                onclick="setRole('siswa')"
                id="btnSiswa"
                class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 bg-white text-indigo-600 shadow-sm">
                Siswa
            </button>
            <button type="button" 
                onclick="setRole('guru')"
                id="btnGuru"
                class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                Guru
            </button>
            <button type="button" 
                onclick="setRole('admin')"
                id="btnAdmin"
                class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                Admin
            </button>
        </div>

        <form action="<?= base_url('app/auth/api-register.php') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="role" id="roleInput" value="siswa">
            
            <!-- Nama Lengkap -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Contoh: Budi Santoso" required>
            </div>

            <!-- Form Khusus Siswa -->
            <div id="roleSiswaForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-200 mb-1">NIS (Nomor Induk Siswa)</label>
                    <input type="number" name="u_id" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Masukkan NIS">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-200 mb-1">Kelas</label>
                    <select name="id_kelas" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition [&>option]:text-gray-900">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Form Khusus Guru -->
            <div id="roleGuruForm" class="hidden">
                 <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-200 mb-1">NIP (Nomor Induk Pegawai)</label>
                    <input type="number" name="nip_guru" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Masukkan NIP">
                </div>
            </div>

            <!-- Form Khusus Admin -->
            <div id="roleAdminForm" class="hidden">
                 <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-200 mb-1">Username</label>
                    <input type="text" name="username_admin" class="w-full px-4 py-3 bg-white bg-opacity-20 border border-gray-300 border-opacity-30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Buat Username">
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

<script>
    function setRole(role) {
        document.getElementById('roleInput').value = role;
        
        // Reset Styles
        ['btnSiswa', 'btnGuru', 'btnAdmin'].forEach(id => {
            document.getElementById(id).classList.remove('bg-white', 'text-indigo-600');
            document.getElementById(id).classList.add('text-white', 'hover:bg-white/10');
        });

        // Set Active Style
        let activeBtn = role === 'siswa' ? 'btnSiswa' : (role === 'guru' ? 'btnGuru' : 'btnAdmin');
        document.getElementById(activeBtn).classList.remove('text-white', 'hover:bg-white/10');
        document.getElementById(activeBtn).classList.add('bg-white', 'text-indigo-600');

        // Toggle Forms
        document.getElementById('roleSiswaForm').classList.add('hidden');
        document.getElementById('roleGuruForm').classList.add('hidden');
        document.getElementById('roleAdminForm').classList.add('hidden');
        
        // Disable all specific inputs first to avoid submitting empty wrong data
        document.querySelector('input[name="u_id"]').disabled = true; // NIS Siswa
        document.querySelector('input[name="nip_guru"]').disabled = true;
        document.querySelector('input[name="username_admin"]').disabled = true;

        if(role === 'siswa') {
            document.getElementById('roleSiswaForm').classList.remove('hidden');
            document.querySelector('input[name="u_id"]').disabled = false;
            document.querySelector('input[name="u_id"]').required = true;
        } else if(role === 'guru') {
            document.getElementById('roleGuruForm').classList.remove('hidden');
            document.querySelector('input[name="nip_guru"]').disabled = false;
            document.querySelector('input[name="nip_guru"]').required = true;
        } else if(role === 'admin') {
            document.getElementById('roleAdminForm').classList.remove('hidden');
            document.querySelector('input[name="username_admin"]').disabled = false;
            document.querySelector('input[name="username_admin"]').required = true;
        }
    }

    // Init
    setRole('siswa');
</script>

<?php require_once '../../layouts/footer.php'; ?>
