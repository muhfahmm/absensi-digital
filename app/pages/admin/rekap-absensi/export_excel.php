<?php
// app/pages/admin/rekap-absensi/export_excel.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';

check_login('admin');

// Get parameters
$filter_tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$filter_tab = $_GET['tab'] ?? 'siswa'; // siswa or guru
$filter_kelas_siswa = $_GET['kelas_siswa'] ?? '';

// Initialize variables
$all_siswa = [];
$all_guru = [];
$selected_kelas_name = '';

if ($filter_tab === 'siswa') {
    // Get students for selected class
    $sql_siswa = "SELECT s.*, k.nama_kelas 
                  FROM tb_siswa s 
                  LEFT JOIN tb_kelas k ON s.id_kelas = k.id 
                  WHERE s.id_kelas = :kelas_id
                  ORDER BY s.nama_lengkap ASC";
    $stmt_siswa = $pdo->prepare($sql_siswa);
    $stmt_siswa->execute([':kelas_id' => $filter_kelas_siswa]);
    $all_siswa = $stmt_siswa->fetchAll();
    
    // Get class name
    if ($filter_kelas_siswa) {
        $stmt_kelas = $pdo->prepare("SELECT nama_kelas FROM tb_kelas WHERE id = ?");
        $stmt_kelas->execute([$filter_kelas_siswa]);
        $kelas = $stmt_kelas->fetch();
        $selected_kelas_name = $kelas['nama_kelas'] ?? 'Unknown';
    }
} else {
    // Get all teachers
    $sql_guru = "SELECT g.*, k.nama_kelas 
                 FROM tb_guru g 
                 LEFT JOIN tb_kelas k ON g.id_kelas_wali = k.id 
                 ORDER BY g.nama_lengkap ASC";
    $stmt_guru = $pdo->query($sql_guru);
    $all_guru = $stmt_guru->fetchAll();
}

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

// Filename
if ($filter_tab === 'siswa') {
    $filename = "Rekap_Siswa_" . str_replace(' ', '_', $selected_kelas_name) . "_" . date('d-m-Y', strtotime($filter_tanggal)) . ".xls";
} else {
    $filename = "Rekap_Guru_" . date('d-m-Y', strtotime($filter_tanggal)) . ".xls";
}

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4F46E5;
            color: white;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .kelas-header {
            background-color: #3B82F6;
            color: white;
            font-weight: bold;
            padding: 10px;
        }
        .guru-header {
            background-color: #A855F7;
            color: white;
            font-weight: bold;
            padding: 10px;
        }
        .hadir {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .belum {
            background-color: #FEE2E2;
            color: #991B1B;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>REKAP KEHADIRAN HARIAN</h2>
        <?php if ($filter_tab === 'siswa'): ?>
            <h3>SISWA - <?= htmlspecialchars($selected_kelas_name) ?></h3>
        <?php else: ?>
            <h3>GURU</h3>
        <?php endif; ?>
        <p>Tanggal: <?= date('d/m/Y', strtotime($filter_tanggal)) ?></p>
        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <?php if ($filter_tab === 'siswa'): ?>
        <!-- SISWA TABLE -->
        <table>
            <thead>
                <tr>
                    <th colspan="6" class="kelas-header">
                        <?= htmlspecialchars($selected_kelas_name) ?> - 
                        <?= count(array_filter($all_siswa, fn($s) => isset($absensi_siswa[$s['id']]))) ?> / <?= count($all_siswa) ?> Hadir
                    </th>
                </tr>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($all_siswa) > 0): ?>
                    <?php foreach ($all_siswa as $idx => $siswa): ?>
                        <?php $abs = $absensi_siswa[$siswa['id']] ?? null; ?>
                        <tr class="<?= $abs ? 'hadir' : 'belum' ?>">
                            <td><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($siswa['nis']) ?></td>
                            <td><?= htmlspecialchars($siswa['nama_lengkap']) ?></td>
                            <td style="text-align: center;"><?= $abs ? date('H:i', strtotime($abs['jam_masuk'])) : '-' ?></td>
                            <td style="text-align: center;"><?= $abs && $abs['jam_keluar'] ? date('H:i', strtotime($abs['jam_keluar'])) : '-' ?></td>
                            <td style="text-align: center;"><?= $abs ? ucfirst($abs['status']) : 'Belum Absen' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Tidak ada data siswa</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- GURU TABLE -->
        <table>
            <thead>
                <tr>
                    <th colspan="7" class="guru-header">
                        Daftar Guru - 
                        <?= count(array_filter($all_guru, fn($g) => isset($absensi_guru[$g['id']]))) ?> / <?= count($all_guru) ?> Hadir
                    </th>
                </tr>
                <tr>
                    <th>No</th>
                    <th>NUPTK</th>
                    <th>Nama</th>
                    <th>Wali Kelas</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($all_guru) > 0): ?>
                    <?php foreach ($all_guru as $idx => $guru): ?>
                        <?php $abs = $absensi_guru[$guru['id']] ?? null; ?>
                        <tr class="<?= $abs ? 'hadir' : 'belum' ?>">
                            <td><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($guru['nuptk']) ?></td>
                            <td><?= htmlspecialchars($guru['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($guru['nama_kelas'] ?? '-') ?></td>
                            <td style="text-align: center;"><?= $abs ? date('H:i', strtotime($abs['jam_masuk'])) : '-' ?></td>
                            <td style="text-align: center;"><?= $abs && $abs['jam_keluar'] ? date('H:i', strtotime($abs['jam_keluar'])) : '-' ?></td>
                            <td style="text-align: center;"><?= $abs ? ucfirst($abs['status']) : 'Belum Absen' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Tidak ada data guru</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br><br>

    <!-- Footer -->
    <p style="font-size: 12px; color: #666;">
        <strong>Keterangan:</strong><br>
        - Data ini dihasilkan secara otomatis dari sistem Absensi Digital<br>
        - Tanggal: <?= date('d/m/Y', strtotime($filter_tanggal)) ?><br>
        <?php if ($filter_tab === 'siswa'): ?>
        - Kelas: <?= htmlspecialchars($selected_kelas_name) ?><br>
        <?php endif; ?>
        - Hijau: Sudah Absen | Merah: Belum Absen
    </p>
</body>
</html>
