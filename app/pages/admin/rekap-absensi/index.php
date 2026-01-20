<?php
// app/pages/admin/rekap-absensi/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// Get admin info
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$admin_kelas_id = $_SESSION['admin_kelas_id'] ?? null;
$initial = substr($admin_name, 0, 1);

// Determine admin role display
$nama_peran = 'Admin Global';
if ($admin_id) {
    $stmtPeran = $pdo->prepare("SELECT m.nama_mapel, k.nama_kelas FROM tb_admin a LEFT JOIN tb_guru g ON a.nuptk = g.nuptk LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id WHERE a.id = ?");
    $stmtPeran->execute([$admin_id]);
    $peran = $stmtPeran->fetch();
    
    $roles = [];
    if (!empty($peran['nama_mapel'])) $roles[] = "Guru " . $peran['nama_mapel'];
    if (!empty($peran['nama_kelas'])) $roles[] = "Wali Kelas " . $peran['nama_kelas'];
    if (!empty($roles)) $nama_peran = "Admin Global (" . implode(" & ", $roles) . ")";
}

// Fetch kelas list
if ($admin_kelas_id) {
    $stmt_kelas = $pdo->prepare("SELECT * FROM tb_kelas WHERE id = ?");
    $stmt_kelas->execute([$admin_kelas_id]);
    $kelas_list = $stmt_kelas->fetchAll();
} else {
    $stmt_kelas = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
    $kelas_list = $stmt_kelas->fetchAll();
}

// Filter parameters
$filter_tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$filter_kelas_siswa = $_GET['kelas_siswa'] ?? ($kelas_list[0]['id'] ?? '');

// Get all students for selected class
$sql_siswa = "SELECT s.*, k.nama_kelas 
              FROM tb_siswa s 
              LEFT JOIN tb_kelas k ON s.id_kelas = k.id 
              WHERE 1=1";
$params_siswa = [];

if ($filter_kelas_siswa) {
    $sql_siswa .= " AND s.id_kelas = :kelas_id";
    $params_siswa[':kelas_id'] = $filter_kelas_siswa;
}

$sql_siswa .= " ORDER BY s.nama_lengkap ASC";
$stmt_siswa = $pdo->prepare($sql_siswa);
$stmt_siswa->execute($params_siswa);
$all_siswa = $stmt_siswa->fetchAll();

// Get all teachers
$sql_guru = "SELECT g.*, k.nama_kelas 
             FROM tb_guru g 
             LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
             ORDER BY g.nama_lengkap ASC";
$stmt_guru = $pdo->query($sql_guru);
$all_guru = $stmt_guru->fetchAll();

// Get attendance for selected date
$sql_absensi = "SELECT * FROM tb_absensi WHERE tanggal = :tanggal";
$stmt_absensi = $pdo->prepare($sql_absensi);
$stmt_absensi->execute([':tanggal' => $filter_tanggal]);
$absensi_data = $stmt_absensi->fetchAll();

// Create lookup arrays
$absensi_siswa = [];
$absensi_guru = [];

foreach ($absensi_data as $abs) {
    if ($abs['role'] == 'siswa') {
        $absensi_siswa[$abs['user_id']] = $abs;
    } elseif ($abs['role'] == 'guru') {
        $absensi_guru[$abs['user_id']] = $abs;
    }
}

// Calculate statistics
$total_siswa = count($all_siswa);
$hadir_siswa = count(array_filter($all_siswa, fn($s) => isset($absensi_siswa[$s['id']])));
$belum_absen_siswa = $total_siswa - $hadir_siswa;

$total_guru = count($all_guru);
$hadir_guru = count(array_filter($all_guru, fn($g) => isset($absensi_guru[$g['id']])));
$belum_absen_guru = $total_guru - $hadir_guru;

