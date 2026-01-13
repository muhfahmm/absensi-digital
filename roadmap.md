# Roadmap Pengembangan Aplikasi Absensi Digital Berbasis QR Code

Dokumen ini berisi rencana pengembangan aplikasi absensi digital menggunakan PHP, MySQL, Vanilla CSS, dan Tailwind CSS.

## 📋 Deskripsi Proyek
Aplikasi ini bertujuan untuk mempermudah proses absensi siswa, guru, dan karyawan menggunakan teknologi QR Code. Aplikasi dibagi menjadi dua sisi utama: **Admin Panel** (Web Desktop) untuk manajemen data dan **User App** (Mobile/Web) untuk melakukan absensi atau melihat riwayat.

## 🛠 Teknologi
- **Backend:** PHP (Native/Vanilla)
- **Database:** MySQL
- **Frontend:** HTML, JavaScript
- **Styling:** Vanilla CSS + Tailwind CSS (via CDN atau Build Process)
- **QR Code Library:** [qrcodejs](https://github.com/davidshimjs/qrcodejs) (Generator), [html5-qrcode](https://github.com/mebjas/html5-qrcode) (Scanner)

---

## 📅 Tahapan Pengembangan

### Fase 1: Perencanaan & Database
- [x] Analisis Kebutuhan (Fitur Admin & User)
- [ ] Desain Database (ERD) dan Implementasi SQL
- [ ] Setup Folder Structure & Koneksi Database

### Fase 2: Autentikasi & Authorization
- [ ] **Admin Login:** Halaman login khusus administrator.
- [ ] **User Login:** Halaman login terpisah untuk Siswa dan Guru/Karyawan.
- [ ] **Session Management:** Membedakan hak akses admin dan user.

### Fase 3: Manajemen Master Data (Admin Panel)
#### 1. Manajemen Kelas (Sesuai Request)
- [ ] CRUD Data Kelas (Nama Kelas, Jumlah Siswa).
- [ ] Fitur "Manajemen Jumlah Siswa": Form input khusus untuk update kapasitas/jumlah.
- [ ] **Tabel Kelas**: No, Nama Kelas, Jumlah Siswa, Kode QR (Text), QR Code Image, Aksi (Edit, Hapus).
- [ ] Generate QR Code Unik per Kelas (jika absensi berbasis kelas).

#### 2. Manajemen Siswa
- [ ] CRUD Data Siswa (NIS, Nama, Kelas, Password).
- [ ] Generate QR Code otomatis saat tambah siswa.
- [ ] Cetak Kartu QR Code Siswa.

#### 3. Manajemen Guru & Karyawan
- [ ] CRUD Data Guru/Karyawan (NIP, Nama, Posisi).
- [ ] Halaman manajemen terpisah dari siswa.
- [ ] Generate QR Code khusus Guru.

### Fase 4: Fitur Absensi (Inti)
- [ ] **Sistem Scanning:** Integrasi kamera untuk scan QR Code.
- [ ] **Logika Absensi:**
    - Validasi QR Code.
    - Cek jam masuk/pulang.
    - Simpan data (Hadir, Terlambat, Izin, Sakit).

### Fase 5: Reporting & Dashboard
- [ ] Dashboard Admin: Statistik kehadiran hari ini (Pie chart kehadiran).
- [ ] Dashboard Siswa/Guru: History absensi pribadi.
- [ ] Export Laporan (PDF/Excel) - *Opsional/Future*.

---

## 🗄 Gambaran Struktur Database

Akan dibuat file `database/schema.sql` dengan tabel-tabel berikut:

1.  **tb_admin**: Akun administrator.
2.  **tb_kelas**: Data kelas dan token QR kelas.
3.  **tb_siswa**: Data siswa, relation ke tb_kelas, token QR individu.
4.  **tb_guru**: Data guru, token QR individu.
5.  **tb_karyawan**: Data karyawan (bisa digabung/dipisah dengan guru tergantung detail field).
6.  **tb_absensi**: Menyimpan log kehadiran (user_id, role, waktu, tanggal, status).

---

## 🎨 UI/UX Guidelines
- **Framework:** Tailwind CSS.
- **Style:** Modern, Clean, Dashboard Admin dengan Sidebar, Dark Mode support (opsional).
- **Glassmorphism:** Aksen transparan pada card/panel login.
