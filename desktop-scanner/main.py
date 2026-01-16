import cv2
import mysql.connector
from pyzbar.pyzbar import decode
import numpy as np
import time
from datetime import datetime
import threading
import logging
import sys

# Setup Logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("scanner_activity.log"),
        logging.StreamHandler(sys.stdout)
    ]
)

# --- DATABASE CONFIG ---
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'db_absensi_digital',
    'autocommit': True
}

def play_beep(success=True):
    try:
        import winsound
        if success:
            winsound.Beep(1200, 200)
        else:
            winsound.Beep(400, 600)
    except:
        pass

def get_db_connection():
    try:
        conn = mysql.connector.connect(**db_config)
        return conn
    except Exception as e:
        logging.error(f"Koneksi Database Gagal: {e}")
        return None

def process_qr_code(qr_data):
    qr_data = qr_data.strip()
    logging.info(f"--- DETEKSI BARU: '{qr_data}' ---")
    
    conn = get_db_connection()
    if not conn:
        return {'success': False, 'message': 'Gagal koneksi database'}
        
    try:
        cursor = conn.cursor(dictionary=True)
        
        # 1. Identity Check
        user = None
        # Cari di Siswa
        cursor.execute("SELECT id, nama_lengkap, 'siswa' as role FROM tb_siswa WHERE kode_qr = %s", (qr_data,))
        user = cursor.fetchone()
        
        if not user:
            # Cari di Guru
            cursor.execute("SELECT id, nama_lengkap, 'guru' as role FROM tb_guru WHERE kode_qr = %s", (qr_data,))
            user = cursor.fetchone()
            
        if not user:
            logging.warning(f"Hasil: QR TIDAK DIKENAL")
            return {'success': False, 'message': 'QR Code Tidak Terdaftar'}
            
        logging.info(f"User Berhasil Diidentifikasi: {user['nama_lengkap']} ({user['role']})")

        # 2. Check Duplikasi
        today = datetime.now().strftime('%Y-%m-%d')
        cursor.execute(
            "SELECT id FROM tb_absensi WHERE user_id = %s AND role = %s AND tanggal = %s",
            (user['id'], user['role'], today)
        )
        if cursor.fetchone():
            logging.info(f"Hasil: DUPLIKAT (User sudah absen hari ini)")
            return {
                'success': True,
                'already_present': True,
                'message': f"PRESENSI '{user['nama_lengkap']}' SUDAH DITERIMA SEBELUMNYA",
                'user': user
            }
            
        # 3. Logic Absensi
        now = datetime.now()
        jam_masuk = now.strftime('%H:%M:%S')
        status = 'hadir'
        
        # Aturan Telat: Senin 06:45, lainnya 07:00
        cutoff_h, cutoff_m = (6, 45) if now.weekday() == 0 else (7, 0)
        cutoff_time = now.replace(hour=cutoff_h, minute=cutoff_m, second=0, microsecond=0)
        
        if now > cutoff_time:
            status = 'terlambat'
        
        # 4. Insert ke Database
        logging.info(f"Menyimpan ke Database: {user['nama_lengkap']} | Status: {status}")
        cursor.execute(
            "INSERT INTO tb_absensi (user_id, role, tanggal, jam_masuk, status, created_at) VALUES (%s, %s, %s, %s, %s, NOW())",
            (user['id'], user['role'], today, jam_masuk, status)
        )
        conn.commit()
        
        logging.info(f"Hasil: BERHASIL DISIMPAN ({status})")
        return {
            'success': True,
            'already_present': False,
            'message': f"PRESENSI MASUK/KELUAR \"{user['nama_lengkap']}\" DITERIMA",
            'status_label': status.upper(),
            'status': status,
            'user': user
        }
        
    except Exception as e:
        logging.error(f"DATABASE ERROR: {str(e)}")
        return {'success': False, 'message': f"Sistem Error: {str(e)}"}
    finally:
        if conn and conn.is_connected():
            cursor.close()
            conn.close()

