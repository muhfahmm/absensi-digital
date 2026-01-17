<?php
// app/pages/admin/jadwal/index.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

// --- Helper Logic ---
$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$selected_hari = $_GET['hari'] ?? 'Senin';

// 1. Fetch Jam Pelajaran (Master Time Slots)
$stmtJam = $pdo->query("SELECT * FROM tb_jam_pelajaran ORDER BY jam_mulai ASC");
$jam_pelajaran = $stmtJam->fetchAll();

// 2. Fetch Classes
$stmtKelas = $pdo->query("SELECT * FROM tb_kelas ORDER BY nama_kelas ASC");
$kelas_list = $stmtKelas->fetchAll();

// 3. Fetch Data Jadwal for Selected Day
// Structure: [id_jam][id_kelas] = ['mapel' => ..., 'guru' => ..., 'kode_guru' => ...]
$sqlJadwal = "
    SELECT j.*, m.nama_mapel, g.nama_lengkap, g.kode_guru 
    FROM tb_jadwal_pelajaran j
    JOIN tb_mata_pelajaran m ON j.id_mapel = m.id
    JOIN tb_guru g ON j.id_guru = g.id
    WHERE j.hari = ?
";
$stmtJadwal = $pdo->prepare($sqlJadwal);
$stmtJadwal->execute([$selected_hari]);
$raw_jadwal = $stmtJadwal->fetchAll();

$jadwal_matrix = [];
foreach ($raw_jadwal as $row) {
    $jadwal_matrix[$row['id_jam']][$row['id_kelas']] = $row;
}

// 4. Fetch All Mapel & Guru for Modal
$all_mapel = $pdo->query("SELECT * FROM tb_mata_pelajaran ORDER BY nama_mapel ASC")->fetchAll();
$all_guru = $pdo->query("SELECT * FROM tb_guru ORDER BY nama_lengkap ASC")->fetchAll();

