# Panduan Upload Aplikasi ke Google Play Store

Selamat! Aplikasi Anda sudah mencapai tahap 80%. Namun, sebelum mengupload ke Play Store, ada beberapa hal **KRUSIAL** yang harus diselesaikan terlebih dahulu karena arsitektur aplikasi Anda saat ini masih berbasis *localhost*.

## ⚠️ Prasyarat Utama (WAJIB DILAKUKAN)

Saat ini aplikasi Anda berkomunikasi dengan `http://192.168.0.103/absensi-digital-2`.
**IP Address ini tidak akan bisa diakses oleh orang lain yang mendownload aplikasi dari Play Store.**

### 1. Hosting Backend (PHP & Database)
Anda **HARUS** mengupload backend (folder `absensi-digital-2` kecuali `mobile-app` dan `desktop-scanner`) ke hosting publik.
- **Beli Domain & Hosting** (Contoh: Niagahoster, Domainesia, Hostinger).
- **Upload File**: Upload semua file PHP ke `public_html`.
- **Import Database**: Export database lokal Anda (`.sql`) dan import ke phpMyAdmin di hosting.
- **Update Koneksi**: Sesuaikan `koneksi.php` di hosting dengan username/password database hosting.

### 2. Update `BASE_URL` di Aplikasi Mobile
Setelah backend online, Anda harus mengubah alamat IP di `mobile-app/App.js` menjadi domain baru Anda.

```javascript
// GANTI MENGGUNAKAN DOMAIN ASLI ANDA
const BASE_URL = 'https://nama-sekolah-anda.com/api'; 
```

---

## 🚀 Langkah-Langkah Build & Upload

Jika backend sudah beres, ikuti langkah ini untuk membuat file instalasi (`.aab`) untuk Play Store.

### 1. Persiapan Akun Google Play Console
- Anda perlu mendaftar di [Google Play Console](https://play.google.com/console).
- Biaya pendaftaran: **$25 USD (sekitar Rp 400.000)** sekali bayar seumur hidup.

### 2. Konfigurasi `app.json`
Buka file `mobile-app/app.json` dan tambahkan konfigurasi Android yang unik.
Contoh (tambahkan bagian `android`):

```json
{
  "expo": {
    "name": "Absensi Digital Sekolah XYZ",
    "slug": "absensi-digital-sekolah-xyz",
    "version": "1.0.0",
    "android": {
      "package": "com.namasekolah.absensidigital", 
      "versionCode": 1,
      "adaptiveIcon": {
        "foregroundImage": "./assets/adaptive-icon.png",
        "backgroundColor": "#ffffff"
      },
      "permissions": [
        "CAMERA",
        "INTERNET"
      ]
    }
  }
}
```
*Catatan: `package` harus unik dan tidak boleh diubah nantinya.*

### 3. Install EAS CLI (Expo Application Services)
Buka terminal di folder `mobile-app`:
```bash
npm install -g eas-cli
eas login
eas build:configure
```
Pilih **Android** ketika ditanya platform.

### 4. Build Aplikasi (Format AAB)
Play Store sekarang mewajibkan format `.aab` (Android App Bundle), bukan `.apk`.
Jalankan perintah ini:
```bash
eas build --platform android
```
- Tunggu proses selesai.
- Expo akan memberikan link download file `.aab` setelah selesai.

### 5. Upload ke Play Store
1. Buka **Google Play Console**.
2. Klik **Create App**.
3. Isi detail aplikasi (Nama, Bahasa, dll).
4. Masuk ke menu **Production** > **Create new release**.
5. Upload file `.aab` yang sudah didownload dari langkah 4.
6. Lengkapi :
   - **Store Listing**: Deskripsi, Screenshot, Ikon (512x512), Feature Graphic (1024x500).
   - **Content Rating**: Isi kuesioner rating umur.
   - **Privacy Policy**: Wajib punya link Privacy Policy (bisa buat gratis di Google Sites atau hosting Anda).
   - **Data Safety**: Deklarasikan bahwa aplikasi mengambil data (Kamera, Nama, dll).

### 6. Review & Publish
Setelah semua centang hijau, klik **Review release** dan **Start rollout to Production**.
Google akan mereview aplikasi Anda (biasanya 1-7 hari).

---

## ✅ Checklist Sebelum Upload
- [ ] Backend sudah di hosting publik (HTTPS).
- [ ] `BASE_URL` di `App.js` sudah diganti ke HTTPS.
- [ ] `app.json` sudah punya `package` name unik.
- [ ] Icon dan Splash screen sudah rapi.
- [ ] Selesaikan fitur-fitur yang masih "Dalam Pengembangan" atau sembunyikan fitur tersebut, karena Google bisa menolak aplikasi yang terlihat belum selesai (masih ada placeholder/crash).
