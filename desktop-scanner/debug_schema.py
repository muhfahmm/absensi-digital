import mysql.connector

db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'db_absensi_digital'
}

def check_schema():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        
        print("\n--- TABLE STATUS: tb_absensi ---")
        cursor.execute("DESCRIBE tb_absensi")
        for row in cursor.fetchall():
            print(row)
            
        print("\n--- INSERT TEST ---")
        # Try manual insert
        try:
            # Cari satu siswa
            cursor.execute("SELECT id FROM tb_siswa LIMIT 1")
            siswa = cursor.fetchone()
            if siswa:
                print(f"Found Siswa ID: {siswa[0]}")
                # Insert dummy
                sql = "INSERT INTO tb_absensi (user_id, role, tanggal, jam_masuk, status, created_at) VALUES (%s, 'siswa', CURDATE(), CURTIME(), 'hadir', NOW())"
                cursor.execute(sql, (siswa[0],))
                conn.commit()
                print(f"✅ Manual Insert Success! ID: {cursor.lastrowid}")
                
                # Cleanup
                cursor.execute("DELETE FROM tb_absensi WHERE id = %s", (cursor.lastrowid,))
                conn.commit()
                print("✅ Cleanup Success")
            else:
                print("❌ No Siswa found to test insert")
        except Exception as e:
            print(f"❌ Insert Failed: {e}")

        conn.close()
    except Exception as e:
        print(f"❌ DB Connection Failed: {e}")

if __name__ == "__main__":
    check_schema()
