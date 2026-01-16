import mysql.connector

db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'db_absensi_digital'
}

def debug_database():
    print("--- DEBUG DATABASE START ---")
    try:
        conn = mysql.connector.connect(**db_config)
        print("✅ Koneksi Database Berhasil")
        cursor = conn.cursor(dictionary=True)

        # 1. Cek Tabel Siswa
        print("\n[1] Sample Data Siswa (Top 3):")
        cursor.execute("SELECT id, nama_lengkap, kode_qr FROM tb_siswa LIMIT 3")
        students = cursor.fetchall()
        for s in students:
            print(f"   - ID: {s['id']}, Nama: {s['nama_lengkap']}, QR: '{s['kode_qr']}'")
            
        if not students:
            print("   ⚠️ TIDAK ADA DATA SISWA!")

        # 2. Cek Data Absensi Hari Ini
        print(f"\n[2] Data Absensi Hari Ini ({db_config['database']}):")
        cursor.execute("SELECT * FROM tb_absensi ORDER BY id DESC LIMIT 5")
        absensi = cursor.fetchall()
        for a in absensi:
            print(f"   - ID: {a['id']}, User: {a['user_id']}, Role: {a['role']}, Ket: {a['status']}")

        if not absensi:
            print("   ⚠️ BELUM ADA DATA ABSENSI MASUK.")

        conn.close()

    except mysql.connector.Error as err:
        print(f"❌ ERROR: {err}")

    print("\n--- DEBUG DATABASE END ---")

if __name__ == "__main__":
    debug_database()