// Get selected class name
$selected_kelas_name = 'Semua Kelas';
if ($filter_kelas_siswa) {
    foreach ($kelas_list as $k) {
        if ($k['id'] == $filter_kelas_siswa) {
            $selected_kelas_name = $k['nama_kelas'];
            break;
        }
    }
}
?>

<style>
    .tab-button {
        transition: all 0.3s ease;
    }
    .tab-button.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    .tab-content {
        display: none;
        animation: fadeIn 0.5s;
    }
    .tab-content.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Rekap Kehadiran Harian</h2>
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
            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Filter Tanggal</h3>
                    <form action="" method="GET" class="flex items-center space-x-3">
                        <input type="date" name="tanggal" value="<?= $filter_tanggal ?>" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="hidden" name="kelas_siswa" value="<?= $filter_kelas_siswa ?>">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter
                        </button>
                        <button type="button" onclick="exportExcel()" id="export-btn" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg shadow transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export Excel
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="border-b border-gray-200">
                    <div class="flex space-x-2 p-2">
                        <button onclick="switchTab('siswa')" id="tab-siswa" class="tab-button active flex-1 px-6 py-3 rounded-lg font-semibold flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Siswa
                        </button>
                        <button onclick="switchTab('guru')" id="tab-guru" class="tab-button flex-1 px-6 py-3 rounded-lg font-semibold flex items-center justify-center bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Guru
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Siswa -->
            <div id="content-siswa" class="tab-content active">
                <!-- Statistics Cards Siswa -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Total Siswa</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_siswa ?></p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-4">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Sudah Hadir</p>
                                <p class="text-3xl font-bold text-green-600"><?= $hadir_siswa ?></p>
                            </div>
                            <div class="bg-green-100 rounded-full p-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Belum Absen</p>
                                <p class="text-3xl font-bold text-red-600"><?= $belum_absen_siswa ?></p>
                            </div>
                            <div class="bg-red-100 rounded-full p-4">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelas Filter for Siswa -->
                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <form action="" method="GET" class="flex items-center space-x-4">
                        <label class="text-sm font-semibold text-gray-700">Pilih Kelas:</label>
                        <input type="hidden" name="tanggal" value="<?= $filter_tanggal ?>">
                        <select name="kelas_siswa" onchange="this.form.submit()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <?php foreach($kelas_list as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $filter_kelas_siswa == $k['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Siswa Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                        <h4 class="text-white font-semibold text-lg flex items-center justify-between">
                            <span><?= htmlspecialchars($selected_kelas_name) ?></span>
                            <span class="text-sm bg-white bg-opacity-20 px-4 py-1 rounded-full">
                                <?= $hadir_siswa ?> / <?= $total_siswa ?> Hadir
                            </span>
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">NIS</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Jam Masuk</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Jam Keluar</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (count($all_siswa) > 0): ?>
                                    <?php foreach ($all_siswa as $idx => $siswa): ?>
                                        <?php $abs = $absensi_siswa[$siswa['id']] ?? null; ?>
                                        <tr class="hover:bg-gray-50 <?= $abs ? 'bg-green-50' : 'bg-red-50' ?>">
                                            <td class="px-6 py-4 text-sm"><?= $idx + 1 ?></td>
                                            <td class="px-6 py-4 text-sm font-mono"><?= htmlspecialchars($siswa['nis']) ?></td>
                                            <td class="px-6 py-4 text-sm font-semibold"><?= htmlspecialchars($siswa['nama_lengkap']) ?></td>
                                            <td class="px-6 py-4 text-sm text-center font-mono">
                                                <?= $abs ? date('H:i', strtotime($abs['jam_masuk'])) : '-' ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-center font-mono">
                                                <?= $abs && $abs['jam_keluar'] ? date('H:i', strtotime($abs['jam_keluar'])) : '-' ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-center">
                                                <?php if ($abs): ?>
                                                    <?php
                                                        $status = ucfirst($abs['status']);
                                                        $color = 'gray';
                                                        if($status == 'Hadir') $color = 'green';
                                                        elseif($status == 'Terlambat') $color = 'yellow';
                                                        elseif($status == 'Sakit') $color = 'blue';
                                                        elseif($status == 'Izin') $color = 'indigo';
                                                        elseif($status == 'Alpa') $color = 'red';
                                                    ?>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-<?= $color ?>-100 text-<?= $color ?>-800">
                                                        <?= $status ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                        Belum Absen
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            <p>Tidak ada data siswa</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Guru -->
            <div id="content-guru" class="tab-content">
                <!-- Statistics Cards Guru -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Total Guru</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_guru ?></p>
                            </div>
                            <div class="bg-purple-100 rounded-full p-4">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Sudah Hadir</p>
                                <p class="text-3xl font-bold text-green-600"><?= $hadir_guru ?></p>
                            </div>
                            <div class="bg-green-100 rounded-full p-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Belum Absen</p>
                                <p class="text-3xl font-bold text-red-600"><?= $belum_absen_guru ?></p>
                            </div>
                            <div class="bg-red-100 rounded-full p-4">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guru Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                        <h4 class="text-white font-semibold text-lg flex items-center justify-between">
                            <span>Daftar Guru</span>
                            <span class="text-sm bg-white bg-opacity-20 px-4 py-1 rounded-full">
                                <?= $hadir_guru ?> / <?= $total_guru ?> Hadir
                            </span>
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">NUPTK</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Wali Kelas</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Jam Masuk</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Jam Keluar</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (count($all_guru) > 0): ?>
                                    <?php foreach ($all_guru as $idx => $guru): ?>
                                        <?php $abs = $absensi_guru[$guru['id']] ?? null; ?>
                                        <tr class="hover:bg-gray-50 <?= $abs ? 'bg-green-50' : 'bg-red-50' ?>">
                                            <td class="px-6 py-4 text-sm"><?= $idx + 1 ?></td>
                                            <td class="px-6 py-4 text-sm font-mono"><?= htmlspecialchars($guru['nuptk']) ?></td>
                                            <td class="px-6 py-4 text-sm font-semibold"><?= htmlspecialchars($guru['nama_lengkap']) ?></td>
                                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($guru['nama_kelas'] ?? '-') ?></td>
                                            <td class="px-6 py-4 text-sm text-center font-mono">
                                                <?= $abs ? date('H:i', strtotime($abs['jam_masuk'])) : '-' ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-center font-mono">
                                                <?= $abs && $abs['jam_keluar'] ? date('H:i', strtotime($abs['jam_keluar'])) : '-' ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-center">
                                                <?php if ($abs): ?>
                                                    <?php
                                                        $status = ucfirst($abs['status']);
                                                        $color = 'gray';
                                                        if($status == 'Hadir') $color = 'green';
                                                        elseif($status == 'Terlambat') $color = 'yellow';
                                                        elseif($status == 'Sakit') $color = 'blue';
                                                        elseif($status == 'Izin') $color = 'indigo';
                                                        elseif($status == 'Alpa') $color = 'red';
                                                    ?>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-<?= $color ?>-100 text-<?= $color ?>-800">
                                                        <?= $status ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                        Belum Absen
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            <p>Tidak ada data guru</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
let currentTab = 'siswa'; // Default tab

function switchTab(tabName) {
    currentTab = tabName;
    
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
        button.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.add('active');
    
    // Add active class to selected button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active');
    activeButton.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
}

function exportExcel() {
    const tanggal = '<?= $filter_tanggal ?>';
    const kelasSiswa = '<?= $filter_kelas_siswa ?>';
    
    let url = 'export_excel.php?tanggal=' + tanggal + '&tab=' + currentTab;
    
    if (currentTab === 'siswa') {
        url += '&kelas_siswa=' + kelasSiswa;
    }
    
    window.location.href = url;
}
</script>

<?php require_once '../../../layouts/footer.php'; ?>
