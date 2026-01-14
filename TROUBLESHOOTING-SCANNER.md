# Troubleshooting Scanner QR Code

## Masalah: Scanner Tidak Bisa Membaca QR Code

### ✅ Solusi & Testing:

#### 1. **Test dengan QR Code Generator**
Buka file: `http://localhost/absensi%20digital/test-qr.html`

**Langkah:**
1. Generate QR code dengan kode yang ada di database (contoh: `SISWA-001`)
2. **PRINT** halaman tersebut (jangan scan dari layar)
3. Scan QR code yang sudah di-print dengan scanner
4. Lihat console log di browser

#### 2. **Cek Data di Database**
```sql
SELECT id, nis, nama_lengkap, kode_qr FROM tb_siswa LIMIT 5;
```

Pastikan kolom `kode_qr` terisi dengan format: `SISWA-{NIS}-{uniqid}`

Contoh: `SISWA-12345-65a1b2c3d4e5f`

#### 3. **Kenapa Scan dari Layar Tidak Berfungsi?**
- ❌ **Brightness** layar terlalu terang/gelap
- ❌ **Refleksi** dari layar
- ❌ **Resolusi** kamera tidak cukup
- ❌ **Refresh rate** layar mengganggu

**Solusi:**
- ✅ **Print QR code** di kertas putih
- ✅ Gunakan **layar kedua** dengan brightness max
- ✅ Scan dari **jarak 15-20cm**
- ✅ Pastikan **tidak ada refleksi**

#### 4. **Test Scanner Langkah demi Langkah**

**A. Buat Siswa Baru:**
1. Login sebagai admin
2. Buka: Admin → Data Siswa → Tambah
3. Isi data siswa (NIS: 12345, Nama: Test Siswa)
4. Simpan

**B. Cek Kode QR:**
1. Login sebagai siswa (username: 12345, password: yang dibuat)
2. Lihat QR code di dashboard
3. Klik tombol QR di bottom nav
4. Screenshot QR code atau catat kode text-nya

**C. Print QR Code:**
1. Buka `test-qr.html`
2. Masukkan kode QR yang sama
3. Print halaman (Ctrl+P)
4. Gunakan kertas putih

**D. Test Scanner:**
1. Buka: `http://localhost/absensi%20digital/app/pages/admin/scanner/index.php`
2. Klik "Mulai Scan"
3. Izinkan akses kamera
4. Arahkan ke QR code yang sudah di-print
5. Tunggu konfirmasi:
   - 🔊 Bunyi beep
   - 📱 Overlay biru "LOCKED!"
   - 💬 Status "🔒 LOCKED! Memproses..."

#### 5. **Debug Console Log**

Buka Console (F12) dan lihat log:
```
✅ Scanner berhasil dimulai!
Detection #1: SISWA-12345-65a1b2c3d4e5f
✅ 🔒 LOCKED! First detection accepted
✅ Scanner cleared
```

Jika muncul:
```
❌ IGNORED - Already locked
```
Berarti scanner sudah ter-lock (SUKSES!)

#### 6. **Alternatif: Scan QR dari HP Lain**

1. Buka dashboard siswa di HP A
2. Tampilkan QR code
3. **Brightness MAX**
4. Scan dengan scanner di HP B/Laptop
5. Jarak 15-20cm
6. Hindari refleksi

#### 7. **Cek Database Setelah Scan**

```sql
SELECT * FROM tb_absensi 
WHERE tanggal = CURDATE() 
ORDER BY created_at DESC 
LIMIT 5;
```

Jika berhasil, akan ada record baru dengan:
- `user_id`: ID siswa
- `role`: 'siswa'
- `status`: 'hadir' atau 'terlambat'
- `jam_masuk`: waktu scan

---

## 🔍 Checklist Troubleshooting

- [ ] Kode QR sudah ada di database (cek tb_siswa.kode_qr)
- [ ] QR code di-print di kertas (bukan scan dari layar)
- [ ] Kamera sudah diberi izin akses
- [ ] Scanner menampilkan "Scanning aktif..."
- [ ] QR code jelas terlihat (tidak blur)
- [ ] Jarak scan 15-20cm
- [ ] Tidak ada refleksi cahaya
- [ ] Console log menampilkan "Detection #1"
- [ ] Muncul overlay "LOCKED!"
- [ ] Terdengar bunyi beep
- [ ] Data masuk ke tb_absensi

---

## 📝 Format QR Code yang Benar

**Siswa:**
```
SISWA-{NIS}-{uniqid}
Contoh: SISWA-12345-65a1b2c3d4e5f
```

**Guru:**
```
GURU-{NIP}-{uniqid}
Contoh: GURU-98765-65a1b2c3d4e5f
```

---

## ⚠️ Catatan Penting

1. **JANGAN scan QR dari layar HP yang sama** (tidak akan berfungsi)
2. **PRINT QR code** untuk hasil terbaik
3. **Gunakan 2 device** (1 untuk tampil QR, 1 untuk scan)
4. **Brightness MAX** jika scan dari layar
5. **Console log** adalah teman terbaik untuk debug

---

**Last Updated:** 14 Januari 2026
