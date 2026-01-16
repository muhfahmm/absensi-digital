# Panduan Menjalankan Python Scanner

Scanner ini berjalan sebagai aplikasi Desktop yang membuka kamera Laptop secara langsung. Kecepatannya jauh lebih tinggi daripada scanner berbasis Web karena koneksi langsung ke hardware dan database.

## Cara Menjalankan

1. Pastikan Anda berada di folder `absensi-digital 3`
2. Jalankan perintah berikut di terminal:

```bash
python desktop-scanner/main.py
```

### Fitur
- 📷 **Instant Scan**: Membaca QR Code secara real-time.
- ⚡ **Direct DB**: Menulis langsung ke database MySQL (sangat cepat).
- 🔊 **Sound Feedback**: Bunyi Beep berbeda untuk Sukses (High Pitch) dan Gagal (Low Pitch).
- 🛑 **Anti-Spam**: Ada cooldown 2 detik agar tidak men-scan orang yang sama berkali-kali dalam waktu singkat.
- 📝 **Info Status**: Menampilkan nama siswa dan status (Hadir/Terlambat) langsung di layar.
