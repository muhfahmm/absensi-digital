<?php
// app/pages/admin/admin/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// Fetch Data Admin (Join dengan Guru untuk info Wali Kelas)
$stmt = $pdo->query("
    SELECT a.*, k.nama_kelas 
    FROM tb_admin a 
    LEFT JOIN tb_guru g ON a.nuptk = g.nuptk 
    LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
    ORDER BY a.created_at DESC
");
$admins = $stmt->fetchAll();
// --- Header Profile Logic ---
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$nama_peran = 'Admin Global';
$initial = substr($admin_name, 0, 1);

if ($admin_id) {
    $stmtPeran = $pdo->prepare("SELECT m.nama_mapel, k.nama_kelas FROM tb_admin a LEFT JOIN tb_guru g ON a.nuptk = g.nuptk LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id WHERE a.id = ?");
    $stmtPeran->execute([$admin_id]);
    $peran = $stmtPeran->fetch();
    
    $roles = [];
    if (!empty($peran['nama_mapel'])) $roles[] = "Guru " . $peran['nama_mapel'];
    if (!empty($peran['nama_kelas'])) $roles[] = "Wali Kelas " . $peran['nama_kelas'];
    elseif (isset($_SESSION['admin_kelas_id']) && $_SESSION['admin_kelas_id']) {
        $stmtKelas = $pdo->prepare("SELECT nama_kelas FROM tb_kelas WHERE id = ?");
        $stmtKelas->execute([$_SESSION['admin_kelas_id']]);
        if ($k = $stmtKelas->fetch()) $roles[] = "Wali Kelas " . $k['nama_kelas'];
    }
    if (!empty($roles)) $nama_peran = "Admin Global (" . implode(" & ", $roles) . ")";
}
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Data Admin</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <?= $nama_peran ?>
                    </p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="mb-6 flex justify-between items-center">
                <div class="text-gray-600 text-sm">Kelola data administrator sistem.</div>
                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Admin
                </a>
            </div>

            <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Foto</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">NUPTK</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">QR Code</th>
                            <th class="px-5 py-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($admins) > 0): ?>
                            <?php foreach($admins as $index => $row): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm"><?= $index + 1 ?></td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                    <?php if(!empty($row['foto_profil']) && file_exists("../../../../uploads/admin/" . $row['foto_profil'])): ?>
                                        <img src="<?= base_url('uploads/admin/' . $row['foto_profil']) ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover shadow-sm bg-gray-100">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-[10px] font-bold">ADM</div>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-gray-400">
                                    <?= !empty($row['nuptk']) ? htmlspecialchars($row['nuptk']) : '-' ?>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                    <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-mono text-xs font-bold"><?= htmlspecialchars($row['username']) ?></span>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm font-bold text-gray-800"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                                    <?php if($row['nama_kelas']): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-blue-200"><?= $row['nama_kelas'] ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">Admin Global</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-center">
                                    <?php if(!empty($row['kode_qr'])): ?>
                                        <div id="qr-admin-<?= $index ?>" class="flex justify-center"></div>
                                        <script>
                                            new QRCode(document.getElementById("qr-admin-<?= $index ?>"), {
                                                text: "<?= $row['kode_qr'] ?>",
                                                width: 50,
                                                height: 50
                                            });
                                        </script>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button onclick="requestAction('edit.php?id=<?= $row['id'] ?>', 'edit')" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-indigo-100">Edit</button>
                                        <button onclick="requestAction('delete.php?id=<?= $row['id'] ?>', 'delete')" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-bold transition hover:bg-red-100">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-5 py-10 border-b border-gray-100 bg-white text-sm text-center text-gray-400 italic">
                                    Belum ada data admin.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Security Verification Modal -->
<div id="securityModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Verifikasi Keamanan
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">
                                Untuk alasan keamanan, silakan masukkan <b>Username dan Password Akun yang akan dihapus/diubah</b>.
                            </p>
                            <form id="verifyForm">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="verify_username">
                                        Username Target
                                    </label>
                                    <input type="text" id="verify_username" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="verify_password">
                                        Password Target
                                    </label>
                                    <input type="password" id="verify_password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required placeholder="********">
                                </div>
                                <div id="verifyError" class="text-red-500 text-xs italic hidden"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="processVerification()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Verifikasi & Lanjutkan
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let targetUrl = '';
    let targetId = '';

    function requestAction(url, actionType) {
        targetUrl = url;

        // Extract ID from URL for verification
        const urlParams = new URLSearchParams(url.split('?')[1]);
        targetId = urlParams.get('id');

        document.getElementById('securityModal').classList.remove('hidden');
        document.getElementById('verify_username').value = '';
        document.getElementById('verify_password').value = '';
        document.getElementById('verifyError').classList.add('hidden');
        document.getElementById('verify_username').focus();
    }

    function closeModal() {
        document.getElementById('securityModal').classList.add('hidden');
    }

    function processVerification() {
        const username = document.getElementById('verify_username').value;
        const pass = document.getElementById('verify_password').value;
        const errorDiv = document.getElementById('verifyError');

        if (!username || !pass) {
            errorDiv.innerText = 'Mohon isi username dan password.';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Send to verification endpoint
        fetch('verify_target.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ target_id: targetId, username: username, password: pass })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success, proceed to target URL
                window.location.href = targetUrl;
            } else {
                errorDiv.innerText = data.message || 'Verifikasi gagal.';
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorDiv.innerText = 'Terjadi kesalahan sistem.';
            errorDiv.classList.remove('hidden');
        });
    }
</script>

<?php require_once '../../../layouts/footer.php'; ?>