// --- Header Profile Logic ---
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = $_SESSION['nama'] ?? 'Admin';
$nama_peran = 'Admin Global';
$initial = substr($admin_name, 0, 1);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manajemen Jadwal Pembelajaran</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        <?= $nama_peran ?>
                    </p>
                </div>
                <!-- Profile logic simplified -->
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <!-- Day Tabs -->
            <div class="mb-6 flex space-x-2 overflow-x-auto pb-2">
                <?php foreach($hari_list as $h): ?>
                    <a href="?hari=<?= $h ?>" 
                       class="px-6 py-2 rounded-lg text-sm font-bold transition whitespace-nowrap 
                       <?= $selected_hari == $h ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-500 hover:text-indigo-600 border border-gray-200' ?>">
                        <?= $h ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-bold text-gray-600 uppercase tracking-wider sticky left-0 z-10 w-32">
                                    Waktu
                                </th>
                                <th class="px-2 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-16">
                                    Ke-
                                </th>
                                <?php foreach($kelas_list as $kelas): ?>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-center text-xs font-bold text-gray-600 uppercase tracking-wider min-w-[120px]">
                                        <?= $kelas['nama_kelas'] ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($jam_pelajaran as $jam): ?>
                                <?php 
                                    // Treat jam_ke 0 (Mentoring) as a break row so it spans all columns and shows the label
                                    $is_break = ($jam['is_istirahat'] == 1) || ($jam['jam_ke'] == 0);
                                    $row_class = $is_break ? 'bg-orange-50' : 'bg-white hover:bg-gray-50';
                                ?>
                                <tr class="<?= $row_class ?> transition">
                                    <!-- Waktu Column -->
                                    <td class="px-4 py-3 border-b border-gray-200 text-sm font-mono text-gray-700 font-semibold sticky left-0 <?= $row_class ?> z-10 border-r cursor-pointer hover:bg-blue-50 relative group"
                                        onclick="openTimeModal('<?= $jam['id'] ?>', '<?= date('H:i', strtotime($jam['jam_mulai'])) ?>', '<?= date('H:i', strtotime($jam['jam_selesai'])) ?>')">
                                        
                                        <?= date('H:i', strtotime($jam['jam_mulai'])) ?> - <?= date('H:i', strtotime($jam['jam_selesai'])) ?>
                                        <?php if($jam['keterangan']): ?>
                                            <div class="text-xs text-orange-600 italic mt-1"><?= $jam['keterangan'] ?></div>
                                        <?php endif; ?>
                                        
                                        <div class="absolute inset-y-0 right-0 w-1 bg-indigo-400 opacity-0 group-hover:opacity-100 transition"></div>
                                        <div class="absolute top-0 right-0 -mt-2 -mr-2 bg-indigo-600 text-white text-[10px] px-1 rounded opacity-0 group-hover:opacity-100 transition">Edit</div>
                                    </td>
                                    
                                    <!-- Jam Ke Column -->
                                    <td class="px-2 py-3 border-b border-gray-200 text-sm text-center font-bold text-gray-500 border-r">
                                        <?= $jam['jam_ke'] == 99 || $jam['jam_ke'] == 98 ? '-' : $jam['jam_ke'] ?>
                                    </td>

                                    <!-- Classes Columns -->
                                    <?php if ($is_break): ?>
                                        <?php foreach($kelas_list as $kelas): ?>
                                            <td class="px-5 py-3 border-b border-gray-200 bg-orange-50 text-center"></td>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <?php foreach($kelas_list as $kelas): ?>
                                            <?php 
                                                $data = $jadwal_matrix[$jam['id']][$kelas['id']] ?? null; 
                                            ?>
                                            <td class="px-2 py-2 border-b border-gray-200 text-center border-r relative group cursor-pointer"
                                                onclick="openModal(<?= $jam['id'] ?>, <?= $kelas['id'] ?>, '<?= $data ? $data['id'] : '' ?>', '<?= $data ? $data['id_mapel'] : '' ?>', '<?= $data ? $data['id_guru'] : '' ?>')">
                                                
                                                <?php if($data): ?>
                                                    <div class="flex flex-col items-center justify-center h-full w-full">
                                                        <span class="text-xs font-bold text-gray-800 bg-gray-100 px-2 py-1 rounded mb-1 w-full truncate" title="<?= $data['nama_mapel'] ?>">
                                                            <?= $data['nama_mapel'] ?>
                                                        </span>
                                                        <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100" title="<?= $data['nama_lengkap'] ?>">
                                                            <?= $data['kode_guru'] ? $data['kode_guru'] : substr($data['nama_lengkap'], 0, 3) ?>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="h-10 w-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                        <span class="bg-indigo-100 text-indigo-600 text-xs px-2 py-1 rounded-full font-bold">+</span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                            </td>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Helper Info Code Guru -->
            <div class="mt-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Daftar Kode Guru</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php foreach($all_guru as $g): ?>
                        <div class="flex items-center space-x-2 text-sm">
                            <span class="bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded text-xs w-8 text-center shrink-0">
                                <?= $g['kode_guru'] ?? '?' ?>
                            </span>
                            <span class="text-gray-600 truncate" title="<?= $g['nama_lengkap'] ?>"><?= $g['nama_lengkap'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Modal Edit/Add Schedule -->
<div id="scheduleModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="save_jadwal.php" method="POST">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Atur Jadwal</h3>
                    
                    <input type="hidden" name="hari" value="<?= $selected_hari ?>">
                    <input type="hidden" name="id_jam" id="modal_id_jam">
                    <input type="hidden" name="id_kelas" id="modal_id_kelas">
                    <input type="hidden" name="id_jadwal" id="modal_id_jadwal"> <!-- If editing -->

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Mata Pelajaran</label>
                        <select name="id_mapel" id="modal_id_mapel" class="block w-full text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm border p-2" required>
                            <option value="">-- Pilih Mapel --</option>
                            <?php foreach($all_mapel as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= $m['nama_mapel'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Guru Pengampu</label>
                        <select name="id_guru" id="modal_id_guru" class="block w-full text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm border p-2" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach($all_guru as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= $g['nama_lengkap'] ?> <?= !empty($g['kode_guru']) ? "(" . $g['kode_guru'] . ")" : "" ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Option to Clear/Delete -->
                    <div class="flex items-center mt-4">
                        <input type="checkbox" name="delete_schedule" id="delete_schedule" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <label for="delete_schedule" class="ml-2 block text-sm text-gray-900">
                            Hapus Jadwal Ini (Kosongkan)
                        </label>
                    </div>

                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Time -->
<div id="timeModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeTimeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="save_time.php" method="POST">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Waktu Jam Pelajaran</h3>
                    
                    <input type="hidden" name="id_jam" id="time_id_jam">
                    <input type="hidden" name="hari_redirect" value="<?= $selected_hari ?>">

                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="time_jam_mulai" class="block w-full text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm border p-2" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="time_jam_selesai" class="block w-full text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm border p-2" required>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Waktu
                    </button>
                    <button type="button" onclick="closeTimeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(idJam, idKelas, idJadwal = '', idMapel = '', idGuru = '') {
        document.getElementById('scheduleModal').classList.remove('hidden');
        document.getElementById('modal_id_jam').value = idJam;
        document.getElementById('modal_id_kelas').value = idKelas;
        document.getElementById('modal_id_jadwal').value = idJadwal;
        
        document.getElementById('modal_id_mapel').value = idMapel;
        document.getElementById('modal_id_guru').value = idGuru;
        
        // Reset delete checkbox
        document.getElementById('delete_schedule').checked = false;
    }

    function closeModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
    }

    function openTimeModal(id, start, end) {
        document.getElementById('timeModal').classList.remove('hidden');
        document.getElementById('time_id_jam').value = id;
        document.getElementById('time_jam_mulai').value = start;
        document.getElementById('time_jam_selesai').value = end;
    }

    function closeTimeModal() {
        document.getElementById('timeModal').classList.add('hidden');
    }
</script>

<?php require_once '../../../layouts/footer.php'; ?>
