<?php
// app/pages/admin/keuangan/payment_settings.php
require_once '../../../functions/helpers.php';
require_once '../../../functions/auth.php';
require_once '../../../layouts/header.php';
require_once '../../../config/database.php';

// Proteksi halaman admin
check_login('admin');

$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$initial = substr($admin_name, 0, 1);

// Handle Save
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_production = isset($_POST['is_production']) ? 1 : 0;
    $server_key = $_POST['server_key'] ?? '';
    $client_key = $_POST['client_key'] ?? '';
    $merchant_id = $_POST['merchant_id'] ?? '';

    try {
        // Cek existing
        $stmt = $pdo->prepare("SELECT id FROM tb_pengaturan_pembayaran WHERE provider = 'midtrans'");
        $stmt->execute();
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE tb_pengaturan_pembayaran SET is_active = ?, is_production = ?, server_key = ?, client_key = ?, merchant_id = ? WHERE provider = 'midtrans'");
            $stmt->execute([$is_active, $is_production, $server_key, $client_key, $merchant_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO tb_pengaturan_pembayaran (provider, is_active, is_production, server_key, client_key, merchant_id) VALUES ('midtrans', ?, ?, ?, ?, ?)");
            $stmt->execute([$is_active, $is_production, $server_key, $client_key, $merchant_id]);
        }
        $message = "Pengaturan berhasil disimpan!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Gagal menyimpan: " . $e->getMessage();
        $messageType = "error";
    }
}

// Fetch Current Settings
$stmt = $pdo->prepare("SELECT * FROM tb_pengaturan_pembayaran WHERE provider = 'midtrans'");
$stmt->execute();
$midtrans = $stmt->fetch(PDO::FETCH_ASSOC);

// Default values if empty
if (!$midtrans) {
    $midtrans = [
        'is_active' => 0,
        'is_production' => 0,
        'server_key' => '',
        'client_key' => '',
        'merchant_id' => ''
    ];
}

$callback_url = base_url('app/api/payment/notification.php');
if (strpos($callback_url, 'localhost') !== false) {
    $callback_url_note = "Warning: Localhost URL tidak bisa diakses Midtrans. Gunakan Ngrok/Cloudflare untuk testing.";
} else {
    $callback_url_note = "";
}

?>

<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include '../../../layouts/sidebar.php'; ?>
    
    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Pengaturan Payment Gateway</h2>
             <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800"><?= $admin_name ?></p>
                     <p class="text-xs text-indigo-500 font-medium bg-indigo-50 px-2 py-0.5 rounded-full inline-block mt-1">Admin Global</p>
                </div>
                 <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center font-bold shadow-md border-2 border-white"><?= $initial ?></div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <?php if ($message): ?>
                <div class="mb-4 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="max-w-4xl mx-auto">
                <!-- Gateway Status -->
                <div class="bg-indigo-600 rounded-xl shadow-lg p-6 mb-8 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold">Midtrans Payment</h3>
                            <p class="text-indigo-200 text-sm mt-1">Status: <?= $midtrans['is_active'] ? '<span class="px-2 py-0.5 bg-green-400 text-green-900 rounded-full text-xs font-bold">Active</span>' : '<span class="px-2 py-0.5 bg-red-400 text-red-900 rounded-full text-xs font-bold">Inactive</span>' ?></p>
                        </div>
                        <svg class="w-12 h-12 text-indigo-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                </div>

                <form method="POST" action="">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <div class="flex items-center">
                                <img src="https://docs.midtrans.com/asset/image/midtrans-logo-dark.png" alt="Midtrans" class="h-8 mr-3">
                                <span class="font-bold text-gray-700">Konfigurasi</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label for="is_active" class="flex items-center cursor-pointer relative">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" class="sr-only" <?= $midtrans['is_active'] ? 'checked' : '' ?>>
                                    <div class="toggle-bg bg-gray-200 border-2 border-gray-200 h-6 w-11 rounded-full"></div>
                                    <span class="ml-3 text-gray-700 text-sm font-medium">Aktifkan</span>
                                </label>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">
                            
                            <!-- Production Mode Toggle -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div>
                                    <label class="font-semibold text-gray-700 block">Production Mode</label>
                                    <p class="text-xs text-gray-500">Aktifkan untuk transaksi real (uang asli). Matikan untuk Sandbox (Testing).</p>
                                </div>
                                <label for="is_production" class="flex items-center cursor-pointer relative">
                                    <input type="checkbox" id="is_production" name="is_production" value="1" class="sr-only" <?= $midtrans['is_production'] ? 'checked' : '' ?>>
                                    <div class="toggle-bg bg-gray-200 border-2 border-gray-200 h-6 w-11 rounded-full"></div>
                                </label>
                            </div>

                            <!-- Credentials -->
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Non-Server Key (Server Key)</label>
                                    <input type="text" name="server_key" value="<?= htmlspecialchars($midtrans['server_key']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition" placeholder="Mid-server-..." required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Client Key</label>
                                    <input type="text" name="client_key" value="<?= htmlspecialchars($midtrans['client_key']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition" placeholder="Mid-client-..." required>
                                </div>
                                 <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Merchant ID</label>
                                    <input type="text" name="merchant_id" value="<?= htmlspecialchars($midtrans['merchant_id']) ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition" placeholder="G..." required>
                                </div>
                            </div>

                            <!-- Callback URL Info -->
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <label class="block text-sm font-bold text-blue-800 mb-2">Callback / Notification URL</label>
                                <div class="flex">
                                    <input type="text" readonly value="<?= $callback_url ?>" class="flex-1 bg-white px-3 py-2 rounded-l-lg border border-blue-200 text-gray-600 text-sm select-all">
                                    <button type="button" onclick="navigator.clipboard.writeText('<?= $callback_url ?>')" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700 font-medium text-sm">Copy</button>
                                </div>
                                <p class="text-xs text-blue-600 mt-2">
                                    Paste URL ini di Dashboard Midtrans > Settings > Configuration > Notification URL.
                                    <?= $callback_url_note ? '<br><span class="text-red-500 font-bold">'.$callback_url_note.'</span>' : '' ?>
                                </p>
                            </div>

                        </div>

                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-bold shadow-md transform active:scale-95 transition">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>

<style>
/* Custom Toggle Switch CSS */
.toggle-bg { transition: background-color 0.2s ease-in-out; }
input:checked ~ .toggle-bg { background-color: #4f46e5; border-color: #4f46e5; }
input:checked ~ .toggle-bg:after { transform: translateX(100%); background-color: white; }
.toggle-bg:after {
    content: ''; position: absolute; top: 2px; left: 2px; bottom: 2px; width: 1.25rem; background-color: white; border-radius: 9999px; transition: transform 0.2s ease-in-out; box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
</style>

<?php require_once '../../../layouts/footer.php'; ?>
