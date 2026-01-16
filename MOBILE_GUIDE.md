# Panduan Installasi Mobile App (React Native)

Aplikasi mobile ini dibuat menggunakan **React Native (Expo)** untuk performa native yang lebih cepat dan akses kamera yang stabil.

## 1. Persiapan
Pastikan HP dan Laptop terhubung ke **WiFi yang SAMA**.

## 2. Setting IP Address
File `mobile-app/App.js` sudah disetting ke IP:
`const BASE_URL = 'http://192.168.0.103/absensi-digital%203';`

Jika IP berubah:
1. Buka CMD, ketik `ipconfig`
2. Cari IPv4 Address pada Wireless LAN adapter Wi-Fi
3. Update `BASE_URL` di `mobile-app/App.js`

## 3. Menjalankan Aplikasi
1. Buka terminal di folder `mobile-app`:
   ```bash
   cd mobile-app
   npx expo start
   ```
2. Scan QR Code yang muncul di terminal menggunakan aplikasi **Expo Go** (Android/iOS).

## 4. Fitur
1. **Login Native**: Menggunakan API ke backend PHP.
2. **Dashboard Siswa**: Menampilkan QR Code siswa untuk discan.
3. **Scanner Admin**: Menggunakan Native Camera untuk scan QR siswa (Lebih cepat & akurat dibanding web).

## Catatan
Jika gagal connect:
- Matikan Firewall Windows (Private Network) untuk sementara.
- Pastikan XAMPP Apache berjalan.
