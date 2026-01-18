# Roadmap: Pengembangan Fitur Tab Pembayaran & SPP

Dokumen ini berisi panduan langkah demi langkah untuk mengubah halaman Pembayaran di aplikasi mobile menjadi sistem Tab yang mencakup: **Riwayat Transaksi**, **Tagihan SPP (Belum Lunas)**, dan **Tagihan SPP (Lunas)**.

---

## 1. Backend API (PHP)

Kita perlu membuat satu endpoint baru untuk mengambil data tagihan SPP siswa.

### 1.1 Buat File Baru: `app/api/payment/get_spp.php`
File ini bertugas mengambil data dari tabel `tb_tagihan_spp` berdasarkan `user_id` (siswa).

**Spesifikasi:**
*   **Method:** GET
*   **Parameter:** `user_id`
*   **Output JSON:**
    ```json
    {
      "success": true,
      "data": {
        "unpaid": [
          { "id": 1, "bulan": "Juli", "tahun": "2024", "jumlah": 150000, "status": "belum_lunas" }
        ],
        "paid": [
          { "id": 2, "bulan": "Juni", "tahun": "2024", "jumlah": 150000, "status": "lunas", "tanggal_bayar": "2024-06-10" }
        ]
      }
    }
    ```

---

## 2. Frontend Mobile (App.js)

Kita akan memodifikasi `App.js` untuk mengakomodasi navigasi Tab di dalam halaman Pembayaran.

### 2.1 State Management
Tambahkan state baru untuk mengelola data SPP dan posisi Tab aktif.

```javascript
// State Baru
const [paymentTab, setPaymentTab] = useState('transaksi'); // 'transaksi', 'tagihan', 'lunas'
const [sppData, setSppData] = useState({ unpaid: [], paid: [] });
const [isSppLoading, setIsSppLoading] = useState(false);
```

### 2.2 Fungsi `fetchSppData()`
Buat fungsi untuk memanggil API baru tadi.

```javascript
const fetchSppData = async () => {
    setIsSppLoading(true);
    try {
        const response = await fetch(`${BASE_URL}/app/api/payment/get_spp.php?user_id=${userData.user.id}`);
        const result = await response.json();
        if (result.success) {
            setSppData(result.data);
        }
    } catch (error) {
        console.error("Fetch SPP Error", error);
    } finally {
        setIsSppLoading(false);
    }
};
```

### 2.3 Fungsi Pembayaran SPP (`handlePaySpp`)
Modifikasi fungsi pembayaran agar bisa menangani SPP juga, tidak hanya Top Up.

```javascript
const handlePaySpp = async (tagihanId, amount, bulan) => {
    // Panggil snap_token.php dengan parameter tambahan:
    // type: 'spp'
    // target_id: tagihanId
};
```

### 2.4 UI Component: Tab Menu
Membuat UI Tab sederhana di atas list.

```javascript
// Contoh Layout
<View style={{ flexDirection: 'row', marginBottom: 16 }}>
    <TouchableOpacity onPress={() => setPaymentTab('transaksi')} ... >
        <Text>Riwayat</Text>
    </TouchableOpacity>
    <TouchableOpacity onPress={() => setPaymentTab('tagihan')} ... >
        <Text>Tagihan SPP</Text>
    </TouchableOpacity>
    <TouchableOpacity onPress={() => setPaymentTab('lunas')} ... >
        <Text>Lunas</Text>
    </TouchableOpacity>
</View>
```

---

## 3. Rencana Eksekusi

Berikut adalah urutan pengerjaan yang disarankan:

1.  **Backend:** Buat `app/api/payment/get_spp.php` dan tes di browser apakah datanya muncul.
2.  **App.js:** Tambahkan state variable dan fungsi `fetchSppData`.
3.  **App.js:** Buat tampilan Tab Menu di fungsi `renderPaymentView`.
4.  **App.js:** Implementasikan list rendering untuk masing-masing tab.
5.  **Testing:** Coba bayar salah satu tagihan SPP dan pastikan statusnya berubah menjadi lunas (pindah tab).