def draw_modern_ui(frame, is_locked, message="", color=(0,0,0), result_cache=None):
    h, w, _ = frame.shape
    font = cv2.FONT_HERSHEY_SIMPLEX
    
    # 1. Premium Header (Navy)
    cv2.rectangle(frame, (0, 0), (w, 80), (60, 30, 20), -1) 
    cv2.putText(frame, "E-ABSENSI SYSTEM", (30, 45), font, 1, (255, 255, 255), 3)
    cv2.putText(frame, "Status: Scanner Aktif & Siap", (32, 70), font, 0.5, (180, 180, 180), 1)

    # 2. Scanning Box Components
    box_size = 350
    x1, y1 = (w - box_size) // 2, (h - box_size) // 2 + 20
    x2, y2 = x1 + box_size, y1 + box_size
    
    # Background Dimming (if not locked)
    if not is_locked:
        overlay = frame.copy()
        cv2.rectangle(overlay, (0, 80), (w, h), (0, 0, 0), -1)
        cv2.rectangle(overlay, (x1, y1), (x2, y2), (255, 255, 255), -1)
        cv2.addWeighted(overlay, 0.5, frame, 0.5, 0, frame)
    
        # Scan Line (Moving Animation effect)
        scan_line_y = y1 + int((time.time() * 200) % box_size)
        cv2.line(frame, (x1, scan_line_y), (x2, scan_line_y), (0, 255, 0), 2)
    
    # Corners
    c_len, c_col = 35, (255, 255, 255)
    cv2.line(frame, (x1, y1), (x1+c_len, y1), c_col, 5)
    cv2.line(frame, (x1, y1), (x1, y1+c_len), c_col, 5)
    cv2.line(frame, (x2, y1), (x2-c_len, y1), c_col, 5)
    cv2.line(frame, (x2, y1), (x2, y1+c_len), c_col, 5)
    cv2.line(frame, (x1, y2), (x1+c_len, y2), c_col, 5)
    cv2.line(frame, (x1, y2), (x1, y2-c_len), c_col, 5)
    cv2.line(frame, (x2, y2), (x2-c_len, y2), c_col, 5)
    cv2.line(frame, (x2, y2), (x2, y2-c_len), c_col, 5)

    # 3. Result Overlay (Popup Card)
    if is_locked:
        cw, ch = 650, 300
        cx1, cy1 = (w - cw) // 2, (h - ch) // 2
        cx2, cy2 = cx1 + cw, cy1 + ch
        
        # Shadow & Smooth Card
        cv2.rectangle(frame, (cx1+10, cy1+10), (cx2+10, cy2+10), (20, 20, 20), -1)
        cv2.rectangle(frame, (cx1, cy1), (cx2, cy2), (255, 255, 255), -1)
        
        # Status Sidebar
        cv2.rectangle(frame, (cx1, cy1), (cx1+20, cy2), color, -1)
        
        # Result Header Text
        header_text = "BERHASIL"
        if result_cache:
            if result_cache.get('already_present'): header_text = "INFO"
            elif not result_cache.get('success'): header_text = "ERROR"
            
        cv2.putText(frame, header_text, (cx1+45, cy1+65), font, 1.4, color, 4)
        
        # First Line: PRESENSI MASUK/KELUAR "nama" DITERIMA
        # Using a slightly smaller font to fit names
        cv2.putText(frame, message, (cx1+45, cy1+140), font, 0.75, (40, 40, 40), 2)
        
        # Second Line: Status Label
        if result_cache and not result_cache.get('already_present') and result_cache.get('success'):
            status_text = f"Status: {result_cache.get('status_label')}"
            cv2.putText(frame, status_text, (cx1+45, cy1+200), font, 0.9, color, 2)
        elif result_cache and result_cache.get('already_present'):
            # For double scans, just show the message line
            pass
            
        cv2.putText(frame, "Sistem terkunci sementara...", (cx1+45, cy2-35), font, 0.5, (120, 120, 120), 1)

def main():
    cap = cv2.VideoCapture(0)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, 1280)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 720)
    
    actual_w = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
    actual_h = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
    logging.info(f"Kamera Aktif: {actual_w}x{actual_h}")
    
    window_name = "Presence Scanner v2.0"
    cv2.namedWindow(window_name, cv2.WINDOW_NORMAL)
    
    is_locked = False
    locked_until = 0
    freeze_frame = None
    message = ""
    message_color = (0, 0, 0)
    result_cache = None
    
    logging.info("--- SCANNER SIAP DIGUNAKAN ---")
    
    while True:
        current_time = time.time()
        
        if not is_locked:
            ret, frame = cap.read()
            if not ret: break
            
            gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
            decoded_objects = decode(gray)
            display_frame = cv2.flip(frame, 1)
            
            if decoded_objects:
                for obj in decoded_objects:
                    qr_data = obj.data.decode('utf-8')
                    result = process_qr_code(qr_data)
                    
                    if result['success']:
                        if result.get('already_present'):
                            message = result['message']
                            message_color = (0, 165, 255) # Orange for info
                        else:
                            status = result.get('status')
                            message = result['message']
                            
                            # Logic Warna sesuai request:
                            # "jika tidak terlambat berwarna hijau" (0, 200, 0)
                            # "jika terlambat berwarna merah" (0, 0, 220)
                            if status == 'terlambat':
                                message_color = (0, 0, 220) # RED (BGR)
                            else:
                                message_color = (0, 200, 0) # GREEN (BGR)
                    else:
                        message = result['message']
                        message_color = (0, 0, 230) # Red for system error
                    
                    is_locked = True
                    locked_until = current_time + 3.0
                    freeze_frame = display_frame.copy()
                    result_cache = result
                    
                    threading.Thread(target=play_beep, args=(result['success'],)).start()
                    break 
        else:
            display_frame = freeze_frame.copy()
            if current_time > locked_until:
                is_locked = False
        
        draw_modern_ui(display_frame, is_locked, message, message_color, result_cache)
        cv2.imshow(window_name, display_frame)
        
        if cv2.waitKey(1) & 0xFF in [ord('q'), 27]: break
            
    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()
