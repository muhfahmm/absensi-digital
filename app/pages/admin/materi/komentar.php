<?php
// app/pages/admin/materi/komentar.php
session_start();
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../config/database.php';
require_once '../../../layouts/header.php';

check_login('admin');

$materi_id = $_GET['id'] ?? null;

if (!$materi_id) {
    echo "<script>alert('Materi tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// 1. Fetch Material Info
$stmt = $pdo->prepare("
    SELECT m.*, g.nama_lengkap as nama_guru, mp.nama_mapel, k.nama_kelas 
    FROM tb_materi m
    JOIN tb_guru g ON m.id_guru = g.id
    LEFT JOIN tb_mata_pelajaran mp ON m.id_mapel = mp.id
    LEFT JOIN tb_kelas k ON m.id_kelas = k.id
    WHERE m.id = ?
");
$stmt->execute([$materi_id]);
$materi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materi) {
    echo "<script>alert('Materi tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// 2. Fetch Comments
$sql = "
    SELECT k.*, 
        CASE 
            WHEN k.role = 'siswa' THEN s.nama_lengkap 
            WHEN k.role = 'guru' THEN g.nama_lengkap 
            WHEN k.role = 'admin' THEN a.nama_lengkap 
        END as nama_user,
        CASE 
            WHEN k.role = 'siswa' THEN s.foto_profil 
            WHEN k.role = 'guru' THEN g.foto_profil 
            WHEN k.role = 'admin' THEN a.foto_profil 
        END as foto_profil
    FROM tb_komentar_elearning k
    LEFT JOIN tb_siswa s ON k.user_id = s.id AND k.role = 'siswa'
    LEFT JOIN tb_guru g ON k.user_id = g.id AND k.role = 'guru'
    LEFT JOIN tb_admin a ON k.user_id = a.id AND k.role = 'admin'
    WHERE k.materi_id = ?
    ORDER BY k.created_at ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$materi_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to build tree
function buildTree(array $elements, $parentId = 0) {
    $branch = array();
    foreach ($elements as $element) {
        $elementParent = $element['parent_id'] ?? 0;
        if ($elementParent == $parentId) {
            $children = buildTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }
    return $branch;
}

$commentTree = buildTree($comments, 0);

function getProfileUrl($path, $role) {
    if (!$path) return 'https://ui-avatars.com/api/?name=User&background=random';
    // Check if full URL
    if (strpos($path, 'http') === 0) return $path;
    
    // Determine folder based on role
    // Folders in uploads: admin, guru, siswa, materi
    $folder = match($role) {
        'siswa' => 'siswa',
        'guru' => 'guru',
        'admin' => 'admin',
        default => 'profil' // Fallback
    };
    
    return base_url('uploads/' . $folder . '/' . $path);
}

function renderComments($comments, $depth = 0) {
    if (empty($comments)) return;
    
    foreach ($comments as $c) {
        $avatar = getProfileUrl($c['foto_profil'], $c['role']);
        $name = $c['nama_user'] ?? 'Unknown User';
        $roleValues = ['siswa' => 'Siswa', 'guru' => 'Guru', 'admin' => 'Admin'];
        $roleLabel = $roleValues[$c['role']] ?? ucfirst($c['role']);
        $roleColor = match($c['role']) { 'guru' => 'bg-indigo-100 text-indigo-700', 'admin' => 'bg-purple-100 text-purple-700', default => 'bg-gray-100 text-gray-700' };
        
        // Indentation for replies
        $marginLeft = $depth > 0 ? 'ml-12 border-l-2 border-gray-100 pl-4' : '';
        
        echo "<div class='mb-4 {$marginLeft} group'>";
            echo "<div class='flex items-start space-x-3'>";
                echo "<img src='{$avatar}' class='w-10 h-10 rounded-full object-cover border border-gray-200'>";
                echo "<div class='flex-1 bg-gray-50 rounded-2xl px-4 py-3'>";
                    echo "<div class='flex items-center justify-between mb-1'>";
                        echo "<div class='flex items-center space-x-2'>";
                            echo "<span class='font-bold text-gray-900 text-sm'>{$name}</span>";
                            echo "<span class='text-xs px-2 py-0.5 rounded-full {$roleColor} font-medium'>{$roleLabel}</span>";
                        echo "</div>";
                        echo "<span class='text-xs text-gray-400'>" . date('d M Y H:i', strtotime($c['created_at'])) . "</span>";
                    echo "</div>";
                    echo "<p class='text-gray-700 text-sm leading-relaxed'>" . nl2br(htmlspecialchars($c['komentar'])) . "</p>";
                echo "</div>";
            echo "</div>";
        echo "</div>";
        
        if (!empty($c['children'])) {
            renderComments($c['children'], $depth + 1);
        }
    }
}

?>

<div class="px-6 py-8 mx-auto max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Materi
        </a>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($materi['judul']) ?></h1>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <?= htmlspecialchars($materi['nama_guru']) ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <?= htmlspecialchars($materi['nama_mapel']) ?> (<?= htmlspecialchars($materi['nama_kelas']) ?>)
                        </span>
                    </div>
                </div>
                <div class="flex items-center">
                   <div class="text-center px-4">
                        <span class="block text-2xl font-bold text-indigo-600"><?= count($comments) ?></span>
                        <span class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Komentar</span>
                   </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Diskusi Kelas</h3>
        </div>
        
        <div class="p-6">
            <?php if (empty($commentTree)): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <p class="text-gray-500">Belum ada komentar untuk materi ini.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php renderComments($commentTree); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../../layouts/footer.php'; ?>
