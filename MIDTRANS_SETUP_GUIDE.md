# Panduan Konfigurasi URL Midtrans

Dokumen ini menjelaskan pengaturan URL yang perlu Anda masukkan di Dashboard Midtrans (Sandbox maupun Production).

## 1. Finish Redirect URL (Halaman Selesai)

**Apakah wajib diisi di Dashboard?**
**TIDAK WAJIB**, tetapi **DIREKOMENDASIKAN** sebagai cadangan (fallback).

### Penjelasan:
Aplikasi kita saat ini sudah secara otomatis mengirimkan alamat "Payment Finish" setiap kali membuat transaksi. Jadi, Midtrans akan mengikuti instruksi dari kode aplikasi (`snap_token.php`) daripada pengaturan di Dashboard.

Namun, jika Anda ingin mengisinya sebagai cadangan (misalnya jika kode gagal mengirim parameter), Anda bisa mengisinya.

### Cara Mengisi (Jika ingin):
- **Development (Ngrok):**
  Setiap kali Ngrok restart, URL akan berubah. Jadi ini agak merepotkan untuk diisi manual berulang kali. Lebih baik **KOSONGKAN SAJA** dan biarkan kode aplikasi yang menanganinya.
  
- **Production (Domain Asli):**
  Jika nanti sudah online dengan domain sendiri (misal: `sekolahku.com`), isi dengan:
  `https://sekolahku.com/absensi-digital/app/api/payment/payment_finish.php`

---

## 2. Notification URL (Webhook) - **WAJIB**

Ini adalah bagian yang **SANGAT PENTING**. Midtrans mengirim laporan status (Berhasil/Gagal) ke alamat ini. Jika salah, saldo siswa tidak akan masuk otomatis.

### Cara Mengisi:
1.  Buka [Midtrans Dashboard](https://dashboard.midtrans.com).
2.  Masuk ke **Settings > Configuration**.
3.  Cari kolom **Notification URL**.
4.  Isi dengan alamat berikut:

    **Untuk Development (Ngrok):**
    `https://[ID-NGROK-ANDA].ngrok-free.app/absensi-digital/app/api/payment/notification.php`
    *(Ingat: Update ini setiap kali Anda restart Ngrok)*

    **Untuk Production:**
    `https://[DOMAIN-ANDA]/absensi-digital/app/api/payment/notification.php`

---

## Ringkasan

| Setting | Status | Keterangan |
| :--- | :--- | :--- |
| **Finish Redirect URL** | **Opsional** | Aplikasi sudah menanganinya secara otomatis (Dynamic). Boleh dikosongkan. |
| **Notification URL** | **WAJIB** | Harus diisi agar saldo masuk otomatis. |

## Tips Tambahan: Recurring / Subscription
Jika nanti Anda menggunakan fitur **Subscription** (Langganan Berulang), kolom **Recurrency Notification URL** juga wajib diisi sama persis dengan Notification URL di atas.
