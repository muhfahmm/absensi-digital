import cv2
import mysql.connector
from pyzbar.pyzbar import decode
import numpy as np
import time
from datetime import datetime
import threading
# import winsound  # Built-in Windows sound

import logging

# Setup Logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("scanner_debug.log"),
        logging.StreamHandler()
    ]
)

# --- CONFIGURASI DATABASE ---
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'db_absensi_digital',
    'autocommit': True  # IMPORTANT: Enable autocommit to ensure data is saved
}

def play_beep(success=True):
    import winsound
    if success:
        winsound.Beep(1000, 200) # High pitch, short
    else:
        winsound.Beep(500, 500)  # Low pitch, long

def get_db_connection():
    return mysql.connector.connect(**db_config)

def process_qr_code(qr_data):
    conn = None
    logging.info(f"Processing QR: '{qr_data}'")
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # 1. Cari User (Siswa/Guru)
        logging.info("Searching in tb_siswa...")
        cursor.execute("SELECT id, nama_lengkap, 'siswa' as role FROM tb_siswa WHERE kode_qr = %s", (qr_data,))
        user = cursor.fetchone()
        
        if not user:
            logging.info("Not found in tb_siswa. Searching in tb_guru...")
            cursor.execute("SELECT id, nama_lengkap, 'guru' as role FROM tb_guru WHERE kode_qr = %s", (qr_data,))
            user = cursor.fetchone()
            
        if not user:
            logging.warning(f"QR Not Found in DB: {qr_data}")
            return {'success': False, 'message': 'QR Code Tidak Dikenal'}
            
        logging.info(f"User Found: {user['nama_lengkap']} ({user['role']})")

        # 2. Cek Absensi Hari Ini
        today = datetime.now().strftime('%Y-%m-%d')
        cursor.execute(
            "SELECT id FROM tb_absensi WHERE user_id = %s AND role = %s AND tanggal = %s",
            (user['id'], user['role'], today)
        )
        existing = cursor.fetchone()
        
        if existing:
            logging.info(f"User already present today.")
            return {
                'success': True, # Masih dianggap sukses scan, tapi statusnya info
                'already_present': True,
                'message': f"Sudah Absen: {user['nama_lengkap']}",
                'user': user
            }
            
        # 3. Insert Absensi
        jam_masuk = datetime.now().strftime('%H:%M:%S')
        status = 'hadir'
        
        # Logika Telat
        now = datetime.now()
        day_of_week = now.weekday() # 0 = Senin, ... 6 = Minggu
        
        # Default Rule (Selasa-Jumat): 07:00
        cutoff_hour = 7
        cutoff_minute = 0
        
        # Khusus Senin: 06:45
        if day_of_week == 0:
            cutoff_hour = 6
            cutoff_minute = 45
            
        cutoff_time = now.replace(hour=cutoff_hour, minute=cutoff_minute, second=0, microsecond=0)
        
        if now > cutoff_time:
            status = 'terlambat'
            logging.info(f"Status TERLAMBAT detected (Cutoff {cutoff_hour}:{cutoff_minute:02d})")
        
        logging.info(f"Inserting attendance... Status: {status}")
        cursor.execute(
            "INSERT INTO tb_absensi (user_id, role, tanggal, jam_masuk, status, created_at) VALUES (%s, %s, %s, %s, %s, NOW())",
            (user['id'], user['role'], today, jam_masuk, status)
        )
        conn.commit()
        
        if cursor.rowcount > 0:
            logging.info("Insert Success! Row Count: 1")
        else:
            logging.error("Insert failed, rowcount 0")

        logging.info(f"Insert Success!")
        
        return {
            'success': True,
            'already_present': False,
            'message': f"Berhasil: {user['nama_lengkap']}",
            'status': status,
            'user': user
        }
        
    except mysql.connector.Error as err:
        logging.error(f"Database Error: {err}")
        return {'success': False, 'message': f"DB Error: {err}"}
    finally:
        if conn and conn.is_connected():
            cursor.close()
            conn.close()

