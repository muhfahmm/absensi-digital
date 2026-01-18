<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Selesai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-lg max-w-sm w-full text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
        <p class="text-gray-500 mb-8">Terima kasih, pembayaran Anda telah kami terima. Saldo Anda akan segera diperbarui.</p>
        
        <div class="space-y-3">
            <p class="text-xs text-gray-400">Order ID: <span id="orderId" class="font-mono text-gray-600">...</span></p>
        </div>

        <div class="mt-8">
            <a href="javascript:void(0)" onclick="closePayment()" class="block w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-xl hover:bg-indigo-700 transition duration-200">
                Kembali ke Aplikasi
            </a>
        </div>
    </div>

    <script>
        // Ambil Order ID dari URL param order_id atau id
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('order_id') || urlParams.get('id');
        if(orderId) {
            document.getElementById('orderId').innerText = orderId;
        }

        function closePayment() {
            // Coba kirim pesan ke React Native WebView
            if (window.ReactNativeWebView) {
                window.ReactNativeWebView.postMessage("PAYMENT_SUCCESS");
            } else {
                // Fallback jika dibuka di browser biasa
                window.close();
            }
        }
    </script>
</body>
</html>
