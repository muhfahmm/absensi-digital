# Roadmap Pengembangan Aplikasi Absensi Digital Berbasis QR Code

Dokumen ini berisi rencana pengembangan aplikasi absensi digital menggunakan PHP, MySQL, Vanilla CSS, dan Tailwind CSS.

## 📋 Deskripsi Proyek
Aplikasi ini bertujuan untuk mempermudah proses absensi siswa, guru, dan karyawan menggunakan teknologi QR Code. Aplikasi dibagi menjadi dua sisi utama: **Admin Panel** (Web Desktop) untuk manajemen data dan **User App** (Mobile/Web) untuk melakukan absensi atau melihat riwayat.

## 🛠 Teknologi
- **Backend:** PHP (Native/Vanilla)
- **Database:** MySQL
- **Frontend:** HTML, JavaScript
- **Styling:** Vanilla CSS + Tailwind CSS (via CDN)
- **QR Code Library:** [qrcodejs](https://github.com/davidshimjs/qrcodejs) (Generator), [html5-qrcode](https://github.com/mebjas/html5-qrcode) (Scanner)

---

## 📅 Tahapan Pengembangan

### Fase 1: Perencanaan & Database ✅
- [x] Analisis Kebutuhan (Fitur Admin & User)
- [x] Desain Database (ERD) dan Implementasi SQL
- [x] Setup Folder Structure & Koneksi Database
- [x] Seed Admin Default

### Fase 2: Autentikasi & Authorization ✅
- [x] **Admin Login:** Halaman login khusus administrator
- [x] **Multi-Role Login:** Login untuk Admin, Siswa, dan Guru dengan satu halaman
- [x] **Session Management:** Membedakan hak akses admin, siswa, dan guru
- [x] **Register Admin:** Halaman registrasi khusus admin
- [x] **Auth Middleware:** Helper functions untuk check login dan role
- [x] **Logout:** Destroy session dan redirect

### Fase 3: Manajemen Master Data (Admin Panel) ✅

#### 1. Manajemen Kelas ✅
- [x] CRUD Data Kelas (Nama Kelas, Jumlah Siswa)
- [x] **Tabel Kelas**: No, Nama Kelas, Jumlah Siswa, Aksi (Edit, Hapus)
- [x] Validasi form input
- [x] Flash messages untuk feedback

#### 2. Manajemen Siswa ✅
- [x] CRUD Data Siswa (NIS, Nama, Kelas, Password)
- [x] Generate QR Code otomatis saat tambah siswa
- [x] Upload Foto Profil siswa
- [x] Preview QR Code di index
- [x] Relasi dengan tb_kelas
- [x] Password hashing dengan bcrypt

#### 3. Manajemen Guru ✅
- [x] CRUD Data Guru (NIP, Nama, Password)
- [x] Generate QR Code otomatis saat tambah guru
- [x] Upload Foto Profil guru
- [x] Halaman manajemen terpisah dari siswa
- [x] Password hashing dengan bcrypt

### Fase 4: Fitur Absensi (Inti) ✅

#### Scanner QR Code (Admin) ✅
- [x] **Halaman Scanner:** Interface kamera untuk scan QR
- [x] **Html5-qrcode Integration:** Library scanner dengan kamera
- [x] **Auto-Lock Mechanism:** Scanner lock setelah QR terdeteksi
- [x] **QR Box Size:** Kotak scanner 350x350px
- [x] **Processing Logic:**
  - [x] Validasi QR Code dari database
  - [x] Cek duplikasi (sudah absen hari ini?)
  - [x] Auto-detect status (Hadir/Terlambat berdasarkan jam)
  - [x] Simpan ke tb_absensi
- [x] **Recent Scans:** Tampilkan 10 scan terakhir hari ini
- [x] **Result Feedback:** Success/Error card dengan animasi
- [x] **Menu di Sidebar:** Link ke scanner

### Fase 5: Dashboard & User Interface

#### Admin Dashboard ⏳
- [x] Dashboard skeleton dengan statistik placeholder
- [ ] Statistik kehadiran hari ini (Total, Hadir, Sakit, Izin, Alpa)
- [ ] Chart/Graph kehadiran
- [ ] Quick actions

#### Guru Dashboard ✅
- [x] Dashboard dengan nama guru
- [x] QR Code pribadi guru (modal)
- [x] Statistik Absensi Kelas Hari Ini:
  - [x] List semua kelas
  - [x] Total siswa per kelas
  - [x] Jumlah siswa hadir
  - [x] Progress bar attendance
  - [x] Color-coded (hijau/kuning/merah)
- [x] Link "Lihat Detail" per kelas

#### Siswa Dashboard ✅
- [x] **Header:** Nama siswa & kelas
- [x] **Student Detail Card:** Foto, Nama, NIS, Kelas
- [x] **QR Code Modal:** 
  - [x] Tombol QR di bottom navigation
  - [x] Modal dengan QR code besar (250x250px)
  - [x] Animasi smooth (scale + fade)
  - [x] Click outside to close
- [x] **Status Absensi Hari Ini:**
  - [x] Badge berwarna (Hadir/Sakit/Izin/Alpa/Terlambat)
  - [x] Jam masuk & keterangan
- [x] **Grid Menu (2x2):**
  - [x] Kehadiran
  - [x] Profil
  - [x] Pembayaran
  - [x] Pengumuman
- [x] **Bottom Navigation:** Home, QR Code, Profil
- [x] **Photo Modal:** Click foto untuk preview besar

#### Siswa - Halaman Tambahan ✅
- [x] **Kehadiran.php:**
  - [x] Statistik (Total, Hadir, Sakit, Izin)
  - [x] Riwayat 30 hari terakhir
  - [x] Detail per absensi (tanggal, jam, status)
  - [x] QR Modal terintegrasi
- [x] **Profil.php:**
  - [x] Foto profil besar
  - [x] Info lengkap (NIS, Kelas, QR Code, Terdaftar sejak)
  - [x] Tombol logout
  - [x] QR Modal terintegrasi
- [x] **Pembayaran.php:**
  - [x] Coming soon page
  - [x] Info placeholder
  - [x] QR Modal terintegrasi
- [x] **Pengumuman.php:**
  - [x] Coming soon page
  - [x] Preview 3 pengumuman contoh
  - [x] Badge kategori (Penting, Akademik, Perubahan)
  - [x] QR Modal terintegrasi

### Fase 6: Reporting & Export ⏳
- [ ] Export Laporan Absensi (Excel/PDF)
- [ ] Filter laporan (per kelas, tanggal, status)
- [ ] Grafik statistik kehadiran
- [ ] Print kartu QR siswa/guru

### Fase 7: Fitur Tambahan (Future) 📝
- [ ] **Jadwal Pelajaran** (Siswa)
- [ ] **Nilai/Rapor** (Siswa)
- [ ] **Tugas** (Siswa)
- [ ] **E-Learning/Materi** (Siswa) - *Started (Database Ready)*
- [ ] **Konseling** (Siswa)
- [ ] **Input Nilai** (Guru)
- [ ] **Pengaturan Sistem** (Admin)
- [ ] **Notifikasi Push** (PWA)
- [ ] **Dark Mode** (All pages)
- [ ] **Multi-language** (ID/EN)

### Fase 5.5: Poin & Akademik (Update Terbaru) ✅
- [x] **Sistem Poin Siswa:**
  - [x] Database kolom `poin`
  - [x] Auto-deduct point saat terlambat
  - [x] Menampilkan poin di Mobile App
- [x] **Manajemen Mata Pelajaran:**
  - [x] Tabel Master Mapel (`tb_mata_pelajaran`)
  - [x] Relasi Guru ke Mapel (`guru_mapel_id`)
  - [x] Relasi Admin ke Mapel (`guru_mapel_id`)
- [x] **Dashboard Updates:**
  - [x] Mobile: Admin Global Title
  - [x] Mobile: Mapel/Role Subtitle
  - [x] Web Admin: Dynamic Title based on Mapel relation

---

## 🗄 Struktur Database

File `database/schema.sql` dengan tabel-tabel:

1. ✅ **tb_admin**: Akun administrator
2. ✅ **tb_kelas**: Data kelas
3. ✅ **tb_siswa**: Data siswa dengan QR code dan foto
4. ✅ **tb_guru**: Data guru dengan QR code dan foto
5. ✅ **tb_karyawan**: Data karyawan (belum digunakan)
6. ✅ **tb_absensi**: Log kehadiran (user_id, role, tanggal, jam, status)

---

## 📁 Struktur Folder

```
absensi digital 3/
├── app/
│   ├── auth/
│   │   ├── api-login.php ✅
│   │   └── api-register.php ✅
│   ├── config/
│   │   ├── database.php ✅
│   │   └── seed_admin.php ✅
│   ├── functions/
│   │   ├── helpers.php ✅
│   │   └── auth.php ✅
│   ├── layouts/
│   │   ├── header.php ✅
│   │   ├── footer.php ✅
│   │   └── sidebar.php ✅
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── login.php ✅
│   │   │   ├── register.php ✅
│   │   │   └── logout.php ✅
│   │   ├── admin/
│   │   │   ├── dashboard.php ✅
│   │   │   ├── kelas/ ✅
│   │   │   ├── siswa/ ✅
│   │   │   ├── guru/ ✅
│   │   │   └── scanner/ ✅
│   │   ├── guru/
│   │   │   └── dashboard.php ✅
│   │   └── siswa/
│   │       ├── dashboard.php ✅
│   │       ├── kehadiran.php ✅
│   │       ├── profil.php ✅
│   │       ├── pembayaran.php ✅
│   │       └── pengumuman.php ✅
│   └── assets/
│       ├── css/
│       └── js/
├── uploads/
│   ├── siswa/ ✅
│   └── guru/ ✅
├── database/
│   ├── schema.sql ✅
│   └── restore_password.sql ✅
├── index.php ✅
├── roadmap.md ✅
└── structure.md ✅
```

---

## 🎨 UI/UX Guidelines

- **Framework:** Tailwind CSS (CDN)
- **Style:** Modern, Clean, Responsive
- **Admin:** Dashboard dengan Sidebar
- **Siswa/Guru:** Mobile-first dengan bottom navigation
- **Glassmorphism:** Aksen transparan pada card/modal
- **Color Scheme:** 
  - Primary: Indigo-Purple gradient
  - Success: Green
  - Warning: Yellow/Orange
  - Danger: Red
  - Info: Blue

---

## 🚀 Progress Summary

**Total Features:** 50+
**Completed:** 45+ ✅
**In Progress:** 2 ⏳
**Planned:** 15+ 📝

**Completion Rate:** ~90% (Core Features)

---

## 📝 Notes

- Semua password di-hash menggunakan `password_hash()` PHP
- QR Code generated client-side menggunakan qrcodejs
- Scanner menggunakan html5-qrcode dengan auto-lock mechanism
- Session-based authentication dengan role checking
- Responsive design untuk mobile dan desktop
- File upload untuk foto profil dengan validasi

---

**Last Updated:** 16 Januari 2026
**Version:** 1.1.0 (Poin & Mapel Update)