def draw_modern_ui(frame, current_time, message, message_color, message_timer):
    height, width, _ = frame.shape
    
    # --- STYLE MATCHING WEB THEME ---
    # Warna Header: Blue-600 to Indigo-600 approximation
    # BGR Format
    header_color = (235, 99, 37) # Blue-ish
    
    # 1. Header Bar (Full Width)
    header_h = 60
    cv2.rectangle(frame, (0, 0), (width, header_h), header_color, -1)
    
    # Header Text
    font = cv2.FONT_HERSHEY_SIMPLEX
    cv2.putText(frame, "Kamera Scanner", (20, 40), font, 1, (255, 255, 255), 2)
    cv2.putText(frame, "Arahkan ke QR Code", (300, 40), font, 0.6, (230, 230, 230), 1)

    # 2. Scan Guide / Corners (Clean White)
    # Tidak perlu dimmed background, biar terlihat 'clean' seperti web
    scan_size = 300
    x1 = (width - scan_size) // 2
    y1 = (height - scan_size) // 2 + 30 # Offset sedikit ke bawah karena ada header
    x2 = x1 + scan_size
    y2 = y1 + scan_size
    
    corner_len = 20
    corner_thk = 4
    corner_col = (255, 255, 255)
    
    # Gambar Siku
    # Top-Left
    cv2.line(frame, (x1, y1), (x1 + corner_len, y1), corner_col, corner_thk)
    cv2.line(frame, (x1, y1), (x1, y1 + corner_len), corner_col, corner_thk)
    # Top-Right
    cv2.line(frame, (x2, y1), (x2 - corner_len, y1), corner_col, corner_thk)
    cv2.line(frame, (x2, y1), (x2, y1 + corner_len), corner_col, corner_thk)
    # Bottom-Left
    cv2.line(frame, (x1, y2), (x1 + corner_len, y2), corner_col, corner_thk)
    cv2.line(frame, (x1, y2), (x1, y2 - corner_len), corner_col, corner_thk)
    # Bottom-Right
    cv2.line(frame, (x2, y2), (x2 - corner_len, y2), corner_col, corner_thk)
    cv2.line(frame, (x2, y2), (x2, y2 - corner_len), corner_col, corner_thk)

    # 3. Status Notification (Jika ada)
    if current_time < message_timer:
        # Status Box di Bawah
        # Mirip alert component
        bar_h = 80
        y_bar = height - bar_h
        
        # Background bar sesuai status
        cv2.rectangle(frame, (0, y_bar), (width, height), message_color, -1)
        
        # Center message
        text_size = cv2.getTextSize(message, font, 1, 2)[0]
        tx = (width - text_size[0]) // 2
        ty = y_bar + (bar_h + text_size[1]) // 2
        
        # Text Color (Hitam jika kuning, Putih jika yg lain)
        text_col = (0,0,0) if message_color == (0,255,255) else (255,255,255)
        
        cv2.putText(frame, message, (tx, ty), font, 1, text_col, 2)
    else:
        # Default Footer
        cv2.rectangle(frame, (0, height-30), (width, height), (50, 50, 50), -1)
        cv2.putText(frame, "Python Scanner Active", (10, height-10), font, 0.5, (200, 200, 200), 1)

def main():
    cap = cv2.VideoCapture(0)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, 1280) # Try HD
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 720)
    
    # If HD fails, it falls back to default. Check actual size
    width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
    height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
    
    # Window setup
    window_name = "Scanner Absensi Digital"
    cv2.namedWindow(window_name, cv2.WINDOW_NORMAL)
    # cv2.setWindowProperty(window_name, cv2.WND_PROP_FULLSCREEN, cv2.WINDOW_FULLSCREEN) # Optional: Enable for Kiosk
    
    last_scan_time = 0
    scan_cooldown = 2.0 
    
    message = ""
    message_color = (0, 0, 0)
    message_timer = 0
    
    logging.info("Starting Modern UI Scanner...")
    
    while True:
        ret, frame = cap.read()
        if not ret:
            break
            
        # Flip for mirror effect (optional, feels more natural)
        frame = cv2.flip(frame, 1)
        
        current_time = time.time()
        
        # QR Code Detection
        decoded_objects = decode(frame)
        
        # Logic Scan
        if current_time - last_scan_time > scan_cooldown:
            for obj in decoded_objects:
                qr_data = obj.data.decode('utf-8')
                result = process_qr_code(qr_data)
                
                if result['success']:
                    last_scan_time = time.time()
                    threading.Thread(target=play_beep, args=(True,)).start()
                    
                    if result.get('already_present'):
                        message = f"SUDAH ABSEN: {result['user']['nama_lengkap']}"
                        message_color = (0, 255, 255) # Yellow
                    else:
                        status_text = "TERLAMBAT" if result.get('status') == 'terlambat' else "HADIR"
                        message = f"{status_text}: {result['user']['nama_lengkap']}"
                        message_color = (0, 255, 0) # Green
                else:
                    threading.Thread(target=play_beep, args=(False,)).start()
                    message = result['message']
                    message_color = (0, 0, 255) # Red
                
                message_timer = current_time + 3.0
                break # Only process one QR per frame
        
        # Draw UI
        draw_modern_ui(frame, current_time, message, message_color, message_timer)
            
        cv2.imshow(window_name, frame)
        
        key = cv2.waitKey(1) & 0xFF
        if key == ord('q') or key == 27: # Q or ESC
            break
            
    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()
