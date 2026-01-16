# Struktur Folder Proyek Absensi Digital

Berikut adalah rancangan struktur folder yang dimodifikasi agar lebih rapi, terorganisir, dan mudah dikelola (scalable) untuk pengembangan jangka panjang.

## 📂 Root Directory (`/`)

| Folder/File | Deskripsi |
| :--- | :--- |
| `config/` | Berisi file konfigurasi seperti koneksi database. |
| `database/` | Tempat menyimpan file migrasi atau skema SQL. |
| `functions/` | Kumpulan fungsi PHP murni (helper) untuk logika yang dipisahkan dari view. |
| `assets/` | Menyimpan file statis (CSS, JS, Gambar). |
| `layouts/` | Potongan kode UI yang berulang (Header, Sidebar, Footer) untuk dipanggil di setiap halaman. |
| `pages/` | Direktori utama yang menampung file-file halaman website, dikelompokkan berdasarkan Role. |
| `index.php` | Landing page utama / Halaman root aplikasi. |

---

## 🌳 Detail Struktur File

```text
absensi digital 3/
├── app/                     # Folder utama aplikasi
│   ├── config/
│   │   └── database.php     # Koneksi ke MySQL
│   │
│   ├── functions/
│   │   ├── auth.php         # Fungsi cek login
│   │   └── helpers.php      # Fungsi umum
│   │
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   │
│   ├── layouts/
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   │
│   └── pages/
│       ├── auth/
│       ├── admin/
│       ├── guru/
│       └── siswa/
│
├── database/                # Terpisah dari logika aplikasi
│   └── schema.sql
│
├── index.php                # Entry point (akan me-redirect ke app/pages/...)
├── roadmap.md
└── structure.md
```

## 📝 Catatan Penting
1. **Folder `app/`:** Semua logika, tampilan, dan aset dibungkus dalam folder ini agar root direktori lebih bersih.
2. **Pemisahan Halaman (Pages):** Semua halaman "View" ada di `app/pages/`.
3. **Layouts:** File partials ada di `app/layouts/`.
