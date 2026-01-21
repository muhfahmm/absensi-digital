# 🕒 Absensi Digital - Sistem Manajemen Kehadiran Modern

Sistem absensi digital terintegrasi yang menggabungkan aplikasi mobile, panel admin berbasis web, dan pemindai QR desktop untuk efisiensi tracking kehadiran siswa dan guru.

---

## 🚀 Fitur Utama

- **📱 Mobile App (React Native)**: Memungkinkan siswa dan guru untuk melihat profil, jadwal, dan melakukan absensi melalui QR Code.
- **🖥️ Desktop Scanner (Python)**: Aplikasi desktop khusus untuk memindai kartu pelajar/guru secara fisik menggunakan webcam atau scanner eksternal.
- **🌐 Admin Dashboard (PHP)**: Panel kendali pusat untuk manajemen data siswa, guru, kelas, mata pelajaran, dan laporan kehadiran.
- **📊 Real-time Monitoring**: Data kehadiran langsung tercatat di database dan dapat dipantau seketika.

---

## 🛠️ Stack Teknologi

- **Frontend Mobile**: React Native (Expo)
- **Backend/Web**: PHP, MySQL
- **Desktop Tools**: Python (OpenCV, Tkinter)
- **Styling**: Vanilla CSS, Modern Aesthetics

---

## 📁 Struktur Proyek

- `/app`: Sumber kode aplikasi web (Admin Panel)
- `/mobile-app`: Sumber kode aplikasi mobile (React Native)
- `/desktop-scanner`: Sumber kode pemindai kartu desktop (Python)
- `/database`: Berisi file SQL untuk inisialisasi database

---

## ⚙️ Cara Instalasi

1. **Database**: 
   - Import file `.sql` dari folder `/database` ke MySQL (XAMPP).
2. **Web Admin**: 
   - Letakkan folder proyek di `htdocs`.
   - Konfigurasi koneksi database di `app/config/koneksi.php`.
3. **Mobile App**:
   - Masuk ke folder `/mobile-app`.
   - Jalankan `npm install` lalu `npx expo start`.
4. **Desktop Scanner**:
   - Masuk ke folder `/desktop-scanner`.
   - Install requirement: `pip install opencv-python requests`.
   - Jalankan script python utama.

---

## 🎨 Design Philosophy
Proyek ini mengutamakan **Visual Excellence** dan **User Experience**. Menggunakan palet warna modern, micro-animations, dan interface yang intuitif di seluruh platform.

---
*Dikembangkan dengan ❤️ untuk digitalisasi pendidikan.*
