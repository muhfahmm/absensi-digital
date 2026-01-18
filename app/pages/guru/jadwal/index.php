<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../functions/helpers.php';
require_once __DIR__ . '/../../../functions/auth.php';

check_login('guru');
$guru_id = $_SESSION['guru_id'];

// Fetch Jadwal
$stmt = $pdo->prepare("SELECT j.*, mp.nama_mapel, k.nama_kelas 
                       FROM tb_jadwal j 
                       JOIN tb_mata_pelajaran mp ON j.id_mapel = mp.id 
                       JOIN tb_kelas k ON j.id_kelas = k.id 
                       WHERE j.id_guru = ? 
                       ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC");
$stmt->execute([$guru_id]);
$jadwal_list = $stmt->fetchAll();

// Fetch Roles for Guru
$stmtPeran = $pdo->prepare("SELECT m.nama_mapel, k.nama_kelas 
                            FROM tb_guru g 
                            LEFT JOIN tb_mata_pelajaran m ON g.guru_mapel_id = m.id 
                            LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
                            WHERE g.id = ?");
$stmtPeran->execute([$guru_id]);
$peran = $stmtPeran->fetch();

$roles = [];
if (!empty($peran['nama_mapel'])) $roles[] = "Guru " . $peran['nama_mapel'];
if (!empty($peran['nama_kelas'])) $roles[] = "Wali Kelas " . $peran['nama_kelas'];
$nama_peran = empty($roles) ? "Guru Pengajar" : implode(" & ", $roles);
?>

<!DOCTYPE html>
<html lang="id">
<!-- Using Global Standard Pattern -->
<?php require_once __DIR__ . '/../../../layouts/header.php'; ?>

<div class="flex h-screen bg-gray-50">
    <?php include __DIR__ . '/../../../layouts/sidebar_guru.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
            <h2 class="text-xl font-semibold text-gray-800">Jadwal Mengajar</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($_SESSION['guru_nama']) ?></p>
                    <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <?= htmlspecialchars($nama_peran) ?>
                    </p>
                </div>
                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= substr($_SESSION['guru_nama'], 0, 1) ?></div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                // Group by Day
                $jadwal_by_day = [];
                foreach($jadwal_list as $j) {
                    $jadwal_by_day[$j['hari']][] = $j;
                }

                foreach($days as $day): 
                    $items = $jadwal_by_day[$day] ?? [];
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-3">
                        <h3 class="text-white font-bold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <?= $day ?>
                        </h3>
                    </div>
                    <div class="p-4 space-y-3 mr-2">
                        <?php if(empty($items)): ?>
                            <p class="text-gray-400 text-sm italic text-center py-4">Tidak ada jadwal</p>
                        <?php else: ?>
                            <?php foreach($items as $item): ?>
                            <div class="flex items-start space-x-3 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                                <div class="bg-indigo-50 text-indigo-600 rounded-lg p-2 text-xs font-bold whitespace-nowrap">
                                    <?= date('H:i', strtotime($item['jam_mulai'])) ?>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800 leading-tight"><?= htmlspecialchars($item['nama_mapel']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($item['nama_kelas']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
