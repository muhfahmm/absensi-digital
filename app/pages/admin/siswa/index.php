<?php
// app/pages/admin/siswa/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// 1. Cek Apakah Admin adalah Wali Kelas?
$admin_kelas_id = $_SESSION['kelas_id'] ?? null;
if (!$admin_kelas_id) {
    // Cek ulang ke database (barangkali session belum terupdate fetch dari login)
    // Sebaiknya logic ini sudah beres di login, tapi untuk safety kita cek role lagi via NUPTK jika perlu.
    // Namun asumsi di dashboard sudah set $_SESSION['kelas_id'] atau bisa kita ambil dari table admin langsung.
    $stmtAdm = $pdo->prepare("SELECT g.id_kelas_wali FROM tb_admin a JOIN tb_guru g ON a.nuptk = g.nuptk WHERE a.id = ?");
    $stmtAdm->execute([$_SESSION['user_id']]);
    $admInfo = $stmtAdm->fetch();
    if ($admInfo && $admInfo['id_kelas_wali']) {
        $admin_kelas_id = $admInfo['id_kelas_wali'];
    }
}

// 2. Fetch Kelas untuk Dropdown 
// Jika Wali Kelas, dropdown HANYA menampilkan kelasnya sendiri
if ($admin_kelas_id) {
    $stmt_kelas = $pdo->prepare("SELECT * FROM tb_kelas WHERE id = ?");
    $stmt_kelas->execute([$admin_kelas_id]);
    $kelas_list = $stmt_kelas->fetchAll();
} else {
    // Admin Global: Fetch semua kelas
    $stmt_kelas = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
    $kelas_list = $stmt_kelas->fetchAll();
}

// 3. Filter Logic
// Jika Wali Kelas, paksa filter kelas ke kelas dia
if ($admin_kelas_id) {
    $filter_kelas = $admin_kelas_id;
} else {
    $filter_kelas = $_GET['kelas_id'] ?? '';
}
$filter_siswa = $_GET['siswa_id'] ?? '';

// 4. Fetch Siswa untuk Dropdown 2 (Hanya jika kelas dipilih)
$siswa_list = [];
if ($filter_kelas) {
    $stmt_s = $pdo->prepare("SELECT id, nama_lengkap FROM tb_siswa WHERE id_kelas = ? ORDER BY nama_lengkap ASC");
    $stmt_s->execute([$filter_kelas]);
    $siswa_list = $stmt_s->fetchAll();
}

// 4. Query Utama untuk Tabel
$sql = "SELECT tb_siswa.*, tb_kelas.nama_kelas 
        FROM tb_siswa 
        LEFT JOIN tb_kelas ON tb_siswa.id_kelas = tb_kelas.id 
        WHERE 1=1";

$params = [];

if ($filter_kelas) {
    $sql .= " AND tb_siswa.id_kelas = :kelas_id";
    $params[':kelas_id'] = $filter_kelas;
}

if ($filter_siswa) {
    $sql .= " AND tb_siswa.id = :siswa_id";
    $params[':siswa_id'] = $filter_siswa;
}

$sql .= " ORDER BY tb_siswa.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$siswa = $stmt->fetchAll();
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Data Siswa</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Admin Panel</span>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <!-- Filter & Action -->
            <div class="mb-6 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                <div class="w-full xl:w-auto flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <h3 class="text-gray-700 text-3xl font-medium whitespace-nowrap">Daftar Siswa</h3>
                    
                    <form action="" method="GET" class="w-full sm:w-auto flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 items-center">
                        <!-- Select Kelas -->
                        <?php if ($admin_kelas_id): ?>
                            <!-- Jika Wali Kelas, Tampilkan Nama Kelas Saja (Bukan Dropdown) -->
                            <div class="px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-md font-medium whitespace-nowrap">
                                Kelas: <?= $kelas_list[0]['nama_kelas'] ?? '-' ?>
                            </div>
                        <?php else: ?>
                            <!-- Admin Global: Dropdown -->
                            <select name="kelas_id" 
                                onchange="var s=this.form.elements['siswa_id']; if(s){s.value='';} this.form.submit()" 
                                class="form-select block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach($kelas_list as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= $filter_kelas == $k['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if($filter_kelas): ?>
                                <a href="index.php" class="text-sm text-red-600 hover:text-red-800 underline ml-2 whitespace-nowrap">Reset Filter</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>
                </div>

                <a href="create.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center whitespace-nowrap">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Siswa
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Foto</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIS</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Password</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kelas</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">QR Code</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($siswa) > 0): ?>
                            <?php foreach($siswa as $index => $row): ?>
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= $index + 1 ?></td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <?php if(!empty($row['foto_profil']) && file_exists("../../../../uploads/siswa/" . $row['foto_profil'])): ?>
                                        <img src="<?= base_url('uploads/siswa/' . $row['foto_profil']) ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">No Img</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= htmlspecialchars($row['nis']) ?></td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-700"><?= htmlspecialchars($row['username'] ?? '-') ?></td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-semibold"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-500">••••••</td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"><?= $row['nama_kelas'] ?? 'Belum ada kelas' ?></span>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <div id="qr-<?= $index ?>" class="flex justify-center"></div>
                                    <script>
                                        new QRCode(document.getElementById("qr-<?= $index ?>"), {
                                            text: "<?= $row['kode_qr'] ?>",
                                            width: 64,
                                            height: 64,
                                            colorDark : "#000000",
                                            colorLight : "#ffffff",
                                            correctLevel : QRCode.CorrectLevel.H
                                        });
                                    </script>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="print-qr.php?id=<?= $row['id'] ?>" target="_blank" 
                                           class="text-purple-600 hover:text-purple-900 bg-purple-100 px-3 py-1 rounded-md transition hover:bg-purple-200" 
                                           title="Print QR Card">
                                            🖨️ Print
                                        </a>
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md transition hover:bg-blue-200">Edit</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data siswa ini?')" class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded-md transition hover:bg-red-200">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                                    <?php if($filter_kelas || $filter_siswa): ?>
                                        Tidak ada siswa ditemukan dengan filter ini. <a href="index.php" class="text-indigo-600 underline">Reset</a>
                                    <?php else: ?>
                                        Belum ada data siswa.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
