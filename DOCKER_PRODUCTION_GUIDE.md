# Panduan Deployment Production dengan Docker

Dokumen ini menjelaskan cara men-deploy aplikasi **Absensi Digital** ke server production menggunakan Docker. Panduan ini dirancang khusus untuk lingkungan yang menggunakan **Proxmox** dan **Cloudflare Tunnel**.

## 1. Prasyarat Server

Pastikan VM (Virtual Machine) Docker Anda (contoh: VM `103 svrct`) sudah terinstall:
*   **Docker Engine**: [Install Docker di Linux](https://docs.docker.com/engine/install/)
*   **Docker Compose**: Biasanya sudah include di Docker versi terbaru (`docker compose`).
*   **Git** (Opsional, untuk pull code).

## 2. Struktur File Deployment

Pastikan file-file berikut ada di root folder aplikasi Anda di server:

1.  `Dockerfile` (Resep build image PHP & Apache).
2.  `docker-compose.yml` (Orkestrasi Container App & Database).
3.  `.env` (Konfigurasi Environment & Password).
4.  Folder `database/` (Berisi `db_absensi_digital.sql` untuk inisialisasi awal).

## 3. Langkah Instalasi & Konfigurasi

### A. Konfigurasi Environment
Salin file `.env` dan sesuaikan passwordnya agar aman.

```bash
# Contoh isi file .env
DB_NAME=db_absensi_digital
DB_USER=absensi_user
DB_PASS=GantiPasswordYangSangatKuat!
DB_ROOT_PASS=GantiRootPasswordJuga!
```

> **PENTING:** Jangan lupa update juga file `app/config/midtrans_config.php` dengan **Server Key (Production)** yang asli jika belum.

### B. Menjalankan Container
Di terminal server, masuk ke folder project dan jalankan:

```bash
docker compose up -d --build
```

Perintah ini akan:
1.  Membangun image `absensi_web` dari Dockerfile.
2.  Mendownload image `mariadb` dan `phpmyadmin`.
3.  Membuat network internal `absensi_net`.
4.  Menjalankan semua layanan di background.

### C. Verifikasi
Cek apakah semua container berjalan dengan status `Up`:

```bash
docker compose ps
```

## 4. Konfigurasi Cloudflare Tunnel

Karena Anda menggunakan Cloudflare Tunnel di VM terpisah (VM `100 cloudflared`), Anda tidak perlu membuka port di router.

1.  Buka Dashboard **Cloudflare Zero Trust** > **Access** > **Tunnels**.
2.  Pilih Tunnel aktif Anda.
3.  Klik **Configure** > **Public Hostname**.
4.  Tambahkan Hostname baru:
    *   **Domain**: `absensi.sekolahku.com` (Ubah sesuai domain Anda).
    *   **Service**: `HTTP`.
    *   **URL**: `IP_LOKAL_VM_DOCKER:80` (Contoh: `192.168.1.103:80`).
5.  Simpan.

Dalam hitungan detik, aplikasi Anda sudah bisa diakses via HTTPS di `https://absensi.sekolahku.com`.

## 5. Manajemen & Maintenance

### Update Aplikasi (Codingan Baru)
Jika Anda mengupload perubahan kode baru ke folder server:

```bash
# Restart container aplikasi saja (agar kode terbaca ulang jika pakai opcache)
docker compose restart app
```

### Melihat Log Error
Jika ada masalah (misal: Error 500), cek log Apache/PHP:

```bash
docker compose logs -f app
```

### Backup Database
Untuk membackup database dari dalam container ke file SQL di host:

```bash
docker exec absensi_db /usr/bin/mysqldump -u root --password=GantiRootPasswordJuga! db_absensi_digital > backup_absensi_$(date +%F).sql
```

## 6. Akses phpMyAdmin (Opsional)
Jika Anda ingin mengakses phpMyAdmin:
1.  Tambahkan Public Hostname baru di Cloudflare Tunnel (misal: `pma.sekolahku.com`).
2.  Arahkan ke `IP_LOKAL_VM_DOCKER:8081`.
3.  Login menggunakan User & Password yang ada di file `.env`.

---
**Catatan Keamanan:**
*   Pastikan `MIDTRANS_IS_PRODUCTION` bernilai `true` di `midtrans_config.php` untuk server production.
*   Folder `uploads/` sudah di-mount ke host, jadi foto tidak hilang meskipun container di-recreate.
