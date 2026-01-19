import React, { useState, useEffect, useMemo, useCallback } from 'react';
import {
    StyleSheet,
    Text,
    View,
    TextInput,
    TouchableOpacity,
    Alert,
    ActivityIndicator,
    SafeAreaView,
    Modal,
    ScrollView,
    Image,
    Dimensions,
    useColorScheme,
    Linking,
    RefreshControl,
    KeyboardAvoidingView,
    Platform,
    Keyboard,
    PanResponder,
    Animated
} from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { CameraView, Camera } from "expo-camera";
import { WebView } from "react-native-webview";
import Constants from 'expo-constants';

import QRCode from 'react-native-qrcode-svg';
import Svg, { Path } from 'react-native-svg';
import AsyncStorage from '@react-native-async-storage/async-storage';

const { width, height } = Dimensions.get('window');

// BASE_URL moved to App component state for dynamic configuration
// const BASE_URL = ...

// Icon paths from dashboard.php and reference image
const PATHS = {
    logout: "M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1",
    tag: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z",
    building: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4",
    clipboard: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4",
    user: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
    card: "M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z",
    speaker: "M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z",
    home: "M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z",
    qr: "M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z",
    cap: "M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z",
    search: "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
    close: "M6 18L18 6M6 6l12 12",
    download: "M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4",
    moon: "M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z",
    sun: "M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z",
    back: "M10 19l-7-7m0 0l7-7m-7 7h18",
    calendar: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    eye: "M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z",
    book: "M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253",
    globe: "M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9",
    info: "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    github: "M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12",
    instagram: "M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z",
    server: "M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01",
    users: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
    link: "M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1",
    heart: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
    lock: "M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z",
    eye_off: "M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21",
    fileText: "M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8",
    barChart: "M18 20V10 M12 20V4 M6 20v6",
    fileText: "M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8",
    barChart: "M18 20V10 M12 20V4 M6 20v6"
};

const WebIcon = ({ name, size = 24, color = "white", strokeWidth = 2, style }) => (
    <Svg width={size} height={size} viewBox="0 0 24 24" fill="none" style={style}>
        <Path
            d={PATHS[name]}
            stroke={color}
            strokeWidth={strokeWidth}
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </Svg>
);

const translations = {
    id: {
        loginTitle: "Absensi Digital",
        loginSubtitle: "Silakan login untuk melanjutkan",
        usernameLabel: "Username / NIS / NUPTK",
        passwordLabel: "Password",
        loginBtn: "Masuk Ke Akun",
        home: "Home",
        qr: "QR Code",
        profil: "Profil",
        dashboard: "Dashboard",
        statusAbsensi: "Status Absensi Hari Ini",
        sudahPulang: "Sudah Pulang",
        sudahMasuk: "Sudah Masuk",
        belumAbsen: "Belum Absen",
        jamMasuk: "Jam Masuk",
        jamPulang: "Jam Pulang",
        kehadiran: "Kehadiran",
        riwayatAbsensi: "Riwayat absensi",
        pembayaran: "Pembayaran",
        statusSpp: "Status SPP",
        pengumuman: "Pengumuman",
        infoTerbaru: "Info terbaru",
        profilSaya: "Profil Saya",
        infoPribadi: "Informasi data pribadi",
        nis: "Nomor Induk Siswa",
        nuptk: "NUPTK",
        kelas: "Kelas",
        kodeQr: "Kode QR",
        terdaftarSejak: "Terdaftar Sejak",
        scanQrSiswa: "Scan QR Siswa",
        keluarAkun: "Keluar dari Akun",
        riwayatKehadiran: "Riwayat Kehadiran",
        dataAbsensiBulan: "Data absensi bulan ini",
        aktivitasTerbaru: "Aktivitas Terbaru",
        hariTerakhir: "30 Hari Terakhir",
        tidakAdaRiwayat: "Belum ada 30 hari terakhir",
        fitur: "Fitur",
        dalamPengembangan: "Halaman ini sedang dalam pengembangan dan akan segera tersedia.",
        selesai: "Selesai",
        monitoringKelas: "Monitoring Kelas",
        pantauSiswa: "Pantau kehadiran siswa",
        totalSiswa: "Total Siswa",
        sudahAbsen: "Sudah Absen",
        terlambat: "Terlambat",
        hadir: "Hadir",
        izin: "Izin",
        sakit: "Sakit",
        batal: "Batal",
        arahkanKamera: "Arahkan kamera ke QR Code Siswa",
        downloadQr: "Download QR Code",
        tunjukkanQr: "Tunjukkan QR Code ini untuk absensi",
        jadwalPelajaran: "Jadwal Pelajaran",
        lihatJadwal: "Lihat jadwal mingguan",
        elearning: "E-Learning",
        aksesMateri: "Akses materi pelajaran",
        tentangAplikasi: "Tentang Aplikasi",
        detailAplikasi: "Informasi dan versi aplikasi",
        versi: "Versi",
        pengembang: "Pengembang",
        deskripsiApp: "Absensi Digital adalah platform manajemen kehadiran modern untuk sekolah.",
        kontakSupport: "Kontak Support",
        website: "Website",
        mediaSosial: "Media Sosial",
        partner: "Partner",
        perizinan: "Perizinan",
        izinSakit: "Izin & Sakit",
        nilai: "Nilai",
        raportAkademik: "Raport & Akademik",
        lihat: "Lihat",
        download: "Download",
        cariMateri: "Cari materi...",
        cariMateri: "Cari materi...",
        tidakAdaMateri: "Tidak ada materi ditemukan.",
        noAnnouncement: "Tidak ada pengumuman terbaru.",
        umum: "UMUM",
        guru: "GURU",
        siswa: "SISWA",
        semuaWarga: "SEMUA WARGA",
        semua: "SEMUA",
        khususGuru: "Khusus Guru",
        khususSiswa: "Khusus Siswa",
        lihatSelengkapnya: "Lihat Selengkapnya",
        detailPengumuman: "Detail Pengumuman",
        diterbitkanPada: "Diterbitkan pada",
        tutup: "Tutup",
        baru: "BARU",
        filter: "Filter",
        newest: "Terbaru",
        oldest: "Terlama",
        thisWeek: "Minggu Ini",
        thisMonth: "Bulan Ini",
        pelajaran: "Pelajaran",
        cariNilai: "Cari nilai / pelajaran...",
        lupaPassword: "Lupa password? Hubungi Admin Sekolah"
    },
    en: {
        loginTitle: "Digital Attendance",
        loginSubtitle: "Please login to continue",
        usernameLabel: "Username / IDs",
        passwordLabel: "Password",
        loginBtn: "Sign In",
        home: "Home",
        qr: "QR Code",
        profil: "Profile",
        dashboard: "Dashboard",
        statusAbsensi: "Today's Attendance Status",
        sudahPulang: "Checked Out",
        sudahMasuk: "Checked In",
        belumAbsen: "Not Present",
        jamMasuk: "Check In",
        jamPulang: "Check Out",
        kehadiran: "Attendance",
        riwayatAbsensi: "Attendance history",
        pembayaran: "Payments",
        statusSpp: "Tuition status",
        pengumuman: "Announcements",
        infoTerbaru: "Latest info",
        profilSaya: "My Profile",
        infoPribadi: "Personal information",
        nis: "Student ID",
        nuptk: "NUPTK ID",
        kelas: "Class",
        kodeQr: "QR Code",
        terdaftarSejak: "Registered Since",
        scanQrSiswa: "Scan Student QR",
        keluarAkun: "Sign Out",
        riwayatKehadiran: "Attendance History",
        dataAbsensiBulan: "Monthly data",
        aktivitasTerbaru: "Recent Activity",
        hariTerakhir: "Last 30 Days",
        tidakAdaRiwayat: "No history found",
        fitur: "Feature",
        dalamPengembangan: "This page is under development and will be available soon.",
        selesai: "Done",
        monitoringKelas: "Class Monitoring",
        pantauSiswa: "Monitor student attendance",
        totalSiswa: "Total Students",
        sudahAbsen: "Present",
        terlambat: "Late",
        hadir: "Present",
        izin: "Permission",
        sakit: "Sick",
        batal: "Cancel",
        arahkanKamera: "Point camera at Student QR Code",
        downloadQr: "Download QR Code",
        tunjukkanQr: "Show this QR Code for attendance",
        jadwalPelajaran: "Class Schedule",
        lihatJadwal: "View weekly schedule",
        elearning: "E-Learning",
        aksesMateri: "Access learning materials",
        tentangAplikasi: "About App",
        detailAplikasi: "Application information & version",
        versi: "Version",
        pengembang: "Developer",
        deskripsiApp: "Digital Attendance is a modern attendance management platform for schools.",
        kontakSupport: "Contact Support",
        website: "Website",
        mediaSosial: "Social Media",
        partner: "Partner",
        perizinan: "Permission",
        izinSakit: "Leave & Sick",
        nilai: "Grades",
        raportAkademik: "Report Card & Academic",
        lihat: "View",
        download: "Download",
        cariMateri: "Search materials...",
        tidakAdaMateri: "No materials found.",
        noAnnouncement: "No latest announcements.",
        umum: "GENERAL",
        guru: "TEACHER",
        siswa: "STUDENT",
        semuaWarga: "ALL MEMBERS",
        semua: "ALL",
        khususGuru: "Teachers Only",
        khususSiswa: "Students Only",
        lihatSelengkapnya: "Read More",
        detailPengumuman: "Announcement Detail",
        diterbitkanPada: "Published on",
        tutup: "Close",
        baru: "NEW",
        filter: "Filter",
        newest: "Newest",
        oldest: "Oldest",
        thisWeek: "This Week",
        thisMonth: "This Month",
        pelajaran: "Subject",
        cariNilai: "Search grades / subjects...",
        lupaPassword: "Forgot password? Contact School Admin"
    }
};

export default function App() {
    const systemTheme = useColorScheme();
    const [isDarkMode, setIsDarkMode] = useState(systemTheme === 'dark');

    // Dynamic Server URL State
    const [baseUrl, setBaseUrl] = useState('http://192.168.0.105/absensi-digital-2');

    // Shadow global BASE_URL with state for compatibility
    const BASE_URL = baseUrl;

    useEffect(() => {
        const loadServerConfig = async () => {
            // Auto-detect from Expo Host (DHCP-like behavior)
            const debuggerHost = Constants.expoConfig?.hostUri;
            if (debuggerHost) {
                const ip = debuggerHost.split(':')[0];
                // Default assumption: XAMPP on port 80, project folder 'absensi-digital-2'
                const detectedUrl = `http://${ip}/absensi-digital-2`;
                console.log('Auto-detected Server URL:', detectedUrl);
                setBaseUrl(detectedUrl);
            }
        };
        loadServerConfig();
    }, []);

    const [currentView, setCurrentView] = useState('login'); // login, dashboard, scanner, kehadiran, profil, pembayaran, pengumuman
    const [userData, setUserData] = useState(null);
    const [attendanceStatus, setAttendanceStatus] = useState(null);
    const [attendanceHistory, setAttendanceHistory] = useState({ history: [], summary: null });
    const [classMonitoring, setClassMonitoring] = useState(null);
    const [jadwal, setJadwal] = useState(null); // Schedules
    const [learningMaterials, setLearningMaterials] = useState([]);
    const [selectedDayJadwal, setSelectedDayJadwal] = useState('Senin');
    const [language, setLanguage] = useState('id'); // 'id' or 'en'
    const [profileModalVisible, setProfileModalVisible] = useState(false);
    const [refreshing, setRefreshing] = useState(false);
    const [menuModalVisible, setMenuModalVisible] = useState(false);
    const [pengumumanList, setPengumumanList] = useState([]);
    const [selectedPengumuman, setSelectedPengumuman] = useState(null);
    const [detailModalVisible, setDetailModalVisible] = useState(false);
    const [nilaiData, setNilaiData] = useState([]);
    const [nilaiSearch, setNilaiSearch] = useState('');
    const [nilaiSort, setNilaiSort] = useState('newest'); // newest, oldest, pelajaran
    const [isNilaiSortDropdownOpen, setIsNilaiSortDropdownOpen] = useState(false);

    // Comments State
    const [commentModalVisible, setCommentModalVisible] = useState(false);
    const [currentMateriId, setCurrentMateriId] = useState(null);
    const [comments, setComments] = useState([]);
    const [newComment, setNewComment] = useState('');
    const [isCommentLoading, setIsCommentLoading] = useState(false);
    const [editingCommentId, setEditingCommentId] = useState(null);
    const [editingCommentText, setEditingCommentText] = useState('');
    const [longPressMenuVisible, setLongPressMenuVisible] = useState(false);
    const [selectedCommentForMenu, setSelectedCommentForMenu] = useState(null);

    // Payment States
    const [topUpAmount, setTopUpAmount] = useState('');
    const [showTopUpModal, setShowTopUpModal] = useState(false);
    const [paymentUrl, setPaymentUrl] = useState(null);
    const [showPaymentModal, setShowPaymentModal] = useState(false);
    const [saldo, setSaldo] = useState(0);
    const [riwayatSaldo, setRiwayatSaldo] = useState([]);
    const [currentOrderId, setCurrentOrderId] = useState(null);
    const [showPaymentMethodModal, setShowPaymentMethodModal] = useState(false);
    const [selectedTagihan, setSelectedTagihan] = useState(null);

    // --- CUSTOM ALERT STATE ---
    const [alertConfig, setAlertConfig] = useState({
        visible: false,
        title: '',
        message: '',
        buttons: [], // [{ text: 'OK', onPress: () => {}, style: 'cancel' | 'default' | 'destructive' }]
        type: 'info' // 'success', 'error', 'info', 'warning'
    });

    const showCustomAlert = (title, message, buttons = [], type = 'info') => {
        // If no buttons provided, default to OK
        if (!buttons || buttons.length === 0) {
            buttons = [{ text: 'OK', onPress: () => closeCustomAlert() }];
        }

        // Wrap onPress to close modal
        const wrappedButtons = buttons.map(btn => ({
            ...btn,
            onPress: () => {
                closeCustomAlert();
                if (btn.onPress) btn.onPress();
            }
        }));

        setAlertConfig({
            visible: true,
            title,
            message,
            buttons: wrappedButtons,
            type
        });
    };

    const closeCustomAlert = () => {
        setAlertConfig(prev => ({ ...prev, visible: false }));
    };

    // Animation for Payment Modal
    const panY = React.useRef(new Animated.Value(0)).current;

    const resetModalAnim = useCallback(() => {
        Animated.timing(panY, {
            toValue: 0,
            duration: 300,
            useNativeDriver: true,
        }).start();
    }, [panY]);

    const paymentPanResponder = React.useRef(
        PanResponder.create({
            onStartShouldSetPanResponder: () => true,
            onMoveShouldSetPanResponder: (_, gestureState) => {
                return gestureState.dy > 0; // Only capture if moving down
            },
            onPanResponderMove: Animated.event(
                [null, { dy: panY }],
                { useNativeDriver: false } // 'dy' is not supported with native driver in PanResponder usually
            ),
            onPanResponderRelease: (_, gestureState) => {
                if (gestureState.dy > 100) {
                    closePaymentMethodModal();
                } else {
                    Animated.spring(panY, {
                        toValue: 0,
                        useNativeDriver: true
                    }).start();
                }
            }
        })
    ).current;

    const closePaymentMethodModal = () => {
        Animated.timing(panY, {
            toValue: height, // Slide down off screen
            duration: 300,
            useNativeDriver: true
        }).start(() => setShowPaymentMethodModal(false));
    };

    useEffect(() => {
        if (showPaymentMethodModal) {
            panY.setValue(0); // Reset immediately on open
        }
    }, [showPaymentMethodModal]);

    // --- PAYMENT TAB STATE ---
    const [paymentTab, setPaymentTab] = useState('transaksi'); // 'transaksi', 'tagihan', 'lunas'
    const [sppData, setSppData] = useState({ unpaid: [], paid: [] });
    const [isSppLoading, setIsSppLoading] = useState(false);

    // Konfigurasi Versi Aplikasi
    const CURRENT_APP_VERSION = "1.0.0";

    useEffect(() => {
        const loadLanguage = async () => {
            try {
                const savedLanguage = await AsyncStorage.getItem('user-language');
                if (savedLanguage) {
                    setLanguage(savedLanguage);
                }
            } catch (e) {
                console.log('Failed to load language');
            }
        };
        loadLanguage();

        // Cek versi aplikasi secara berkala (setiap 1 menit)
        const versionInterval = setInterval(checkAppVersion, 60000);
        return () => clearInterval(versionInterval);
    }, []);

    useEffect(() => {
        if (currentView === 'pembayaran') {
            fetchSaldo();
            if (userData?.role === 'siswa') {
                fetchSppData();
            }
        }
    }, [currentView]);

    const fetchSppData = async () => {
        setIsSppLoading(true);
        try {
            const response = await fetch(`${BASE_URL}/app/api/payment/get_spp.php?user_id=${userData.user.id}`);
            const result = await response.json();
            if (result.success) {
                setSppData(result.data);
            }
        } catch (error) {
            console.error("Fetch SPP Error", error);
        } finally {
            setIsSppLoading(false);
        }
    };

    const handlePaySpp = (tagihanId, amount) => {
        setSelectedTagihan({ id: tagihanId, amount: parseInt(amount) });
        setShowPaymentMethodModal(true);
    };

    const handlePaySppWallet = async (tagihanId) => {
        setIsSppLoading(true);
        closePaymentMethodModal(); // Use close helper
        try {
            const response = await fetch(`${BASE_URL}/app/api/payment/pay_spp_wallet.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.user.id,
                    tagihan_id: tagihanId
                })
            });
            const result = await response.json();

            showCustomAlert(
                result.success ? "Berhasil" : "Gagal",
                result.message,
                [{
                    text: "OK",
                    onPress: () => {
                        if (result.success) {
                            fetchSppData();
                            fetchSaldo();
                        }
                    }
                }],
                result.success ? 'success' : 'error'
            );
        } catch (error) {
            showCustomAlert("Error", "Gagal menghubungi server", [], 'error');
        } finally {
            setIsSppLoading(false);
        }
    };

    const handlePaySppMidtrans = async (tagihanId, amount) => {
        closePaymentMethodModal(); // Use close helper
        try {
            const response = await fetch(`${BASE_URL}/app/api/payment/snap_token.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.user.id,
                    role: userData.role,
                    amount: parseInt(amount),
                    type: 'spp',
                    target_id: tagihanId
                })
            });
            const result = await response.json();
            if (result.token) {
                setPaymentUrl(result.redirect_url);
                setCurrentOrderId(result.order_id);
                setShowPaymentModal(true);
            } else {
                showCustomAlert("Error", result.message || "Gagal membuat transaksi SPP", [], 'error');
            }
        } catch (e) {
            showCustomAlert("Error", "Gagal menghubungi server pembayaran", [], 'error');
        }
    };


    const checkAppVersion = async () => {
        try {
            const response = await fetch(`${BASE_URL}/app/api/app_info.php`);
            const result = await response.json();
            if (result.success && result.version !== CURRENT_APP_VERSION) {
                showCustomAlert(
                    "Update Tersedia",
                    "Versi aplikasi telah diperbarui. Silakan login kembali untuk menikmati fitur terbaru.",
                    [{ text: "OK", onPress: () => handleLogout() }],
                    'info'
                );
            }
        } catch (error) {
            console.log("Version check failed", error);
        }
    };

    const onRefresh = React.useCallback(() => {
        setRefreshing(true);
        // Refresh data based on current view
        if (currentView === 'dashboard') fetchAttendanceStatus();
        if (currentView === 'kehadiran') fetchAttendanceHistory();
        if (currentView === 'monitoring') fetchClassMonitoring();
        if (currentView === 'jadwal') fetchJadwal();
        if (currentView === 'elearning') fetchLearningMaterials();
        if (currentView === 'pengumuman') fetchPengumuman();
        if (currentView === 'nilai') fetchNilaiData();

        // Check version on manually refresh too
        checkAppVersion();

        setTimeout(() => {
            setRefreshing(false);
        }, 1500);
    }, [currentView, userData]);

    const t = (key) => translations[language][key] || key;

    // Login State
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [isSecureEntry, setIsSecureEntry] = useState(true);
    const [loading, setLoading] = useState(false);

    // E-Learning State
    const [elearningSearch, setElearningSearch] = useState('');
    const [elearningFilter, setElearningFilter] = useState('Semua');
    const [elearningTimeFilter, setElearningTimeFilter] = useState('newest');
    const [isTimeDropdownOpen, setIsTimeDropdownOpen] = useState(false);

    // Pengumuman State
    const [pengumumanTimeFilter, setPengumumanTimeFilter] = useState('newest');
    const [isPengumumanTimeDropdownOpen, setIsPengumumanTimeDropdownOpen] = useState(false);

    // Scanner State
    const [hasPermission, setHasPermission] = useState(null);
    const [scanned, setScanned] = useState(false);

    // Modal State
    const [qrModalVisible, setQrModalVisible] = useState(false);

    // Animation Value for Height (0 to 1) 
    // 0 = 50% height, 1 = 90% height
    const modalAnimation = React.useRef(new Animated.Value(0)).current;

    // Ref to track expansion state (avoiding stale closures)
    const isExpandedRef = React.useRef(false);

    const animateModal = (toValue, callback) => {
        isExpandedRef.current = toValue === 1;
        Animated.timing(modalAnimation, {
            toValue,
            duration: 300,
            useNativeDriver: false,
        }).start(callback);
    };

    // PanResponder for Swipe Down/Up
    const panResponder = React.useRef(
        PanResponder.create({
            onStartShouldSetPanResponder: () => true,
            onMoveShouldSetPanResponder: (_, gestureState) => {
                return Math.abs(gestureState.dy) > 10;
            },
            onPanResponderRelease: (_, gestureState) => {
                const isExpanded = isExpandedRef.current;

                if (gestureState.dy > 50) {
                    // Swipe Down
                    if (isExpanded) {
                        // If expanded (90%), collapse to 50% first
                        animateModal(0);
                    } else {
                        // If collapsed (50%), animate close "smoothly" causing it to shrink down
                        animateModal(-1, () => setDetailModalVisible(false));
                    }
                } else if (gestureState.dy < -50) {
                    // Swipe Up -> Expand
                    animateModal(1);
                }
            },
        })
    ).current;

    // Reset animation when modal opens/closes
    useEffect(() => {
        if (detailModalVisible) {
            // Start at 0 height (-1) and animate to 50% (0) with spring physics
            modalAnimation.setValue(-1);
            Animated.spring(modalAnimation, {
                toValue: 0,
                friction: 8,
                tension: 40,
                useNativeDriver: false
            }).start();
        } else {
            isExpandedRef.current = false;
        }
    }, [detailModalVisible]);



    // Keyboard State
    const [isKeyboardVisible, setKeyboardVisible] = useState(false);

    useEffect(() => {
        const keyboardDidShowListener = Keyboard.addListener(
            'keyboardDidShow',
            () => setKeyboardVisible(true)
        );
        const keyboardDidHideListener = Keyboard.addListener(
            'keyboardDidHide',
            () => setKeyboardVisible(false)
        );

        return () => {
            keyboardDidHideListener.remove();
            keyboardDidShowListener.remove();
        };
    }, []);

    const theme = {
        bg: isDarkMode ? '#0f172a' : '#f3f4f6',
        card: isDarkMode ? '#1e293b' : '#ffffff',
        text: isDarkMode ? '#f1f5f9' : '#1e293b',
        textMuted: isDarkMode ? '#94a3b8' : '#64748b',
        border: isDarkMode ? '#334155' : '#e2e8f0',
        primary: '#7c3aed',
        bottomNav: isDarkMode ? '#1e293b' : '#ffffff',
        navIconIdle: isDarkMode ? '#475569' : '#9ca3af',
        navIconActive: '#3b82f6'
    };

    useEffect(() => {
        const getCameraPermissions = async () => {
            const { status } = await Camera.requestCameraPermissionsAsync();
            setHasPermission(status === "granted");
        };
        getCameraPermissions();
    }, []);

    useEffect(() => {
        let interval;

        if (userData && currentView === 'dashboard') {
            fetchAttendanceStatus();
            // Auto refresh attendance status every 30 seconds to handle day change
            interval = setInterval(fetchAttendanceStatus, 30000);
        }
        if (userData && currentView === 'kehadiran') {
            fetchAttendanceHistory();
        }
        if (userData && currentView === 'monitoring') {
            fetchClassMonitoring();
        }
        if (userData && currentView === 'elearning') {
            fetchLearningMaterials();
        }
        if (userData && currentView === 'jadwal') {
            fetchJadwal();
        }
        if (userData && currentView === 'pengumuman') {
            fetchPengumuman();
            // Auto refresh pengumuman every 10 seconds
            interval = setInterval(fetchPengumuman, 10000);
        }
        if (userData && currentView === 'nilai') {
            fetchNilaiData();
        }

        return () => {
            if (interval) clearInterval(interval);
        };
    }, [userData, currentView]);

    const fetchJadwal = async () => {
        try {
            const response = await fetch(`${BASE_URL}/app/api/jadwal.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.user.id,
                    role: userData.role
                }),
            });
            const result = await response.json();
            if (result.success) {
                setJadwal(result.data);
            }
        } catch (error) {
            console.error("Fetch jadwal error:", error);
        }
    };

    const fetchLearningMaterials = async () => {
        try {
            const response = await fetch(`${BASE_URL}/app/api/materi.php`);
            const result = await response.json();
            if (result.success) {
                setLearningMaterials(result.data);
            }
        } catch (error) {
            console.error("Fetch materials error:", error);
        }
    };

    const fetchPengumuman = async () => {
        try {
            const response = await fetch(`${BASE_URL}/app/api/pengumuman.php?role=${userData ? userData.role : 'semua'}`);
            const result = await response.json();
            if (result.success) {
                setPengumumanList(result.data);
            }
        } catch (error) {
            console.error("Fetch pengumuman error:", error);
        }
    };

    const fetchClassMonitoring = async () => {
        try {
            const response = await fetch(`${BASE_URL}/app/api/wali_kelas_monitoring.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    guru_id: userData.user.id
                }),
            });
            const result = await response.json();
            if (result.success) {
                setClassMonitoring(result);
            } else {
                showCustomAlert("Akses Ditolak", result.message, [], 'error');
                setCurrentView('dashboard');
            }
        } catch (error) {
            console.error("Fetch monitoring error:", error);
        }
    };

    const fetchAttendanceHistory = async () => {
        try {
            const response = await fetch(`${BASE_URL}/app/api/attendance_history.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.user.id,
                    role: userData.role
                }),
            });
            const result = await response.json();
            if (result.success) {
                setAttendanceHistory(result.data);
            }
        } catch (error) {
            console.error("Fetch history error:", error);
        }
    };

    const fetchAttendanceStatus = async () => {
        try {
            const response = await fetch(`${BASE_URL}/app/api/attendance_status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.user.id,
                    role: userData.role
                }),
            });
            const result = await response.json();
            if (result.success) {
                setAttendanceStatus(result.data);
            }
        } catch (error) {
            console.error("Fetch attendance error:", error);
        }
    };

    const fetchSaldo = async () => {
        if (!userData) return;
        try {
            const response = await fetch(`${BASE_URL}/app/api/payment/get_saldo.php?user_id=${userData.user.id}&role=${userData.role}`);
            const result = await response.json();
            if (result.status === 'success') {
                setSaldo(result.saldo);
                setRiwayatSaldo(result.history);
            }
        } catch (error) {
            console.log("Fetch Saldo Error:", error);
        }
    };

    const handleTopUp = async () => {
        if (!topUpAmount || isNaN(topUpAmount) || parseInt(topUpAmount) < 10000) {
            showCustomAlert("Invalid Amount", "Minimal Top Up adalah Rp 10.000", [], 'warning');
            return;
        }
        setShowTopUpModal(false);

        try {
            const response = await fetch(`${BASE_URL}/app/api/payment/snap_token.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.user.id,
                    role: userData.role,
                    amount: parseInt(topUpAmount),
                    type: 'topup'
                })
            });
            const result = await response.json();
            if (result.token) {
                setPaymentUrl(result.redirect_url);
                setCurrentOrderId(result.order_id); // Save Order ID
                setShowPaymentModal(true);
            } else {
                showCustomAlert("Error", result.message || "Gagal membuat transaksi", [], 'error');
            }
        } catch (e) {
            showCustomAlert("Error", "Gagal menghubungi server pembayaran", [], 'error');
        }
    };

    const fetchNilaiData = async () => {
        if (!userData) return;
        try {
            const response = await fetch(`${BASE_URL}/app/api/nilai.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userData.user.id,
                    role: userData.role
                }),
            });
            const result = await response.json();
            if (result.success) {
                setNilaiData(result.data);
            }
        } catch (error) {
            console.error("Fetch nilai error:", error);
        }
    };

    const handleLogin = async () => {
        if (!username || !password) {
            showCustomAlert('Error', 'Mohon isi username dan password', [], 'warning');
            return;
        }

        setLoading(true);
        try {
            const response = await fetch(`${BASE_URL}/app/api/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password }),
            });

            const data = await response.json();

            if (data.success) {
                setUserData(data);
                setCurrentView('dashboard');
                setPassword('');
            } else {
                showCustomAlert('Login Gagal', data.message || 'Periksa kembali data anda', [], 'error');
            }
        } catch (error) {
            showCustomAlert('Error', 'Gagal menghubungkan ke server. Pastikan IP Address benar dan HP terhubung ke WiFi yang sama.', [], 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleLogout = () => {
        setUserData(null);
        setCurrentView('login');
        setUsername('');
        setAttendanceStatus(null);
    };

    const handleBarCodeScanned = async ({ type, data }) => {
        if (scanned) return;
        setScanned(true);

        try {
            const response = await fetch(`${BASE_URL}/app/api/scan.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    qr_code: data,
                    scanner_role: userData?.role
                }),
            });

            const result = await response.json();

            if (result.success) {
                const isPulang = result.data.type === 'pulang';
                const statusLabel = result.data.status ? result.data.status.toUpperCase() : (isPulang ? 'PULANG' : 'BELUM ABSEN');
                const timeLabel = result.data.jam || '--:--';

                // Update local points if available
                if (result.data.poin !== undefined && result.data.poin !== null) {
                    setUserData(prev => ({
                        ...prev,
                        user: { ...prev.user, poin: result.data.poin }
                    }));
                }

                showCustomAlert(
                    "✅ " + (isPulang ? "Presensi Pulang!" : "Presensi Masuk!"),
                    `${result.data.nama}\nStatus: ${statusLabel}\nJam: ${timeLabel}`,
                    [{ text: "OK", onPress: () => setScanned(false) }],
                    'success'
                );
            } else {
                showCustomAlert("❌ Gagal", result.message || "QR Code tidak valid", [{ text: "Scan Lagi", onPress: () => setScanned(false) }], 'error');
            }
        } catch (error) {
            showCustomAlert("Error", "Gagal memproses data", [{ text: "OK", onPress: () => setScanned(false) }], 'error');
        }
    };

    // View Components
    const ScreenTemplate = useCallback(({ title, subtitle, showBack = true, children, headerOverlap = true }) => (
        <View style={[styles.dashboardWrapper, { backgroundColor: theme.bg }]}>
            <View style={styles.webHeader}>
                <View style={styles.headerFlex}>
                    <View style={{ flex: 1 }}>
                        <Text style={styles.webHeaderTitle}>{title}</Text>
                        {subtitle && <Text style={styles.webHeaderSubtitle}>{subtitle}</Text>}
                    </View>
                    {showBack && (
                        <TouchableOpacity onPress={() => setCurrentView('dashboard')}>
                            <WebIcon name="back" size={24} color="white" />
                        </TouchableOpacity>
                    )}
                </View>
            </View>
            <ScrollView
                style={styles.scrollView}
                bounces={true}
                keyboardShouldPersistTaps="handled"
                keyboardDismissMode="on-drag"
            >
                <View style={[styles.mainContent, { paddingBottom: 120 }, !headerOverlap && { marginTop: 0 }]}>
                    {children}
                </View>
            </ScrollView>

            {/* Navigasi tetap tampil di semua view dashboard kecuali login/scanner */}
            {renderBottomNav()}
        </View>
    ), [theme, currentView, isDarkMode, language, isKeyboardVisible]); // Added isKeyboardVisible and other state deps directly

    const renderBottomNav = useCallback(() => {
        if (isKeyboardVisible) return null;
        return (
            <View style={[styles.webBottomNav, { backgroundColor: theme.bottomNav, borderTopColor: isDarkMode ? '#334155' : '#f1f5f9', height: 100 }]}>
                {/* Home */}
                <TouchableOpacity style={styles.navBtn} onPress={() => setCurrentView('dashboard')}>
                    <WebIcon name="home" size={28} color={currentView === 'dashboard' ? '#7c3aed' : theme.navIconIdle} />
                    <Text style={[styles.navLabelGray, { color: currentView === 'dashboard' ? '#7c3aed' : theme.navIconIdle, fontWeight: currentView === 'dashboard' ? 'bold' : 'normal' }]}>{t('home')}</Text>
                </TouchableOpacity>

                {/* QR Code - Floating */}
                <TouchableOpacity style={styles.navBtnCenter} onPress={() => setQrModalVisible(true)}>
                    <View style={[styles.qrCircleFab, { transform: [{ scale: 1.1 }] }]}>
                        <WebIcon name="qr" size={32} color="white" />
                    </View>
                    <Text style={[styles.navLabelCenter, { color: theme.text }]}>{t('qr')}</Text>
                </TouchableOpacity>

                {/* Profil */}
                <TouchableOpacity style={styles.navBtn} onPress={() => setCurrentView('profil')}>
                    <WebIcon name="user" size={28} color={currentView === 'profil' ? '#7c3aed' : theme.navIconIdle} />
                    <Text style={[styles.navLabelGray, { color: currentView === 'profil' ? '#7c3aed' : theme.navIconIdle, fontWeight: currentView === 'profil' ? 'bold' : 'normal' }]}>{t('profil')}</Text>
                </TouchableOpacity>
            </View>
        );
    }, [theme, isDarkMode, currentView, language, isKeyboardVisible]); // Fix: Added isKeyboardVisible dependency

    // UI Renders
    const renderLogin = () => (
        <KeyboardAvoidingView
            behavior={Platform.OS === "ios" ? "padding" : "height"}
            style={[styles.loginContainer, { backgroundColor: theme.bg }]}
        >
            <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', width: '100%' }}>
                <TouchableOpacity
                    style={{ position: 'absolute', top: 50, right: 20, padding: 10, backgroundColor: theme.card, borderRadius: 30, zIndex: 10, elevation: 3, shadowColor: "#000", shadowOpacity: 0.1, shadowRadius: 5 }}
                    onPress={() => setIsDarkMode(!isDarkMode)}
                >
                    <WebIcon name={isDarkMode ? "sun" : "moon"} size={22} color={theme.text} />
                </TouchableOpacity>

                <TouchableOpacity
                    style={{ position: 'absolute', top: 50, left: 20, padding: 10, backgroundColor: theme.card, borderRadius: 30, zIndex: 10, elevation: 3, shadowColor: "#000", shadowOpacity: 0.1, shadowRadius: 5, flexDirection: 'row', alignItems: 'center' }}
                    onPress={() => setLanguage(language === 'id' ? 'en' : 'id')}
                >
                    <Text style={{ fontWeight: 'bold', color: theme.text, fontSize: 12, marginRight: 5 }}>{language.toUpperCase()}</Text>
                    <WebIcon name="globe" size={18} color={theme.text} />
                </TouchableOpacity>
                <View style={{ alignItems: 'center', marginBottom: 40 }}>
                    <View style={{
                        width: 100, height: 100, borderRadius: 30,
                        backgroundColor: theme.primary,
                        justifyContent: 'center', alignItems: 'center',
                        marginBottom: 20,
                        shadowColor: theme.primary, shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.5, shadowRadius: 20, elevation: 10,
                        transform: [{ rotate: '-5deg' }]
                    }}>
                        <WebIcon name="cap" size={50} color="white" />
                    </View>
                    <Text style={{ fontSize: 32, fontWeight: '900', color: theme.text, letterSpacing: -1 }}>Gradasi Absensi</Text>
                    <Text style={{ fontSize: 16, color: theme.textMuted, marginTop: 8 }}>{t('loginSubtitle')}</Text>
                </View>

                <View style={{ width: '100%', maxWidth: 400, backgroundColor: theme.card, padding: 30, borderRadius: 30, shadowColor: "#000", shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.05, shadowRadius: 20, elevation: 5 }}>
                    <View style={{ marginBottom: 20 }}>
                        <Text style={{ fontSize: 14, fontWeight: 'bold', color: theme.text, marginBottom: 8, marginLeft: 4 }}>{t('usernameLabel')}</Text>
                        <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: isDarkMode ? '#0f172a' : '#f8fafc', borderRadius: 16, borderWidth: 1, borderColor: theme.border, paddingHorizontal: 16 }}>
                            <WebIcon name="user" size={20} color={theme.textMuted} />
                            <TextInput
                                style={{ flex: 1, padding: 16, fontSize: 16, color: theme.text }}
                                value={username}
                                onChangeText={setUsername}
                                autoCapitalize="none"
                                placeholder="Contoh: 12345"
                                placeholderTextColor={theme.textMuted}
                            />
                        </View>
                    </View>

                    <View style={{ marginBottom: 30 }}>
                        <Text style={{ fontSize: 14, fontWeight: 'bold', color: theme.text, marginBottom: 8, marginLeft: 4 }}>{t('passwordLabel')}</Text>
                        <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: isDarkMode ? '#0f172a' : '#f8fafc', borderRadius: 16, borderWidth: 1, borderColor: theme.border, paddingHorizontal: 16 }}>
                            <WebIcon name="lock" size={20} color={theme.textMuted} />
                            <TextInput
                                style={{ flex: 1, padding: 16, fontSize: 16, color: theme.text }}
                                value={password}
                                onChangeText={setPassword}
                                placeholder="••••••••"
                                placeholderTextColor={theme.textMuted}
                                autoCapitalize="none"
                                secureTextEntry={isSecureEntry}
                                textContentType="password"
                                autoComplete="password"
                                autoCorrect={false}
                                spellCheck={false}
                                keyboardType="default"
                            />
                            <TouchableOpacity onPress={() => setIsSecureEntry(!isSecureEntry)} style={{ padding: 10 }}>
                                <WebIcon name={isSecureEntry ? "eye" : "eye_off"} size={20} color={theme.textMuted} />
                            </TouchableOpacity>
                        </View>
                    </View>

                    <TouchableOpacity
                        style={{ backgroundColor: theme.primary, padding: 20, borderRadius: 16, alignItems: 'center', shadowColor: theme.primary, shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.4, shadowRadius: 15, elevation: 5 }}
                        onPress={handleLogin}
                        disabled={loading}
                    >
                        {loading ? <ActivityIndicator color="white" /> : <Text style={{ color: 'white', fontSize: 18, fontWeight: 'bold' }}>{t('loginBtn')}</Text>}
                    </TouchableOpacity>

                    <View style={{ marginTop: 25, alignItems: 'center' }}>
                        <Text style={{ color: theme.textMuted, fontSize: 13 }}>{t('lupaPassword')}</Text>
                    </View>


                </View>
            </View>

            <View style={{ paddingBottom: 20, alignItems: 'center' }}>
                <Text style={{ color: theme.textMuted, opacity: 0.8, fontSize: 12, fontWeight: 'bold' }}>© 2026 Gradasi Web</Text>
                <Text style={{ color: theme.textMuted, opacity: 0.5, fontSize: 10, marginTop: 4 }}>VERSION {CURRENT_APP_VERSION}</Text>
            </View>


        </KeyboardAvoidingView >
    );

    const renderDashboard = () => {
        const user = userData.user;
        const role = userData.role;

        const getStatusStyles = (status) => {
            if (isDarkMode) {
                switch (status?.toLowerCase()) {
                    case 'hadir': return { bg: '#064e3b', border: '#065f46', text: '#34d399', icon: 'hadir' };
                    case 'sakit': return { bg: '#1e3a8a', border: '#1e40af', text: '#60a5fa', icon: 'sakit' };
                    case 'izin': return { bg: '#713f12', border: '#854d0e', text: '#facc15', icon: 'izin' };
                    case 'terlambat': return { bg: '#7c2d12', border: '#9a3412', text: '#fb923c', icon: 'terlambat' };
                    case 'alpa': return { bg: '#7f1d1d', border: '#991b1b', text: '#f87171', icon: 'alpa' };
                    default: return { bg: '#451a03', border: '#713f12', text: '#facc15', icon: 'none' };
                }
            } else {
                switch (status?.toLowerCase()) {
                    case 'hadir': return { bg: '#dcfce7', border: '#bbf7d0', text: '#166534', icon: 'hadir' };
                    case 'sakit': return { bg: '#dbeafe', border: '#bfdbfe', text: '#1e40af', icon: 'sakit' };
                    case 'izin': return { bg: '#fef9c3', border: '#fef08a', text: '#854d0e', icon: 'izin' };
                    case 'terlambat': return { bg: '#ffedd5', border: '#fed7aa', text: '#9a3412', icon: 'terlambat' };
                    case 'alpa': return { bg: '#fee2e2', border: '#fecaca', text: '#991b1b', icon: 'alpa' };
                    default: return { bg: '#fefce8', border: '#fef08a', text: '#854d0e', icon: 'none' };
                }
            }
        };

        const statusStyle = getStatusStyles(attendanceStatus?.status);

        return (
            <View style={[styles.dashboardWrapper, { backgroundColor: theme.bg }]}>
                {/* Status Bar Background Placeholder */}
                <View style={{ height: 40, width: '100%', backgroundColor: '#7c3aed', position: 'absolute', top: 0, zIndex: 50 }} />

                <SafeAreaView style={{ flex: 1, backgroundColor: theme.bg }}>
                    <ScrollView
                        style={styles.scrollView}
                        bounces={true}
                        refreshControl={
                            <RefreshControl
                                refreshing={refreshing}
                                onRefresh={onRefresh}
                                tintColor="#fff"
                                title="Memuat ulang..."
                                titleColor="#fff"
                                colors={['#7c3aed', '#db2777']}
                                progressBackgroundColor="#ffffff"
                                progressViewOffset={60}
                            />
                        }
                    >
                        {/* Header */}
                        <View style={styles.webHeader}>
                            <View style={styles.headerFlex}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.webHeaderTitle}>{t('dashboard')} {role === 'siswa' ? (language === 'id' ? 'Siswa' : 'Student') : (language === 'id' ? 'Guru' : 'Teacher')}</Text>
                                    <Text style={styles.webHeaderSubtitle}>{user.nama} • {user.nama_kelas || (role === 'guru' ? (language === 'id' ? 'Tenaga Pendidik' : 'Teacher Staff') : 'Admin')}</Text>
                                </View>
                                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                    <TouchableOpacity style={{ marginRight: 15 }} onPress={() => setIsDarkMode(!isDarkMode)}>
                                        <WebIcon name={isDarkMode ? "sun" : "moon"} size={22} color="white" />
                                    </TouchableOpacity>
                                    <TouchableOpacity onPress={handleLogout}>
                                        <WebIcon name="logout" size={24} color="white" />
                                    </TouchableOpacity>
                                </View>
                            </View>
                        </View>

                        {/* Floating Profile Card (Overlapping Header) */}
                        <View style={{ paddingHorizontal: 20, marginTop: -25, zIndex: 10 }}>
                            <View style={styles.webProfileCard}>
                                <TouchableOpacity
                                    style={[styles.avatarCircle, { overflow: 'hidden' }]}
                                    onPress={() => setProfileModalVisible(true)}
                                >
                                    {user.foto_profil ? (
                                        <Image
                                            source={{ uri: `${BASE_URL}/uploads/${role}/${encodeURIComponent(user.foto_profil)}` }}
                                            style={styles.avatarImg}
                                            resizeMode="cover"
                                            onError={(e) => console.log("Image load error")}
                                        />
                                    ) : (
                                        <WebIcon name="user" size={32} color="white" />
                                    )}
                                </TouchableOpacity>
                                <TouchableOpacity style={styles.profileTextInfo} onPress={() => setCurrentView('profil')}>
                                    <Text style={styles.profileNameTxt}>{user.nama}</Text>
                                    <View style={styles.badgeRow}>
                                        <WebIcon name="tag" size={14} color="#ddd6fe" style={{ marginRight: 8 }} />
                                        <Text style={styles.badgeLabel}>{role === 'guru' ? 'NUPTK:' : 'NIS:'}</Text>
                                        <Text style={styles.badgeValue}>{user.nis || user.nuptk || '-'}</Text>
                                    </View>
                                    <View style={styles.badgeRow}>
                                        <WebIcon name="building" size={14} color="#ddd6fe" style={{ marginRight: 8 }} />
                                        <Text style={styles.badgeLabel}>Kelas:</Text>
                                        <Text style={styles.badgeValue}>{user.nama_kelas || '-'}</Text>
                                    </View>
                                </TouchableOpacity>
                            </View>
                        </View>

                        {/* Main Content (Status & Menu) */}
                        <View style={{ paddingHorizontal: 20, marginTop: 20, paddingBottom: 120 }}>

                            {/* Status Absensi Card */}
                            {/* Tombol Monitoring Khusus Wali Kelas */}
                            {role === 'guru' && user.nama_kelas && user.nama_kelas.includes('Wali Kelas') && (
                                <TouchableOpacity
                                    style={[styles.webStatusContainer, { backgroundColor: theme.primary, marginBottom: 20, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }]}
                                    onPress={() => setCurrentView('monitoring')}
                                >
                                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                        <View style={{ width: 48, height: 48, borderRadius: 24, backgroundColor: 'rgba(255,255,255,0.2)', justifyContent: 'center', alignItems: 'center', marginRight: 16 }}>
                                            <WebIcon name="eye" size={24} color="white" />
                                        </View>
                                        <View>
                                            <Text style={{ color: 'white', fontSize: 18, fontWeight: 'bold' }}>{t('monitoringKelas')}</Text>
                                            <Text style={{ color: '#ddd6fe', fontSize: 13 }}>{t('pantauSiswa')}</Text>
                                        </View>
                                    </View>
                                    <WebIcon name="back" size={20} color="white" style={{ transform: [{ rotate: '180deg' }] }} />
                                </TouchableOpacity>
                            )}

                            <View style={[styles.webStatusContainer, { backgroundColor: theme.card }]}>
                                <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 5 }}>
                                    <Text style={[styles.sectionTitleWeb, { color: theme.text }]}>{t('statusAbsensi')}</Text>
                                    <View style={[styles.typeBadge, { backgroundColor: attendanceStatus?.jam_keluar ? '#dbeafe' : (attendanceStatus?.jam_masuk ? '#fef3c7' : '#f3f4f6') }]}>
                                        <Text style={[styles.typeBadgeTxt, { color: attendanceStatus?.jam_keluar ? '#1e40af' : (attendanceStatus?.jam_masuk ? '#92400e' : '#6b7280') }]}>
                                            {attendanceStatus?.jam_keluar ? t('sudahPulang') : (attendanceStatus?.jam_masuk ? t('sudahMasuk') : t('belumAbsen'))}
                                        </Text>
                                    </View>
                                </View>
                                <Text style={styles.dateLabelWeb}>{new Date().toLocaleDateString(language === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'long', year: 'numeric' })}</Text>

                                <View style={[styles.statusInnerBox, { backgroundColor: statusStyle.bg, borderColor: statusStyle.border, flexDirection: 'column', alignItems: 'flex-start' }]}>
                                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', width: '100%', alignItems: 'center' }}>
                                        <View>
                                            <Text style={[styles.statusMainLabel, { color: statusStyle.text }]}>
                                                {attendanceStatus ? (translations[language][attendanceStatus.status] ? translations[language][attendanceStatus.status].toUpperCase() : attendanceStatus.status.toUpperCase()) : t('belumAbsen').toUpperCase()}
                                            </Text>
                                            <Text style={[styles.roleLabel, { color: isDarkMode ? '#94a3b8' : '#64748b' }]}>{role.charAt(0).toUpperCase() + role.slice(1)} • {user.nama}</Text>
                                        </View>
                                        <View style={[styles.checkCircle, { backgroundColor: attendanceStatus ? '#16a34a' : '#facc15' }]}>
                                            <Text style={styles.checkIcon}>{attendanceStatus ? '✓' : '!'}</Text>
                                        </View>
                                    </View>

                                    <View style={{ width: '100%', height: 1, backgroundColor: isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)', marginVertical: 15 }} />

                                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', width: '100%' }}>
                                        <View style={styles.timeInfoItem}>
                                            <Text style={styles.timeLabel}>Jam Masuk</Text>
                                            <Text style={[styles.timeValue, { color: theme.text }]}>{attendanceStatus?.jam_masuk ? attendanceStatus.jam_masuk.substring(0, 5) : '--:--'}</Text>
                                        </View>
                                        <View style={styles.timeInfoItem}>
                                            <Text style={styles.timeLabel}>Jam Pulang</Text>
                                            <Text style={[styles.timeValue, { color: theme.text }]}>{attendanceStatus?.jam_keluar ? attendanceStatus.jam_keluar.substring(0, 5) : '--:--'}</Text>
                                        </View>
                                    </View>
                                </View>

                            </View>

                            {/* Menu Grid */}
                            <View style={styles.webGrid}>
                                <View style={styles.webRow}>
                                    <WebMenuItem iconName="clipboard" iconColor="#2563eb" iconBg="#dbeafe" title={t('kehadiran')} sub={t('riwayatAbsensi')} onPress={() => setCurrentView('kehadiran')} theme={theme} isDarkMode={isDarkMode} />
                                    <WebMenuItem iconName="user" iconColor="#7c3aed" iconBg="#f5f3ff" title={t('profil')} sub={t('infoPribadi')} onPress={() => setCurrentView('profil')} theme={theme} isDarkMode={isDarkMode} />
                                </View>
                                <View style={styles.webRow}>
                                    <WebMenuItem iconName="card" iconColor="#16a34a" iconBg="#dcfce7" title={t('pembayaran')} sub={t('statusSpp')} onPress={() => setCurrentView('pembayaran')} theme={theme} isDarkMode={isDarkMode} />
                                    <WebMenuItem iconName="speaker" iconColor="#ea580c" iconBg="#ffedd5" title={t('pengumuman')} sub={t('infoTerbaru')} onPress={() => setCurrentView('pengumuman')} theme={theme} isDarkMode={isDarkMode} />
                                </View>
                                <View style={styles.webRow}>
                                    <WebMenuItem iconName="book" iconColor="#e11d48" iconBg="#ffe4e6" title={t('jadwalPelajaran')} sub={t('lihatJadwal')} onPress={() => setCurrentView('jadwal')} theme={theme} isDarkMode={isDarkMode} />
                                    <WebMenuItem iconName="globe" iconColor="#0891b2" iconBg="#cffafe" title={t('elearning')} sub={t('aksesMateri')} onPress={() => setCurrentView('elearning')} theme={theme} isDarkMode={isDarkMode} />
                                </View>
                                <View style={styles.webRow}>
                                    <WebMenuItem iconName="fileText" iconColor="#ec4899" iconBg="#fce7f3" title={t('perizinan')} sub={t('izinSakit')} onPress={() => setCurrentView('perizinan')} theme={theme} isDarkMode={isDarkMode} />
                                    <WebMenuItem iconName="barChart" iconColor="#8b5cf6" iconBg="#ede9fe" title={t('nilai')} sub={t('raportAkademik')} onPress={() => setCurrentView('nilai')} theme={theme} isDarkMode={isDarkMode} />
                                </View>
                            </View>
                        </View>
                    </ScrollView>

                    {/* Bottom Nav */}
                    {renderBottomNav()}
                </SafeAreaView>
            </View >
        );
    };

    const WebMenuItem = ({ iconName, iconColor, iconBg, title, sub, onPress, theme, isDarkMode }) => (
        <TouchableOpacity style={[styles.webMenuCard, { backgroundColor: theme.card }]} onPress={onPress}>
            <View style={[styles.iconCircleWeb, { backgroundColor: isDarkMode ? iconColor + '20' : iconBg }]}>
                <WebIcon name={iconName} size={28} color={isDarkMode ? iconColor : iconColor} />
            </View>
            <Text style={[styles.menuTitleWeb, { color: theme.text }]}>{title}</Text>
            <Text style={[styles.menuSubWeb, { color: theme.textMuted }]}>{sub}</Text>
        </TouchableOpacity>
    );

    const renderScanner = () => {
        if (hasPermission === null) return <Text style={{ color: theme.text }}>Requesting for camera permission</Text>;
        if (hasPermission === false) return <Text style={{ color: theme.text }}>No access to camera</Text>;
        return (
            <View style={styles.scannerContainer}>
                <CameraView style={StyleSheet.absoluteFillObject} onBarcodeScanned={scanned ? undefined : handleBarCodeScanned} barcodeScannerSettings={{ barcodeTypes: ["qr"] }} />
                <View style={styles.overlayScanner}>
                    <Text style={styles.scanText}>{t('arahkanKamera')}</Text>
                    <TouchableOpacity style={styles.closeBtnScanner} onPress={() => setCurrentView('dashboard')}>
                        <Text style={styles.closeTxtScanner}>{t('batal')}</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    };

    const renderProfil = () => {
        const user = userData?.user;
        const role = userData?.role;
        return (
            <ScreenTemplate title={t('profilSaya')} subtitle={t('infoPribadi')} headerOverlap={false}>
                {/* Main Profile Image Card */}
                <View style={[styles.webStatusContainer, { backgroundColor: theme.card, alignItems: 'center', padding: 40, marginTop: 20 }]}>
                    <View style={[styles.avatarCircleLarge, { borderColor: 'white', elevation: 10, shadowColor: '#000', shadowOpacity: 0.1, shadowRadius: 10 }]}>
                        {user.foto_profil ? (
                            <Image
                                source={{ uri: `${BASE_URL}/uploads/${role}/${encodeURIComponent(user.foto_profil)}` }}
                                style={styles.avatarImgLarge}
                                resizeMode="cover"
                                onError={(e) => console.log('Profile image error')}
                            />
                        ) : (
                            <WebIcon name="user" size={80} color={isDarkMode ? theme.textMuted : "#cbd5e1"} />
                        )}
                    </View>
                    <Text style={[styles.profileNameTxt, { color: theme.text, fontSize: 32, marginTop: 24, fontWeight: '900' }]}>{user.nama}</Text>
                    <Text style={[styles.webHeaderSubtitle, { color: theme.textMuted, fontSize: 18, marginTop: 4 }]}>{user.nama_kelas || '-'}</Text>
                </View>

                <View style={{ marginTop: 20 }}>
                    <InfoCard icon="tag" iconBg="#eef2ff" iconColor="#4f46e5" label={role === 'guru' ? t('nuptk') : t('nis')} value={user.nis || user.nuptk || '-'} theme={theme} isDarkMode={isDarkMode} />
                    <InfoCard icon="building" iconBg="#f5f3ff" iconColor="#7c3aed" label={t('kelas')} value={user.nama_kelas || '-'} theme={theme} isDarkMode={isDarkMode} />
                    <InfoCard icon="qr" iconBg="#f0fdf4" iconColor="#16a34a" label={t('kodeQr')} value={user.kode_qr || '-'} theme={theme} isDarkMode={isDarkMode} mono />
                    <InfoCard icon="calendar" iconBg="#eff6ff" iconColor="#3b82f6" label={t('terdaftarSejak')} value={new Date(user.created_at || new Date()).toLocaleDateString(language === 'id' ? 'id-ID' : 'en-GB', { day: 'numeric', month: 'long', year: 'numeric' })} theme={theme} isDarkMode={isDarkMode} />
                </View>

                {/* Language Toggle Button */}
                <TouchableOpacity
                    style={[styles.logoutBtnFull, { backgroundColor: isDarkMode ? '#334155' : '#e2e8f0', marginBottom: 15, marginTop: 10, shadowColor: 'transparent' }]}
                    onPress={async () => {
                        const newLang = language === 'id' ? 'en' : 'id';
                        setLanguage(newLang);
                        try {
                            await AsyncStorage.setItem('user-language', newLang);
                        } catch (error) {
                            console.log('Error saving language:', error);
                        }
                    }}
                >
                    <Text style={{ fontSize: 24, marginRight: 10 }}>🌐</Text>
                    <Text style={[styles.logoutBtnText, { color: theme.text }]}>
                        {language === 'id' ? 'Ganti Bahasa (ID)' : 'Switch Language (EN)'}
                    </Text>
                </TouchableOpacity>

                {/* Detail Lengkap Button */}
                <TouchableOpacity
                    style={[styles.logoutBtnFull, { backgroundColor: theme.card, marginBottom: 15, shadowColor: 'transparent', borderWidth: 1, borderColor: isDarkMode ? '#334155' : '#e2e8f0' }]}
                    onPress={() => setMenuModalVisible(true)}
                >
                    <WebIcon name="fileText" size={20} color={theme.text} style={{ marginRight: 10 }} />
                    <Text style={[styles.logoutBtnText, { color: theme.text }]}>Detail Lengkap</Text>
                </TouchableOpacity>

                {/* Scanner Button for Guru */}
                {
                    role === 'guru' && (
                        <TouchableOpacity
                            style={[styles.logoutBtnFull, { backgroundColor: theme.primary, marginBottom: 15, marginTop: 10, shadowColor: theme.primary }]}
                            onPress={() => setCurrentView('scanner')}
                        >
                            <WebIcon name="search" size={20} color="white" style={{ marginRight: 10 }} />
                            <Text style={styles.logoutBtnText}>{t('scanQrSiswa')}</Text>
                        </TouchableOpacity>
                    )
                }

                {/* About App Button */}
                <TouchableOpacity
                    style={[styles.logoutBtnFull, { backgroundColor: theme.card, marginBottom: 15, marginTop: 10, shadowColor: 'transparent', borderWidth: 1, borderColor: isDarkMode ? '#334155' : '#e2e8f0' }]}
                    onPress={() => setCurrentView('tentang')}
                >
                    <WebIcon name="info" size={20} color={theme.text} style={{ marginRight: 10 }} />
                    <Text style={[styles.logoutBtnText, { color: theme.text }]}>{t('tentangAplikasi')}</Text>
                </TouchableOpacity>

                {/* Logout Button */}
                <TouchableOpacity style={styles.logoutBtnFull} onPress={handleLogout}>
                    <WebIcon name="logout" size={20} color="white" style={{ marginRight: 10 }} />
                    <Text style={styles.logoutBtnText}>{t('keluarAkun')}</Text>
                </TouchableOpacity>
            </ScreenTemplate >
        );
    };

    const renderElearning = () => {
        // Filter Logic
        const uniqueSubjects = ['Semua', ...new Set(learningMaterials.map(item => item.nama_mapel).filter(Boolean))];

        const filteredMaterials = learningMaterials.filter(item => {
            const matchesSearch = item.judul.toLowerCase().includes(elearningSearch.toLowerCase()) ||
                (item.deskripsi && item.deskripsi.toLowerCase().includes(elearningSearch.toLowerCase()));
            const matchesFilter = elearningFilter === 'Semua' || item.nama_mapel === elearningFilter;

            // Time Filter Logic
            let matchesTime = true;
            const itemDate = new Date(item.created_at);
            const now = new Date();

            if (elearningTimeFilter === 'thisWeek') {
                const oneWeekAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                matchesTime = itemDate >= oneWeekAgo;
            } else if (elearningTimeFilter === 'thisMonth') {
                matchesTime = itemDate.getMonth() === now.getMonth() && itemDate.getFullYear() === now.getFullYear();
            }

            return matchesSearch && matchesFilter && matchesTime;
        }).sort((a, b) => {
            // Sorting Logic
            const dateA = new Date(a.created_at);
            const dateB = new Date(b.created_at);
            if (elearningTimeFilter === 'oldest') {
                return dateA - dateB;
            }
            return dateB - dateA; // Default newest
        });

        const fetchComments = async (materiId) => {
            setIsCommentLoading(true);
            try {
                const response = await fetch(`${BASE_URL}/app/api/komentar.php?materi_id=${materiId}`);
                const data = await response.json();
                if (data.success) {
                    setComments(data.data);
                } else {
                    showCustomAlert('Error', 'Gagal memuat komentar', [], 'error');
                }
            } catch (error) {
                console.error(error);
                showCustomAlert('Error', 'Terjadi kesalahan koneksi', [], 'error');
            } finally {
                setIsCommentLoading(false);
            }
        };

        const handlePostComment = async () => {
            if (!newComment.trim()) return;

            setIsCommentLoading(true);

            const payload = {
                materi_id: currentMateriId,
                user_id: userData.user.id,
                role: userData.role,
                komentar: newComment
            };
            console.log('Sending comment:', payload);

            try {
                const response = await fetch(`${BASE_URL}/app/api/komentar.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (data.success) {
                    setNewComment('');
                    fetchComments(currentMateriId); // Refresh comments
                } else {
                    showCustomAlert('Error', 'Gagal mengirim komentar: ' + data.message, [], 'error');
                }
            } catch (error) {
                console.error(error);
                showCustomAlert('Error', 'Terjadi kesalahan koneksi', [], 'error');
            } finally {
                setIsCommentLoading(false);
            }
        };

        const openCommentModal = (materiId) => {
            setCurrentMateriId(materiId);
            setComments([]);
            setCommentModalVisible(true);
            fetchComments(materiId);
        };

        const handleEditComment = (commentId, currentText) => {
            setEditingCommentId(commentId);
            setEditingCommentText(currentText);
        };

        const handleUpdateComment = async () => {
            if (!editingCommentText.trim()) return;

            setIsCommentLoading(true);
            try {
                const response = await fetch(`${BASE_URL}/app/api/komentar.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: editingCommentId,
                        user_id: userData.user.id,
                        role: userData.role,
                        komentar: editingCommentText
                    }),
                });
                const data = await response.json();
                if (data.success) {
                    setEditingCommentId(null);
                    setEditingCommentText('');
                    fetchComments(currentMateriId);
                } else {
                    showCustomAlert('Error', 'Gagal mengupdate komentar: ' + data.message, [], 'error');
                }
            } catch (error) {
                console.error(error);
                showCustomAlert('Error', 'Terjadi kesalahan koneksi', [], 'error');
            } finally {
                setIsCommentLoading(false);
            }
        };

        const handleDeleteComment = async (commentId) => {
            showCustomAlert(
                'Konfirmasi',
                'Apakah Anda yakin ingin menghapus komentar ini?',
                [
                    { text: 'Batal', style: 'cancel' },
                    {
                        text: 'Hapus',
                        style: 'destructive',
                        onPress: async () => {
                            setIsCommentLoading(true);
                            try {
                                const response = await fetch(`${BASE_URL}/app/api/komentar.php`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        id: commentId,
                                        user_id: userData.user.id,
                                        role: userData.role
                                    }),
                                });
                                const data = await response.json();
                                if (data.success) {
                                    fetchComments(currentMateriId);
                                } else {
                                    showCustomAlert('Error', 'Gagal menghapus komentar: ' + data.message, [], 'error');
                                }
                            } catch (error) {
                                console.error(error);
                                showCustomAlert('Error', 'Terjadi kesalahan koneksi', [], 'error');
                            } finally {
                                setIsCommentLoading(false);
                            }
                        }
                    }
                ],
                'warning'
            );
        };
        return (
            <View style={[styles.dashboardWrapper, { backgroundColor: theme.bg }]}>
                {/* Fixed Header Manual */}
                <View style={styles.webHeader}>
                    <View style={styles.headerFlex}>
                        <View style={{ flex: 1 }}>
                            <Text style={styles.webHeaderTitle}>{t('elearning')}</Text>
                            <Text style={styles.webHeaderSubtitle}>{t('aksesMateri')}</Text>
                        </View>
                        <TouchableOpacity onPress={() => setCurrentView('dashboard')}>
                            <WebIcon name="back" size={24} color="white" />
                        </TouchableOpacity>
                    </View>
                </View>

                {/* Scrollable Content */}
                <ScrollView style={styles.scrollView} bounces={true}>
                    <View style={[styles.mainContent, { paddingBottom: 120, marginTop: 0 }]}>
                        {/* Search Bar */}
                        <View style={{ marginBottom: 15, marginTop: 10 }}>
                            <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: theme.card, borderRadius: 12, paddingHorizontal: 12, borderWidth: 1, borderColor: theme.border }}>
                                <WebIcon name="search" size={20} color={theme.textMuted} />
                                <TextInput
                                    style={{ flex: 1, padding: 12, color: theme.text, fontSize: 14 }}
                                    placeholder={t('cariMateri')}
                                    placeholderTextColor={theme.textMuted}
                                    value={elearningSearch}
                                    onChangeText={setElearningSearch}
                                />
                                {elearningSearch.length > 0 && (
                                    <TouchableOpacity onPress={() => setElearningSearch('')}>
                                        <WebIcon name="close" size={16} color={theme.textMuted} />
                                    </TouchableOpacity>
                                )}
                            </View>
                        </View>



                        {/* Time Filter Dropdown */}
                        <View style={{ zIndex: 1000, marginBottom: 15 }}>
                            <TouchableOpacity
                                style={{
                                    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
                                    backgroundColor: theme.card, padding: 12, borderRadius: 12,
                                    borderWidth: 1, borderColor: theme.border
                                }}
                                onPress={() => setIsTimeDropdownOpen(!isTimeDropdownOpen)}
                            >
                                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                    <WebIcon name="calendar" size={16} color={theme.textMuted} style={{ marginRight: 8 }} />
                                    <Text style={{ color: theme.text }}>{t('filter')}: {t(elearningTimeFilter)}</Text>
                                </View>
                                <WebIcon name="back" size={16} color={theme.textMuted} style={{ transform: [{ rotate: isTimeDropdownOpen ? '90deg' : '-90deg' }] }} />
                            </TouchableOpacity>

                            {isTimeDropdownOpen && (
                                <View style={{
                                    position: 'absolute', top: 50, left: 0, right: 0,
                                    backgroundColor: theme.card, borderRadius: 12,
                                    borderWidth: 1, borderColor: theme.border,
                                    shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 10, elevation: 5
                                }}>
                                    {['newest', 'oldest', 'thisWeek', 'thisMonth'].map((option, idx) => (
                                        <TouchableOpacity
                                            key={option}
                                            style={{
                                                padding: 12,
                                                borderBottomWidth: idx === 3 ? 0 : 1,
                                                borderBottomColor: theme.border,
                                                flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'
                                            }}
                                            onPress={() => {
                                                setElearningTimeFilter(option);
                                                setIsTimeDropdownOpen(false);
                                            }}
                                        >
                                            <Text style={{ color: elearningTimeFilter === option ? theme.primary : theme.text, fontWeight: elearningTimeFilter === option ? 'bold' : 'normal' }}>
                                                {t(option)}
                                            </Text>
                                            {elearningTimeFilter === option && <WebIcon name="tag" size={14} color={theme.primary} />}
                                        </TouchableOpacity>
                                    ))}
                                </View>
                            )}
                        </View>

                        {/* Filter Tags */}
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 20 }}>
                            {uniqueSubjects.map((subject, index) => (
                                <TouchableOpacity
                                    key={index}
                                    style={{
                                        paddingHorizontal: 16, paddingVertical: 8,
                                        borderRadius: 20,
                                        backgroundColor: elearningFilter === subject ? theme.primary : theme.card,
                                        marginRight: 10,
                                        borderWidth: 1,
                                        borderColor: elearningFilter === subject ? theme.primary : theme.border
                                    }}
                                    onPress={() => setElearningFilter(subject)}
                                >
                                    <Text style={{ color: elearningFilter === subject ? 'white' : theme.text, fontWeight: '600', fontSize: 13 }}>{subject}</Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>

                        {/* Results List */}
                        <View>
                            {filteredMaterials.length === 0 ? (
                                <View style={{ padding: 40, alignItems: 'center' }}>
                                    <WebIcon name="book" size={40} color={theme.textMuted} />
                                    <Text style={{ color: theme.textMuted, marginTop: 15 }}>{t('tidakAdaMateri')}</Text>
                                </View>
                            ) : (
                                filteredMaterials.map((item) => (
                                    <View key={item.id} style={[styles.infoItemCard, { backgroundColor: theme.card, padding: 16, flexDirection: 'column', alignItems: 'flex-start' }]}>
                                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', width: '100%', marginBottom: 8 }}>
                                            <View style={{ flexDirection: 'row', alignItems: 'center', flexWrap: 'wrap' }}>
                                                <View style={{ paddingHorizontal: 8, paddingVertical: 2, backgroundColor: '#e0e7ff', borderRadius: 4, marginRight: 8, marginBottom: 4 }}>
                                                    <Text style={{ fontSize: 10, fontWeight: 'bold', color: '#4338ca' }}>{(item.tipe_file || 'FILE').toUpperCase()}</Text>
                                                </View>
                                                {item.nama_mapel && (
                                                    <View style={{ paddingHorizontal: 8, paddingVertical: 2, backgroundColor: isDarkMode ? '#064e3b' : '#dcfce7', borderRadius: 4, marginRight: 8, marginBottom: 4 }}>
                                                        <Text style={{ fontSize: 10, fontWeight: 'bold', color: isDarkMode ? '#86efac' : '#166534' }}>{item.nama_mapel.toUpperCase()}</Text>
                                                    </View>
                                                )}
                                                <Text style={{ fontSize: 12, color: theme.textMuted, marginBottom: 4 }}>{new Date(item.created_at).toLocaleDateString()}</Text>
                                            </View>
                                        </View>

                                        <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text, marginBottom: 4 }}>{item.judul}</Text>
                                        <Text style={{ fontSize: 13, color: theme.textMuted, marginBottom: 12, lineHeight: 18 }}>{item.deskripsi || 'Tidak ada deskripsi'}</Text>

                                        <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 16 }}>
                                            <WebIcon name="user" size={14} color={theme.textMuted} style={{ marginRight: 4 }} />
                                            <Text style={{ fontSize: 12, color: theme.textMuted, marginRight: 12 }}>{item.nama_guru}</Text>
                                            {item.nama_mapel && (
                                                <>
                                                    <WebIcon name="book" size={14} color={theme.textMuted} style={{ marginRight: 4 }} />
                                                    <Text style={{ fontSize: 12, color: theme.textMuted }}>{item.nama_mapel}</Text>
                                                </>
                                            )}
                                        </View>

                                        <View style={{ flexDirection: 'row', marginTop: 8 }}>
                                            <TouchableOpacity
                                                style={{ flex: 1, padding: 12, backgroundColor: theme.bg, borderWidth: 1, borderColor: theme.border, borderRadius: 12, alignItems: 'center', flexDirection: 'row', justifyContent: 'center', marginRight: 8 }}
                                                onPress={() => {
                                                    const url = `${BASE_URL}/uploads/materi/${item.file_path}`;
                                                    Linking.openURL(url).catch(err => showCustomAlert('Error', 'Gagal membuka: ' + err, [], 'error'));
                                                }}
                                            >
                                                <WebIcon name="eye" size={16} color={theme.text} style={{ marginRight: 8 }} />
                                                <Text style={{ color: theme.text, fontWeight: '600' }}>{t('lihat')}</Text>
                                            </TouchableOpacity>

                                            <TouchableOpacity
                                                style={{ flex: 1, padding: 12, backgroundColor: isDarkMode ? '#1e3a8a' : '#eff6ff', borderRadius: 12, alignItems: 'center', flexDirection: 'row', justifyContent: 'center' }}
                                                onPress={() => {
                                                    const url = `${BASE_URL}/uploads/materi/${item.file_path}`;
                                                    Linking.openURL(url).catch(err => showCustomAlert('Error', 'Gagal membuka: ' + err, [], 'error'));
                                                }}
                                            >
                                                <WebIcon name="download" size={16} color={isDarkMode ? '#93c5fd' : '#2563eb'} style={{ marginRight: 8 }} />
                                                <Text style={{ color: isDarkMode ? '#93c5fd' : '#2563eb', fontWeight: 'bold' }}>{t('download')}</Text>
                                            </TouchableOpacity>


                                            <TouchableOpacity
                                                style={{ flex: 1, padding: 12, backgroundColor: theme.bg, borderWidth: 1, borderColor: theme.border, borderRadius: 12, alignItems: 'center', flexDirection: 'row', justifyContent: 'center', marginLeft: 8 }}
                                                onPress={() => openCommentModal(item.id)}
                                            >
                                                <WebIcon name="chat" size={16} color={theme.textMuted} style={{ marginRight: 8 }} />
                                                <Text style={{ color: theme.textMuted, fontWeight: '600' }}>Komentar</Text>
                                            </TouchableOpacity>
                                        </View>
                                    </View>
                                ))
                            )}
                        </View>
                    </View >
                </ScrollView >

                {/* COMMENT MODAL */}
                <Modal
                    visible={commentModalVisible}
                    transparent={true}
                    animationType="slide"
                    onRequestClose={() => setCommentModalVisible(false)}
                >
                    <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' }}>
                        <View style={{ backgroundColor: theme.card, borderTopLeftRadius: 24, borderTopRightRadius: 24, height: '80%', padding: 20 }}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                                <Text style={{ fontSize: 18, fontWeight: 'bold', color: theme.text }}>Komentar</Text>
                                <TouchableOpacity onPress={() => setCommentModalVisible(false)}>
                                    <WebIcon name="close" size={24} color={theme.text} />
                                </TouchableOpacity>
                            </View>

                            <ScrollView style={{ flex: 1, marginBottom: 10 }}>
                                {isCommentLoading && comments.length === 0 ? (
                                    <ActivityIndicator size="large" color={theme.primary} style={{ marginTop: 20 }} />
                                ) : comments.length === 0 ? (
                                    <Text style={{ textAlign: 'center', color: theme.textMuted, marginTop: 20 }}>Belum ada komentar.</Text>
                                ) : (
                                    comments.map((item, index) => {
                                        const isOwner = item.user_id === userData.user.id && item.role === userData.role;
                                        const isEditing = editingCommentId === item.id;

                                        return (
                                            <View key={index} style={{ marginBottom: 15, flexDirection: 'row' }}>
                                                <Image
                                                    source={{ uri: `${BASE_URL}/uploads/${item.role === 'siswa' ? 'siswa' : 'guru'}/${item.foto_profil}` }}
                                                    style={{ width: 40, height: 40, borderRadius: 20, marginRight: 10, backgroundColor: '#f1f5f9' }}
                                                />
                                                <View style={{ flex: 1 }}>
                                                    <TouchableOpacity
                                                        onLongPress={() => {
                                                            if (isOwner && !isEditing) {
                                                                setSelectedCommentForMenu(item);
                                                                setLongPressMenuVisible(true);
                                                            }
                                                        }}
                                                        delayLongPress={500}
                                                        activeOpacity={0.7}
                                                    >
                                                        <View style={{ backgroundColor: isDarkMode ? '#1e293b' : '#f1f5f9', padding: 10, borderRadius: 12, borderTopLeftRadius: 0 }}>
                                                            <Text style={{ fontWeight: 'bold', color: theme.text, fontSize: 13 }}>{item.nama_user} <Text style={{ fontWeight: 'normal', fontSize: 11, color: theme.textMuted }}>• {item.role.toUpperCase()}</Text></Text>

                                                            {isEditing ? (
                                                                <View style={{ marginTop: 8 }}>
                                                                    <TextInput
                                                                        style={{ backgroundColor: isDarkMode ? '#0f172a' : 'white', borderRadius: 8, padding: 8, color: theme.text, borderWidth: 1, borderColor: theme.border }}
                                                                        value={editingCommentText}
                                                                        onChangeText={setEditingCommentText}
                                                                        multiline
                                                                        autoFocus
                                                                    />
                                                                    <View style={{ flexDirection: 'row', marginTop: 8, gap: 8 }}>
                                                                        <TouchableOpacity
                                                                            style={{ flex: 1, backgroundColor: theme.primary, padding: 8, borderRadius: 8, alignItems: 'center' }}
                                                                            onPress={handleUpdateComment}
                                                                        >
                                                                            <Text style={{ color: 'white', fontWeight: 'bold', fontSize: 12 }}>Simpan</Text>
                                                                        </TouchableOpacity>
                                                                        <TouchableOpacity
                                                                            style={{ flex: 1, backgroundColor: isDarkMode ? '#334155' : '#e2e8f0', padding: 8, borderRadius: 8, alignItems: 'center' }}
                                                                            onPress={() => { setEditingCommentId(null); setEditingCommentText(''); }}
                                                                        >
                                                                            <Text style={{ color: theme.text, fontWeight: 'bold', fontSize: 12 }}>Batal</Text>
                                                                        </TouchableOpacity>
                                                                    </View>
                                                                </View>
                                                            ) : (
                                                                <Text style={{ color: theme.text, marginTop: 4 }}>{item.komentar}</Text>
                                                            )}
                                                        </View>
                                                    </TouchableOpacity>
                                                    <Text style={{ color: theme.textMuted, fontSize: 10, marginTop: 4, marginLeft: 5 }}>{new Date(item.created_at).toLocaleString()}</Text>
                                                </View>
                                            </View>
                                        );
                                    })
                                )}
                            </ScrollView>

                            <View style={{ flexDirection: 'row', alignItems: 'center', borderTopWidth: 1, borderTopColor: theme.border, paddingTop: 10 }}>
                                <TextInput
                                    style={{ flex: 1, backgroundColor: isDarkMode ? '#1e293b' : '#f1f5f9', borderRadius: 20, paddingHorizontal: 15, paddingVertical: 10, color: theme.text, marginRight: 10 }}
                                    placeholder="Tulis komentar..."
                                    placeholderTextColor={theme.textMuted}
                                    value={newComment}
                                    onChangeText={setNewComment}
                                />
                                <TouchableOpacity
                                    style={{ backgroundColor: theme.primary, width: 44, height: 44, borderRadius: 22, justifyContent: 'center', alignItems: 'center' }}
                                    onPress={handlePostComment}
                                    disabled={isCommentLoading}
                                >
                                    {isCommentLoading ? <ActivityIndicator size="small" color="white" /> : <WebIcon name="back" size={20} color="white" style={{ transform: [{ rotate: '180deg' }] }} />}
                                </TouchableOpacity>
                            </View>
                        </View>
                    </View>
                </Modal>

                {/* FLOATING MENU FOR LONG PRESS */}
                <Modal
                    visible={longPressMenuVisible}
                    transparent={true}
                    animationType="fade"
                    onRequestClose={() => setLongPressMenuVisible(false)}
                >
                    <TouchableOpacity
                        style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'center', alignItems: 'center' }}
                        activeOpacity={1}
                        onPress={() => setLongPressMenuVisible(false)}
                    >
                        <View style={{
                            backgroundColor: theme.card,
                            borderRadius: 20,
                            padding: 4,
                            minWidth: 180,
                            shadowColor: "#000",
                            shadowOffset: { width: 0, height: 8 },
                            shadowOpacity: 0.25,
                            shadowRadius: 16,
                            elevation: 12,
                            borderWidth: 1,
                            borderColor: isDarkMode ? '#334155' : '#e2e8f0'
                        }}>
                            <TouchableOpacity
                                style={{
                                    flexDirection: 'row',
                                    alignItems: 'center',
                                    padding: 16,
                                    borderRadius: 16,
                                    backgroundColor: isDarkMode ? 'transparent' : 'transparent'
                                }}
                                onPress={() => {
                                    setLongPressMenuVisible(false);
                                    if (selectedCommentForMenu) {
                                        handleEditComment(selectedCommentForMenu.id, selectedCommentForMenu.komentar);
                                    }
                                }}
                            >
                                <View style={{
                                    width: 36,
                                    height: 36,
                                    borderRadius: 10,
                                    backgroundColor: isDarkMode ? '#1e40af20' : '#dbeafe',
                                    justifyContent: 'center',
                                    alignItems: 'center',
                                    marginRight: 12
                                }}>
                                    <WebIcon name="create" size={18} color={theme.primary} />
                                </View>
                                <Text style={{ color: theme.text, fontSize: 16, fontWeight: '600', flex: 1 }}>Edit Komentar</Text>
                            </TouchableOpacity>

                            <View style={{ height: 1, backgroundColor: theme.border, marginHorizontal: 12 }} />

                            <TouchableOpacity
                                style={{
                                    flexDirection: 'row',
                                    alignItems: 'center',
                                    padding: 16,
                                    borderRadius: 16
                                }}
                                onPress={() => {
                                    setLongPressMenuVisible(false);
                                    if (selectedCommentForMenu) {
                                        handleDeleteComment(selectedCommentForMenu.id);
                                    }
                                }}
                            >
                                <View style={{
                                    width: 36,
                                    height: 36,
                                    borderRadius: 10,
                                    backgroundColor: isDarkMode ? '#7f1d1d20' : '#fee2e2',
                                    justifyContent: 'center',
                                    alignItems: 'center',
                                    marginRight: 12
                                }}>
                                    <WebIcon name="delete" size={18} color="#ef4444" />
                                </View>
                                <Text style={{ color: '#ef4444', fontSize: 16, fontWeight: '600', flex: 1 }}>Hapus Komentar</Text>
                            </TouchableOpacity>
                        </View>
                    </TouchableOpacity>
                </Modal>

                {renderBottomNav()}
            </View >
        );
    };

    const renderTentangAplikasi = () => {
        return (
            <ScreenTemplate title={t('tentangAplikasi')} subtitle={t('detailAplikasi')} headerOverlap={false}>
                {/* Hero Section */}
                <View style={{ alignItems: 'center', marginTop: 20, marginBottom: 30 }}>
                    <View style={{
                        width: 120, height: 120, borderRadius: 35,
                        backgroundColor: theme.primary,
                        justifyContent: 'center', alignItems: 'center',
                        marginBottom: 20,
                        shadowColor: theme.primary, shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.4, shadowRadius: 20, elevation: 15,
                        transform: [{ rotate: '-3deg' }]
                    }}>
                        <WebIcon name="cap" size={60} color="white" />
                    </View>
                    <Text style={{ fontSize: 28, fontWeight: '900', color: theme.text, letterSpacing: -1 }}>Absensi Digital</Text>
                    <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 8, backgroundColor: isDarkMode ? '#1e293b' : '#e0e7ff', paddingHorizontal: 16, paddingVertical: 8, borderRadius: 20, borderWidth: 1, borderColor: isDarkMode ? '#334155' : 'transparent' }}>
                        <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: '#4338ca', marginRight: 8 }} />
                        <Text style={{ fontSize: 13, color: isDarkMode ? '#c7d2fe' : '#4338ca', fontWeight: 'bold' }}>v1.0.0 Public Beta</Text>
                    </View>
                </View>

                {/* Developer Card - Modern Glass style */}
                <View style={{
                    backgroundColor: theme.card,
                    borderRadius: 24, padding: 20,
                    marginBottom: 25,
                    borderWidth: 1, borderColor: isDarkMode ? '#334155' : '#f1f5f9',
                    shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 3
                }}>
                    <Text style={{ fontSize: 11, color: theme.textMuted, fontWeight: '800', marginBottom: 15, letterSpacing: 1 }}>DEVELOPED BY</Text>
                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                        <View style={{ width: 60, height: 60, borderRadius: 20, backgroundColor: isDarkMode ? '#0c4a6e' : '#f0f9ff', justifyContent: 'center', alignItems: 'center', marginRight: 16, borderWidth: 2, borderColor: isDarkMode ? '#0ea5e9' : '#bae6fd' }}>
                            <WebIcon name="user" size={30} color={isDarkMode ? '#bae6fd' : '#0284c7'} />
                        </View>
                        <View style={{ flex: 1 }}>
                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: theme.text }}>Muhammad Fahiim</Text>
                            <Text style={{ fontSize: 14, color: theme.textMuted }}>Fullstack Developer</Text>
                            <TouchableOpacity onPress={() => Linking.openURL('https://gradasi.net')} style={{ flexDirection: 'row', alignItems: 'center', marginTop: 6, alignSelf: 'flex-start' }}>
                                <WebIcon name="globe" size={12} color={theme.primary} style={{ marginRight: 4 }} />
                                <Text style={{ fontSize: 12, color: theme.primary, fontWeight: '700' }}>gradasi.net</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>

                {/* Grid Features */}
                <View style={{ flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', marginBottom: 25 }}>
                    {[
                        { label: 'Realtime', icon: 'time', bg: '#fef2f2', color: '#ef4444' },
                        { label: 'Secure QR', icon: 'qr', bg: '#f0fdf4', color: '#16a34a' },
                        { label: 'Cloud DB', icon: 'server', bg: '#eff6ff', color: '#2563eb' },
                        { label: 'Multi-Role', icon: 'users', bg: '#fff7ed', color: '#ea580c' },
                    ].map((item, index) => (
                        <View key={index} style={{
                            width: '48%', backgroundColor: isDarkMode ? '#1e293b' : item.bg,
                            padding: 16, borderRadius: 20, marginBottom: 15,
                            alignItems: 'center', flexDirection: 'row',
                            borderWidth: 1, borderColor: isDarkMode ? '#334155' : 'transparent',
                            shadowColor: item.color, shadowOpacity: isDarkMode ? 0 : 0.05, shadowRadius: 5, elevation: isDarkMode ? 0 : 2
                        }}>
                            <View style={{ width: 36, height: 36, borderRadius: 12, backgroundColor: isDarkMode ? '#0f172a' : 'white', justifyContent: 'center', alignItems: 'center', marginRight: 10 }}>
                                <WebIcon name={item.icon === 'time' ? 'calendar' : item.icon} size={18} color={item.color} />
                            </View>
                            <Text style={{ fontWeight: 'bold', color: isDarkMode ? theme.text : '#334155', fontSize: 13 }}>{item.label}</Text>
                        </View>
                    ))}
                </View>

                {/* Tech Stack */}
                <View style={{ marginBottom: 30 }}>
                    <Text style={{ fontSize: 18, fontWeight: '900', color: theme.text, marginBottom: 15 }}>Tech Stack</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingRight: 20 }}>
                        {['React Native', 'Expo', 'PHP 8', 'MySQL', 'Node.js'].map((t, i) => (
                            <View key={i} style={{
                                paddingHorizontal: 16, paddingVertical: 10,
                                backgroundColor: theme.card,
                                borderRadius: 12, marginRight: 10,
                                borderWidth: 1, borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                                flexDirection: 'row', alignItems: 'center'
                            }}>
                                <View style={{ width: 6, height: 6, borderRadius: 3, backgroundColor: theme.textMuted, marginRight: 8, opacity: 0.5 }} />
                                <Text style={{ fontWeight: '600', color: theme.text }}>{t}</Text>
                            </View>
                        ))}
                    </ScrollView>
                </View>

                {/* Connect Buttons */}
                <View style={{ gap: 12, paddingBottom: 40 }}>
                    <TouchableOpacity
                        style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: isDarkMode ? '#0f172a' : '#1f2937', padding: 18, borderRadius: 20, borderWidth: 1, borderColor: isDarkMode ? '#334155' : 'transparent' }}
                        onPress={() => Linking.openURL('https://github.com/muhfahmm')}
                    >
                        <WebIcon name="github" size={28} color="white" style={{ marginRight: 15 }} />
                        <View>
                            <Text style={{ color: 'white', fontWeight: 'bold', fontSize: 16 }}>Follow on GitHub</Text>
                            <Text style={{ color: '#9ca3af', fontSize: 12, marginTop: 2 }}>Check source code & updates</Text>
                        </View>
                        <View style={{ flex: 1, alignItems: 'flex-end' }}>
                            <WebIcon name="back" size={20} color="#6b7280" style={{ transform: [{ rotate: '180deg' }] }} />
                        </View>
                    </TouchableOpacity>

                    <TouchableOpacity
                        style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: '#db2777', padding: 18, borderRadius: 20 }}
                        onPress={() => Linking.openURL('https://www.instagram.com/_muhfhmm')}
                    >
                        <WebIcon name="instagram" size={28} color="white" style={{ marginRight: 15 }} />
                        <View>
                            <Text style={{ color: 'white', fontWeight: 'bold', fontSize: 16 }}>Follow on Instagram</Text>
                            <Text style={{ color: 'rgba(255,255,255,0.9)', fontSize: 12, marginTop: 2 }}>Daily updates & stories</Text>
                        </View>
                        <View style={{ flex: 1, alignItems: 'flex-end' }}>
                            <WebIcon name="back" size={20} color="rgba(255,255,255,0.6)" style={{ transform: [{ rotate: '180deg' }] }} />
                        </View>
                    </TouchableOpacity>
                </View>

                <View style={{ marginTop: 10, alignItems: 'center', paddingBottom: 15 }}>
                    <Text style={{ color: theme.textMuted, fontSize: 13, marginTop: 4 }}>© 2026 Gradasi Web</Text>
                </View>

            </ScreenTemplate>
        );
    };

    const renderJadwal = () => {
        const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const currentData = jadwal ? (jadwal[selectedDayJadwal] || []) : [];

        return (
            <ScreenTemplate title={t('jadwalPelajaran')} subtitle={t('lihatJadwal')} headerOverlap={false}>
                {/* Day Tabs */}
                <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginTop: 20, marginBottom: 20 }}>
                    {days.map((day) => (
                        <TouchableOpacity
                            key={day}
                            style={{
                                paddingHorizontal: 20,
                                paddingVertical: 10,
                                backgroundColor: selectedDayJadwal === day ? theme.primary : theme.card,
                                borderRadius: 20,
                                marginRight: 10,
                                borderWidth: 1,
                                borderColor: selectedDayJadwal === day ? theme.primary : (isDarkMode ? '#334155' : '#e2e8f0')
                            }}
                            onPress={() => setSelectedDayJadwal(day)}
                        >
                            <Text style={{ color: selectedDayJadwal === day ? 'white' : theme.text, fontWeight: 'bold' }}>{day}</Text>
                        </TouchableOpacity>
                    ))}
                </ScrollView>



                {/* Schedule List */}
                <View style={{ paddingBottom: 40 }}>
                    {!jadwal ? (
                        <View style={{ padding: 40, alignItems: 'center' }}>
                            <ActivityIndicator size="large" color={theme.primary} />
                            <Text style={{ color: theme.textMuted, marginTop: 15 }}>Memuat jadwal...</Text>
                        </View>
                    ) : currentData.length === 0 ? (
                        <View style={{ padding: 40, alignItems: 'center' }}>
                            <WebIcon name="calendar" size={40} color={theme.textMuted} />
                            <Text style={{ color: theme.textMuted, marginTop: 15 }}>Tidak ada jadwal untuk hari {selectedDayJadwal}</Text>
                        </View>
                    ) : (
                        currentData.map((item, index) => {
                            const isIstirahat = item.is_istirahat == 1 || item.jam_ke == 0;
                            return (
                                <View key={index} style={[styles.infoItemCard, { backgroundColor: isIstirahat ? (isDarkMode ? '#3f1f14' : '#fff7ed') : theme.card, padding: 16, borderLeftWidth: 4, borderLeftColor: isIstirahat ? '#f97316' : theme.primary }]}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                        <View style={{ width: 60, marginRight: 10 }}>
                                            <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>
                                                {item.jam_mulai ? item.jam_mulai.substring(0, 5) : '--:--'}
                                            </Text>
                                            <Text style={{ fontSize: 12, color: theme.textMuted }}>
                                                {item.jam_selesai ? item.jam_selesai.substring(0, 5) : '--:--'}
                                            </Text>
                                        </View>

                                        <View style={{ flex: 1 }}>
                                            {isIstirahat ? (
                                                <Text style={{ fontSize: 16, fontWeight: 'bold', color: isDarkMode ? '#fdba74' : '#c2410c', fontStyle: 'italic' }}>ISTIRAHAT</Text>
                                            ) : (
                                                <>
                                                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text, marginBottom: 4 }}>
                                                        {item.nama_mapel || 'Tanpa Mapel'}
                                                    </Text>
                                                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                                        <WebIcon name="user" size={12} color={theme.textMuted} style={{ marginRight: 4 }} />
                                                        <Text style={{ fontSize: 12, color: theme.textMuted }}>
                                                            {item.nama_guru || (item.nama_kelas ? `Kelas ${item.nama_kelas}` : 'Guru tidak diketahui')}
                                                        </Text>
                                                    </View>
                                                </>
                                            )}
                                        </View>

                                        <View style={{ marginLeft: 10 }}>
                                            <View style={{ width: 30, height: 30, borderRadius: 15, backgroundColor: isIstirahat ? '#ffedd5' : '#e0e7ff', justifyContent: 'center', alignItems: 'center' }}>
                                                <Text style={{ fontSize: 12, fontWeight: 'bold', color: isIstirahat ? '#c2410c' : '#4338ca' }}>
                                                    {isIstirahat ? 'R' : item.jam_ke}
                                                </Text>
                                            </View>
                                        </View>
                                    </View>
                                </View>
                            );
                        })
                    )}
                </View>
            </ScreenTemplate>
        );
    };

    const renderPembayaran = () => {
        const isSiswa = userData?.role === 'siswa';

        return (
            <ScreenTemplate title="Pembayaran" onBack={() => setCurrentView('dashboard')}>
                <ScrollView
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { fetchSaldo(); if (isSiswa) fetchSppData(); }} />}
                    style={{ padding: 20 }}
                >
                    {/* Card Saldo */}
                    <View style={{
                        backgroundColor: '#7c3aed',
                        borderRadius: 24,
                        padding: 24,
                        marginBottom: 24,
                        elevation: 10,
                        shadowColor: '#7c3aed',
                        shadowOffset: { width: 0, height: 8 },
                        shadowOpacity: 0.3,
                        shadowRadius: 16
                    }}>
                        <Text style={{ color: '#ddd6fe', fontSize: 14 }}>Saldo E-Wallet</Text>
                        <Text style={{ color: 'white', fontSize: 32, fontWeight: 'bold', marginVertical: 8 }}>
                            Rp {parseInt(saldo).toLocaleString('id-ID')}
                        </Text>
                        <TouchableOpacity
                            style={{
                                backgroundColor: 'white',
                                paddingVertical: 12,
                                borderRadius: 12,
                                alignItems: 'center',
                                marginTop: 12
                            }}
                            onPress={() => setShowTopUpModal(true)}
                        >
                            <Text style={{ color: '#7c3aed', fontWeight: 'bold', fontSize: 16 }}>+ Top Up Saldo</Text>
                        </TouchableOpacity>
                    </View>

                    {/* Tab Menu */}
                    <View style={{ flexDirection: 'row', marginBottom: 20, backgroundColor: theme.card, borderRadius: 12, padding: 4 }}>
                        {['transaksi', 'tagihan', 'lunas'].map((tab) => (
                            <TouchableOpacity
                                key={tab}
                                style={{
                                    flex: 1,
                                    paddingVertical: 10,
                                    alignItems: 'center',
                                    backgroundColor: paymentTab === tab ? theme.primary : 'transparent',
                                    borderRadius: 10
                                }}
                                onPress={() => setPaymentTab(tab)}
                            >
                                <Text style={{
                                    color: paymentTab === tab ? 'white' : theme.textMuted,
                                    fontWeight: 'bold',
                                    fontSize: 13
                                }}>
                                    {tab === 'transaksi' ? 'Riwayat' : (tab === 'tagihan' ? 'Tagihan SPP' : 'SPP Lunas')}
                                </Text>
                            </TouchableOpacity>
                        ))}
                    </View>

                    {/* CONTENT - RIWAYAT TRANSAKSI */}
                    {paymentTab === 'transaksi' && (
                        <View>
                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: theme.text, marginBottom: 15 }}>Riwayat Transaksi E-Wallet</Text>
                            {riwayatSaldo.length === 0 ? (
                                <View style={{ alignItems: 'center', marginTop: 30 }}>
                                    <WebIcon name="info" size={40} color={theme.textMuted} />
                                    <Text style={{ color: theme.textMuted, marginTop: 10 }}>Belum ada transaksi</Text>
                                </View>
                            ) : (
                                riwayatSaldo.map((item, index) => (
                                    <View key={index} style={{
                                        flexDirection: 'row',
                                        backgroundColor: theme.card,
                                        padding: 16,
                                        borderRadius: 16,
                                        marginBottom: 12,
                                        alignItems: 'center',
                                        borderColor: theme.border,
                                        borderWidth: 1
                                    }}>
                                        <View style={{
                                            width: 44,
                                            height: 44,
                                            borderRadius: 22,
                                            backgroundColor: item.tipe === 'masuk' ? '#dcfce7' : '#fee2e2',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            marginRight: 16
                                        }}>
                                            <WebIcon name={item.tipe === 'masuk' ? 'download' : 'upload'} size={20} color={item.tipe === 'masuk' ? '#16a34a' : '#ef4444'} />
                                        </View>
                                        <View style={{ flex: 1 }}>
                                            <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>{item.keterangan}</Text>
                                            <Text style={{ fontSize: 12, color: theme.textMuted }}>{new Date(item.created_at).toLocaleString()}</Text>
                                        </View>
                                        <Text style={{
                                            fontSize: 16,
                                            fontWeight: 'bold',
                                            color: item.tipe === 'masuk' ? '#16a34a' : '#ef4444'
                                        }}>
                                            {item.tipe === 'masuk' ? '+' : '-'} Rp {parseInt(item.jumlah).toLocaleString()}
                                        </Text>
                                    </View>
                                ))
                            )}
                        </View>
                    )}

                    {/* CONTENT - TAGIHAN SPP */}
                    {paymentTab === 'tagihan' && (
                        <View>
                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: theme.text, marginBottom: 15 }}>Tagihan Belum Lunas</Text>
                            {isSppLoading ? (
                                <ActivityIndicator size="large" color={theme.primary} />
                            ) : sppData.unpaid.length === 0 ? (
                                <View style={{ alignItems: 'center', padding: 30 }}>
                                    <WebIcon name="check" size={40} color="#16a34a" />
                                    <Text style={{ color: theme.text, marginTop: 10, fontWeight: 'bold' }}>Hebat!</Text>
                                    <Text style={{ color: theme.textMuted }}>Tidak ada tagihan SPP tunggakan.</Text>
                                </View>
                            ) : (
                                sppData.unpaid.map((item, index) => (
                                    <View key={index} style={{
                                        backgroundColor: theme.card,
                                        padding: 16,
                                        borderRadius: 16,
                                        marginBottom: 12,
                                        borderColor: theme.border,
                                        borderWidth: 1
                                    }}>
                                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                            <View>
                                                <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>SPP {item.bulan_nama} {item.tahun}</Text>
                                                <Text style={{ fontSize: 14, color: '#ef4444', fontWeight: 'bold', marginTop: 4 }}>{item.formatted_nominal}</Text>
                                            </View>
                                            <TouchableOpacity
                                                style={{ backgroundColor: theme.primary, paddingHorizontal: 16, paddingVertical: 8, borderRadius: 8 }}
                                                onPress={() => handlePaySpp(item.id, item.nominal_tagihan)}
                                            >
                                                <Text style={{ color: 'white', fontWeight: 'bold' }}>Bayar</Text>
                                            </TouchableOpacity>
                                        </View>
                                    </View>
                                ))
                            )}
                        </View>
                    )}

                    {/* CONTENT - SPP LUNAS */}
                    {paymentTab === 'lunas' && (
                        <View>
                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: theme.text, marginBottom: 15 }}>Riwayat Pembayaran SPP</Text>
                            {isSppLoading ? (
                                <ActivityIndicator size="large" color={theme.primary} />
                            ) : sppData.paid.length === 0 ? (
                                <Text style={{ color: theme.textMuted, textAlign: 'center', marginTop: 20 }}>Belum ada data pembayaran.</Text>
                            ) : (
                                sppData.paid.map((item, index) => (
                                    <View key={index} style={{
                                        backgroundColor: theme.card,
                                        padding: 16,
                                        borderRadius: 16,
                                        marginBottom: 12,
                                        borderColor: theme.border,
                                        borderWidth: 1,
                                        flexDirection: 'row',
                                        justifyContent: 'space-between',
                                        alignItems: 'center'
                                    }}>
                                        <View>
                                            <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>SPP {item.bulan_nama} {item.tahun}</Text>
                                            <Text style={{ fontSize: 12, color: theme.textMuted }}>Dibayar: {item.formatted_tanggal}</Text>
                                        </View>
                                        <View style={{ alignItems: 'flex-end' }}>
                                            <Text style={{ fontSize: 14, color: '#16a34a', fontWeight: 'bold' }}>LUNAS</Text>
                                            <Text style={{ fontSize: 12, color: theme.text }}>{item.formatted_nominal}</Text>
                                        </View>
                                    </View>
                                ))
                            )}
                        </View>
                    )}
                </ScrollView>
                {/* MODERN PAYMENT METHOD MODAL - SWIPEABLE */}
                <Modal
                    visible={showPaymentMethodModal}
                    transparent={true}
                    animationType="slide"
                    onRequestClose={closePaymentMethodModal}
                >
                    <View style={{ flex: 1, justifyContent: 'flex-end' }}>
                        {/* Invisible touchable to close on click outside, optionally */}

                        <Animated.View
                            style={{
                                backgroundColor: theme.card,
                                borderTopLeftRadius: 24,
                                borderTopRightRadius: 24,
                                padding: 24,
                                transform: [{ translateY: panY }],
                                shadowColor: "#000",
                                shadowOffset: {
                                    width: 0,
                                    height: -4,
                                },
                                shadowOpacity: 0.1,
                                shadowRadius: 4.65,
                                elevation: 8,
                            }}
                            {...paymentPanResponder.panHandlers}
                        >
                            <View style={{ alignItems: 'center', marginBottom: 20 }}>
                                <View style={{ width: 40, height: 4, backgroundColor: theme.border, borderRadius: 2 }} />
                            </View>

                            <Text style={{ fontSize: 20, fontWeight: 'bold', color: theme.text, textAlign: 'center', marginBottom: 8 }}>
                                Pilih Metode Pembayaran
                            </Text>
                            <Text style={{ fontSize: 14, color: theme.textMuted, textAlign: 'center', marginBottom: 24 }}>
                                Total Tagihan: <Text style={{ fontWeight: 'bold', color: theme.primary }}>Rp {selectedTagihan?.amount?.toLocaleString('id-ID')}</Text>
                            </Text>

                            <TouchableOpacity
                                onPress={() => handlePaySppWallet(selectedTagihan?.id)}
                                style={{
                                    flexDirection: 'row', alignItems: 'center', backgroundColor: isDarkMode ? '#1e293b' : '#f8fafc',
                                    padding: 16, borderRadius: 16, marginBottom: 12, borderWidth: 1, borderColor: theme.border
                                }}
                            >
                                <View style={{ width: 48, height: 48, borderRadius: 12, backgroundColor: '#dcfce7', justifyContent: 'center', alignItems: 'center', marginRight: 16 }}>
                                    <WebIcon name="card" size={24} color="#16a34a" />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>Saldo E-Wallet</Text>
                                    <Text style={{ fontSize: 12, color: theme.textMuted }}>Sisa Saldo: Rp {saldo.toLocaleString('id-ID')}</Text>
                                </View>
                                <WebIcon name="chevron-right" size={20} color={theme.textMuted} />
                            </TouchableOpacity>

                            <TouchableOpacity
                                onPress={() => handlePaySppMidtrans(selectedTagihan?.id, selectedTagihan?.amount)}
                                style={{
                                    flexDirection: 'row', alignItems: 'center', backgroundColor: isDarkMode ? '#1e293b' : '#f8fafc',
                                    padding: 16, borderRadius: 16, marginBottom: 24, borderWidth: 1, borderColor: theme.border
                                }}
                            >
                                <View style={{ width: 48, height: 48, borderRadius: 12, backgroundColor: '#dbeafe', justifyContent: 'center', alignItems: 'center', marginRight: 16 }}>
                                    <WebIcon name="globe" size={24} color="#2563eb" />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>Transfer / QRIS</Text>
                                    <Text style={{ fontSize: 12, color: theme.textMuted }}>Midtrans Gateway</Text>
                                </View>
                                <WebIcon name="chevron-right" size={20} color={theme.textMuted} />
                            </TouchableOpacity>

                            <TouchableOpacity
                                onPress={closePaymentMethodModal}
                                style={{ padding: 16, alignItems: 'center' }}
                            >
                                <Text style={{ color: theme.textMuted, fontWeight: 'bold' }}>Batal</Text>
                            </TouchableOpacity>
                        </Animated.View>
                    </View>
                </Modal>
            </ScreenTemplate>
        );
    };

    const renderKehadiran = () => {
        const summary = attendanceHistory.summary || { hadir: 0, terlambat: 0, izin: 0, sakit: 0, alpa: 0 };
        const history = attendanceHistory.history || [];

        return (
            <ScreenTemplate title={t('riwayatKehadiran')} subtitle={t('dataAbsensiBulan')} headerOverlap={false}>
                {/* Summary Section */}
                <View style={{ flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', marginTop: 20 }}>
                    <SummaryMiniCard label={t('hadir')} value={summary.hadir} color="#16a34a" theme={theme} />
                    <SummaryMiniCard label={t('terlambat')} value={summary.terlambat} color="#ea580c" theme={theme} />
                    <SummaryMiniCard label={t('izin')} value={summary.izin} color="#3b82f6" theme={theme} />
                    <SummaryMiniCard label={t('sakit')} value={summary.sakit} color="#7c3aed" theme={theme} />
                </View>

                {/* History List */}
                <View style={{ marginTop: 25 }}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                        <Text style={{ color: theme.text, fontSize: 18, fontWeight: 'bold' }}>{t('aktivitasTerbaru')}</Text>
                        <Text style={{ color: theme.primary, fontSize: 13, fontWeight: '600' }}>{t('hariTerakhir')}</Text>
                    </View>

                    {history.length === 0 ? (
                        <View style={{ padding: 40, alignItems: 'center' }}>
                            <WebIcon name="clipboard" size={40} color={theme.textMuted} />
                            <Text style={{ color: theme.textMuted, marginTop: 15 }}>{t('tidakAdaRiwayat')}</Text>
                        </View>
                    ) : (
                        history.map((item, index) => (
                            <View key={index} style={[styles.infoItemCard, { backgroundColor: theme.card, marginBottom: 15, padding: 16 }]}>
                                <View style={{ flex: 1 }}>
                                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <Text style={{ color: theme.text, fontSize: 16, fontWeight: 'bold' }}>
                                            {new Date(item.tanggal).toLocaleDateString(language === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'long', year: 'numeric' })}
                                        </Text>
                                        <View style={{ backgroundColor: item.status === 'hadir' ? '#dcfce7' : (item.status === 'terlambat' ? '#ffedd5' : '#f1f5f9'), paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 }}>
                                            <Text style={{ color: item.status === 'hadir' ? '#16a34a' : (item.status === 'terlambat' ? '#ea580c' : '#64748b'), fontSize: 11, fontWeight: 'bold' }}>
                                                {item.status.toUpperCase()}
                                            </Text>
                                        </View>
                                    </View>
                                    <View style={{ flexDirection: 'row', marginTop: 12 }}>
                                        <View style={{ marginRight: 25 }}>
                                            <Text style={{ color: theme.textMuted, fontSize: 11 }}>{t('jamMasuk').toUpperCase()}</Text>
                                            <Text style={{ color: theme.text, fontSize: 14, fontWeight: '700', marginTop: 2 }}>{item.jam_masuk ? item.jam_masuk.substring(0, 5) : '--:--'}</Text>
                                        </View>
                                        <View>
                                            <Text style={{ color: theme.textMuted, fontSize: 11 }}>{t('jamPulang').toUpperCase()}</Text>
                                            <Text style={{ color: theme.text, fontSize: 14, fontWeight: '700', marginTop: 2 }}>{item.jam_keluar ? item.jam_keluar.substring(0, 5) : '--:--'}</Text>
                                        </View>
                                    </View>
                                </View>
                            </View>
                        ))
                    )}
                </View>
            </ScreenTemplate>
        );
    };

    const renderMonitoringKelas = () => {
        const summary = classMonitoring?.summary || { total: 0, hadir: 0, belum: 0, terlambat: 0 };
        const students = classMonitoring?.students || [];

        // Filter tabs could be added here, for now listing all sorted by status
        const presentStudents = students.filter(s => s.status !== 'belum');
        const absentStudents = students.filter(s => s.status === 'belum');

        // Styles specific
        const cardBg = theme.card;

        return (
            <ScreenTemplate title={t('monitoringKelas')} subtitle={classMonitoring?.kelas || 'Memuat...'} headerOverlap={false}>
                {/* Summary Cards */}
                <View style={{ flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', marginTop: 20 }}>
                    <SummaryMiniCard label={t('totalSiswa')} value={summary.total} color={theme.text} theme={theme} />
                    <SummaryMiniCard label={t('sudahAbsen')} value={summary.hadir} color="#16a34a" theme={theme} />
                    <SummaryMiniCard label={t('belumAbsen')} value={summary.belum} color="#ef4444" theme={theme} />
                    <SummaryMiniCard label={t('terlambat')} value={summary.terlambat} color="#ea580c" theme={theme} />
                </View>

                {/* Lists */}
                <View style={{ marginTop: 10 }}>
                    <Text style={{ color: theme.text, fontSize: 18, fontWeight: 'bold', marginBottom: 15 }}>{t('belumAbsen')} ({absentStudents.length})</Text>
                    {absentStudents.map((item) => (
                        <View key={item.id} style={[styles.infoItemCard, { backgroundColor: cardBg, padding: 12 }]}>
                            <View style={[styles.avatarCircle, { width: 40, height: 40, borderRadius: 20, backgroundColor: '#f1f5f9', borderWidth: 0, marginRight: 12 }]}>
                                {item.foto ? (
                                    <Image source={{ uri: `${BASE_URL}/uploads/siswa/${encodeURIComponent(item.foto)}` }} style={{ width: 40, height: 40, borderRadius: 20 }} />
                                ) : (
                                    <Text style={{ fontSize: 10, fontWeight: 'bold', color: '#94a3b8' }}>{item.nama.substring(0, 2).toUpperCase()}</Text>
                                )}
                            </View>
                            <View style={{ flex: 1 }}>
                                <Text style={{ color: theme.text, fontWeight: 'bold', fontSize: 14 }}>{item.nama}</Text>
                                <Text style={{ color: theme.textMuted, fontSize: 12 }}>{item.nis}</Text>
                            </View>
                            <View style={{ backgroundColor: '#fee2e2', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 6 }}>
                                <Text style={{ color: '#991b1b', fontSize: 10, fontWeight: 'bold' }}>{t('belumAbsen').toUpperCase()}</Text>
                            </View>
                        </View>
                    ))}

                    <Text style={{ color: theme.text, fontSize: 18, fontWeight: 'bold', marginBottom: 15, marginTop: 25 }}>{t('sudahAbsen')} ({presentStudents.length})</Text>
                    {presentStudents.map((item) => (
                        <View key={item.id} style={[styles.infoItemCard, { backgroundColor: cardBg, padding: 12 }]}>
                            <View style={[styles.avatarCircle, { width: 40, height: 40, borderRadius: 20, backgroundColor: '#f1f5f9', borderWidth: 0, marginRight: 12 }]}>
                                {item.foto ? (
                                    <Image source={{ uri: `${BASE_URL}/uploads/siswa/${encodeURIComponent(item.foto)}` }} style={{ width: 40, height: 40, borderRadius: 20 }} />
                                ) : (
                                    <Text style={{ fontSize: 10, fontWeight: 'bold', color: '#94a3b8' }}>{item.nama.substring(0, 2).toUpperCase()}</Text>
                                )}
                            </View>
                            <View style={{ flex: 1 }}>
                                <Text style={{ color: theme.text, fontWeight: 'bold', fontSize: 14 }}>{item.nama}</Text>
                                <Text style={{ color: theme.textMuted, fontSize: 12 }}>{t('hadir')}: {item.jam_masuk?.substring(0, 5) || '-'}</Text>
                            </View>
                            <View style={{ backgroundColor: item.status === 'terlambat' ? '#ffedd5' : '#dcfce7', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 6 }}>
                                <Text style={{ color: item.status === 'terlambat' ? '#9a3412' : '#166534', fontSize: 10, fontWeight: 'bold' }}>
                                    {item.status.toUpperCase()}
                                </Text>
                            </View>
                        </View>
                    ))}
                </View>
            </ScreenTemplate>
        );
    };

    const SummaryMiniCard = ({ label, value, color, theme }) => (
        <View style={{ width: (width - 60) / 2, backgroundColor: theme.card, padding: 20, borderRadius: 20, marginBottom: 15, shadowColor: "#000", shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 5, elevation: 2 }}>
            <Text style={{ color: theme.textMuted, fontSize: 12, fontWeight: 'bold' }}>{label.toUpperCase()}</Text>
            <Text style={{ color: color, fontSize: 24, fontWeight: '900', marginTop: 4 }}>{value}</Text>
        </View>
    );

    const InfoCard = ({ icon, iconBg, iconColor, label, value, theme, isDarkMode, mono }) => (
        <View style={[styles.infoItemCard, { backgroundColor: theme.card }]}>
            <View style={[styles.infoIconCircle, { backgroundColor: isDarkMode ? iconColor + '20' : iconBg }]}>
                <WebIcon name={icon} size={22} color={iconColor} />
            </View>
            <View style={{ flex: 1, marginLeft: 16 }}>
                <Text style={{ color: theme.textMuted, fontSize: 13, fontWeight: '600' }}>{label}</Text>
                <Text style={{ color: theme.text, fontSize: 16, fontWeight: 'bold', marginTop: 2, fontFamily: mono ? 'monospace' : undefined }}>{value}</Text>
            </View>
        </View>
    );

    const renderPlaceholder = (title) => (
        <ScreenTemplate title={t('fitur') + " " + title}>
            <View style={[styles.webStatusContainer, { backgroundColor: theme.card, alignItems: 'center', padding: 50, marginTop: 10 }]}>
                <WebIcon name="speaker" size={60} color={theme.textMuted} />
                <Text style={{ color: theme.text, fontSize: 18, marginTop: 20, textAlign: 'center', fontWeight: 'bold' }}>
                    {t('fitur')} {title}
                </Text>
                <Text style={{ color: theme.textMuted, fontSize: 14, textAlign: 'center', marginTop: 8 }}>
                    {t('dalamPengembangan')}
                </Text>
                <TouchableOpacity
                    style={[styles.loginButton, { backgroundColor: theme.primary, width: '100%', marginTop: 30 }]}
                    onPress={() => setCurrentView('dashboard')}
                >
                    <Text style={styles.loginButtonText}>{t('selesai')}</Text>
                </TouchableOpacity>
            </View>
        </ScreenTemplate>
    );

    const renderNilai = () => {
        const isSiswa = userData?.role === 'siswa';

        // Filter and Sort Logic
        let processedNilai = [...nilaiData];

        // 1. Searching
        if (nilaiSearch.trim() !== '') {
            const query = nilaiSearch.toLowerCase();
            if (isSiswa) {
                processedNilai = processedNilai.filter(item =>
                    item.mata_pelajaran.toLowerCase().includes(query) ||
                    item.guru.toLowerCase().includes(query) ||
                    item.grades.some(g => g.ket && g.ket.toLowerCase().includes(query))
                );
            } else {
                processedNilai = processedNilai.filter(item =>
                    item.nama_siswa.toLowerCase().includes(query) ||
                    item.nama_mapel.toLowerCase().includes(query) ||
                    item.tipe_nilai.toLowerCase().includes(query)
                );
            }
        }

        // 2. Sorting
        processedNilai.sort((a, b) => {
            if (isSiswa) {
                if (nilaiSort === 'pelajaran') {
                    return a.mata_pelajaran.localeCompare(b.mata_pelajaran);
                }
                const getLatestDate = (item) => Math.max(...item.grades.map(g => new Date(g.tgl).getTime()));
                return nilaiSort === 'oldest' ? getLatestDate(a) - getLatestDate(b) : getLatestDate(b) - getLatestDate(a);
            } else {
                if (nilaiSort === 'pelajaran') {
                    return a.nama_mapel.localeCompare(b.nama_mapel);
                }
                const dateA = new Date(a.created_at).getTime();
                const dateB = new Date(b.created_at).getTime();
                return nilaiSort === 'oldest' ? dateA - dateB : dateB - dateA;
            }
        });

        const getGradeStyle = (val) => {
            const grade = parseFloat(val);
            if (grade >= 80) return {
                bg: isDarkMode ? '#064e3b' : '#dcfce7',
                text: isDarkMode ? '#34d399' : '#166534'
            };
            if (grade >= 60) return {
                bg: isDarkMode ? '#713f1240' : '#fef9c3',
                text: isDarkMode ? '#facc15' : '#854d0e'
            };
            return {
                bg: isDarkMode ? '#7f1d1d' : '#fee2e2',
                text: isDarkMode ? '#f87171' : '#991b1b'
            };
        };

        return (
            <View style={[styles.dashboardWrapper, { backgroundColor: theme.bg }]}>
                {/* Fixed Header Manual - Same as E-Learning */}
                <View style={styles.webHeader}>
                    <View style={styles.headerFlex}>
                        <View style={{ flex: 1 }}>
                            <Text style={styles.webHeaderTitle}>{t('nilai')}</Text>
                            <Text style={styles.webHeaderSubtitle}>{t('raportAkademik')}</Text>
                        </View>
                        <TouchableOpacity onPress={() => setCurrentView('dashboard')}>
                            <WebIcon name="back" size={24} color="white" />
                        </TouchableOpacity>
                    </View>
                </View>

                {/* Scrollable Content */}
                <ScrollView
                    style={styles.scrollView}
                    bounces={true}
                    keyboardShouldPersistTaps="handled"
                    keyboardDismissMode="on-drag"
                >
                    <View style={[styles.mainContent, { paddingBottom: 120, marginTop: 0 }]}>
                        {/* Search Bar - Exactly like E-Learning */}
                        <View style={{ marginBottom: 15, marginTop: 10 }}>
                            <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: theme.card, borderRadius: 12, paddingHorizontal: 12, borderWidth: 1, borderColor: theme.border }}>
                                <WebIcon name="search" size={20} color={theme.textMuted} />
                                <TextInput
                                    style={{ flex: 1, padding: 12, color: theme.text, fontSize: 14 }}
                                    placeholder={t('cariNilai')}
                                    placeholderTextColor={theme.textMuted}
                                    value={nilaiSearch}
                                    onChangeText={setNilaiSearch}
                                    autoCapitalize="none"
                                />
                                {nilaiSearch.length > 0 && (
                                    <TouchableOpacity onPress={() => setNilaiSearch('')}>
                                        <WebIcon name="close" size={16} color={theme.textMuted} />
                                    </TouchableOpacity>
                                )}
                            </View>
                        </View>

                        {/* Sort Dropdown - Exactly like E-Learning's time filter */}
                        <View style={{ zIndex: 1000, marginBottom: 15 }}>
                            <TouchableOpacity
                                style={{
                                    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
                                    backgroundColor: theme.card, padding: 12, borderRadius: 12,
                                    borderWidth: 1, borderColor: theme.border
                                }}
                                onPress={() => setIsNilaiSortDropdownOpen(!isNilaiSortDropdownOpen)}
                            >
                                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                    <WebIcon name="calendar" size={16} color={theme.textMuted} style={{ marginRight: 8 }} />
                                    <Text style={{ color: theme.text }}>{t('filter')}: {t(nilaiSort)}</Text>
                                </View>
                                <WebIcon name="back" size={16} color={theme.textMuted} style={{ transform: [{ rotate: isNilaiSortDropdownOpen ? '90deg' : '-90deg' }] }} />
                            </TouchableOpacity>

                            {isNilaiSortDropdownOpen && (
                                <View style={{
                                    position: 'absolute', top: 50, left: 0, right: 0,
                                    backgroundColor: theme.card, borderRadius: 12,
                                    borderWidth: 1, borderColor: theme.border,
                                    shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 10, elevation: 5,
                                    zIndex: 2000
                                }}>
                                    {['newest', 'oldest', 'pelajaran'].map((option, idx) => (
                                        <TouchableOpacity
                                            key={option}
                                            style={{
                                                padding: 12,
                                                borderBottomWidth: idx === 2 ? 0 : 1,
                                                borderBottomColor: theme.border,
                                                flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'
                                            }}
                                            onPress={() => {
                                                setNilaiSort(option);
                                                setIsNilaiSortDropdownOpen(false);
                                            }}
                                        >
                                            <Text style={{ color: nilaiSort === option ? theme.primary : theme.text, fontWeight: nilaiSort === option ? 'bold' : 'normal' }}>
                                                {t(option)}
                                            </Text>
                                            {nilaiSort === option && <WebIcon name="tag" size={14} color={theme.primary} />}
                                        </TouchableOpacity>
                                    ))}
                                </View>
                            )}
                        </View>

                        {/* List Results */}
                        <View>
                            {processedNilai.length === 0 ? (
                                <View style={{ padding: 40, alignItems: 'center', opacity: 0.8 }}>
                                    <View style={{ width: 80, height: 80, borderRadius: 40, backgroundColor: isDarkMode ? '#1e293b' : '#f1f5f9', justifyContent: 'center', alignItems: 'center', marginBottom: 16 }}>
                                        <WebIcon name="barChart" size={32} color={theme.textMuted} />
                                    </View>
                                    <Text style={{ color: theme.text, fontSize: 16, fontWeight: 'bold' }}>{nilaiSearch ? "Tidak Ditemukan" : "Belum Ada Nilai"}</Text>
                                    <Text style={{ color: theme.textMuted, textAlign: 'center', marginTop: 8, fontSize: 14 }}>
                                        {nilaiSearch ? "Coba gunakan kata kunci lain." : (isSiswa ? "Nilai Anda belum diinput oleh guru mata pelajaran." : "Anda belum menginput nilai untuk siswa manapun.")}
                                    </Text>
                                </View>
                            ) : isSiswa ? (
                                // UI SISWA - Grouped by Mapel
                                processedNilai.map((item, index) => (
                                    <View key={index} style={{
                                        backgroundColor: theme.card,
                                        borderRadius: 24,
                                        marginBottom: 20,
                                        padding: 20,
                                        borderWidth: 1,
                                        borderColor: theme.border,
                                        shadowColor: "#000",
                                        shadowOffset: { width: 0, height: 4 },
                                        shadowOpacity: 0.05,
                                        shadowRadius: 10,
                                        elevation: 3
                                    }}>
                                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 }}>
                                            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                                <View style={{ width: 44, height: 44, borderRadius: 14, backgroundColor: isDarkMode ? '#3b82f620' : '#eff6ff', justifyContent: 'center', alignItems: 'center', marginRight: 12 }}>
                                                    <WebIcon name="book" size={22} color="#3b82f6" />
                                                </View>
                                                <View>
                                                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>{item.mata_pelajaran}</Text>
                                                    <Text style={{ fontSize: 12, color: theme.textMuted }}>{item.guru}</Text>
                                                </View>
                                            </View>
                                        </View>

                                        <View style={{ gap: 12 }}>
                                            {item.grades.sort((a, b) => {
                                                const dateA = new Date(a.tgl).getTime();
                                                const dateB = new Date(b.tgl).getTime();
                                                return nilaiSort === 'oldest' ? dateA - dateB : dateB - dateA;
                                            }).map((g, gi) => (
                                                <View key={gi} style={{
                                                    flexDirection: 'row',
                                                    alignItems: 'center',
                                                    backgroundColor: isDarkMode ? '#0f172a' : '#f8fafc',
                                                    padding: 12,
                                                    borderRadius: 16,
                                                    borderWidth: 1,
                                                    borderColor: isDarkMode ? '#334155' : '#f1f5f9'
                                                }}>
                                                    <View style={{ flex: 1 }}>
                                                        <Text style={{ fontSize: 11, fontWeight: 'bold', color: theme.textMuted, marginBottom: 2 }}>{g.tipe}</Text>
                                                        <Text style={{ fontSize: 13, color: theme.text, fontWeight: '600' }} numberOfLines={1}>{g.ket || 'Tidak ada keterangan'}</Text>
                                                        <Text style={{ fontSize: 10, color: theme.textMuted, marginTop: 4 }}>{new Date(g.tgl).toLocaleDateString()}</Text>
                                                    </View>
                                                    <View style={{
                                                        width: 50, height: 50, borderRadius: 12,
                                                        backgroundColor: getGradeStyle(g.nilai).bg,
                                                        justifyContent: 'center', alignItems: 'center'
                                                    }}>
                                                        <Text style={{
                                                            fontSize: 16, fontWeight: '900',
                                                            color: getGradeStyle(g.nilai).text
                                                        }}>{parseFloat(g.nilai)}</Text>
                                                    </View>
                                                </View>
                                            ))}
                                        </View>
                                    </View>
                                ))
                            ) : (
                                // UI GURU - List of recent grades given
                                processedNilai.map((item, index) => (
                                    <View key={index} style={[styles.infoItemCard, { backgroundColor: theme.card, padding: 16 }]}>
                                        <View style={{ width: 48, height: 48, borderRadius: 16, backgroundColor: getGradeStyle(item.nilai).bg, justifyContent: 'center', alignItems: 'center', marginRight: 16 }}>
                                            <Text style={{ fontSize: 18, fontWeight: 'bold', color: getGradeStyle(item.nilai).text }}>{item.nilai}</Text>
                                        </View>
                                        <View style={{ flex: 1 }}>
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <Text style={{ fontSize: 15, fontWeight: 'bold', color: theme.text }}>{item.nama_siswa}</Text>
                                                <Text style={{ fontSize: 10, fontWeight: 'bold', color: getGradeStyle(item.nilai).text, backgroundColor: getGradeStyle(item.nilai).bg, paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4 }}>{item.tipe_nilai}</Text>
                                            </View>
                                            <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 4 }}>
                                                <WebIcon name="book" size={12} color={theme.textMuted} style={{ marginRight: 4 }} />
                                                <Text style={{ fontSize: 12, color: theme.textMuted }}>{item.nama_mapel} • {item.nama_kelas}</Text>
                                            </View>
                                            <Text style={{ fontSize: 11, color: theme.textMuted, marginTop: 4 }}>{new Date(item.created_at).toLocaleString()}</Text>
                                        </View>
                                    </View>
                                ))
                            )}
                        </View>
                    </View>
                </ScrollView>

                {/* Bottom Navigation */}
                {renderBottomNav()}
            </View>
        );
    };
    const renderPengumuman = () => {
        return (
            <ScreenTemplate title={t('pengumuman')} subtitle={t('infoTerbaru')} headerOverlap={false}>
                {/* Time Filter Dropdown */}
                <View style={{ zIndex: 1000, marginTop: 20 }}>
                    <TouchableOpacity
                        style={{
                            flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
                            backgroundColor: theme.card, padding: 12, borderRadius: 12,
                            borderWidth: 1, borderColor: theme.border, elevation: 2
                        }}
                        onPress={() => setIsPengumumanTimeDropdownOpen(!isPengumumanTimeDropdownOpen)}
                    >
                        <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                            <WebIcon name="calendar" size={16} color={theme.textMuted} style={{ marginRight: 8 }} />
                            <Text style={{ color: theme.text }}>{t('filter')}: {t(pengumumanTimeFilter)}</Text>
                        </View>
                        <WebIcon name="back" size={16} color={theme.textMuted} style={{ transform: [{ rotate: isPengumumanTimeDropdownOpen ? '90deg' : '-90deg' }] }} />
                    </TouchableOpacity>

                    {isPengumumanTimeDropdownOpen && (
                        <View style={{
                            position: 'absolute', top: 50, left: 0, right: 0,
                            backgroundColor: theme.card, borderRadius: 12,
                            borderWidth: 1, borderColor: theme.border, zIndex: 2000, elevation: 5
                        }}>
                            {['newest', 'oldest', 'thisWeek', 'thisMonth'].map((option, idx) => (
                                <TouchableOpacity
                                    key={option}
                                    style={{
                                        padding: 12, borderBottomWidth: idx === 3 ? 0 : 1, borderBottomColor: theme.border,
                                        flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'
                                    }}
                                    onPress={() => {
                                        setPengumumanTimeFilter(option);
                                        setIsPengumumanTimeDropdownOpen(false);
                                    }}
                                >
                                    <Text style={{ color: pengumumanTimeFilter === option ? theme.primary : theme.text, fontWeight: pengumumanTimeFilter === option ? 'bold' : 'normal' }}>
                                        {t(option)}
                                    </Text>
                                    {pengumumanTimeFilter === option && <WebIcon name="tag" size={14} color={theme.primary} />}
                                </TouchableOpacity>
                            ))}
                        </View>
                    )}
                </View>

                {/* List Container */}
                <View style={{ marginTop: 20, paddingBottom: 40 }}>
                    {pengumumanList
                        .filter(item => {
                            let matchesTime = true;
                            const itemDate = new Date(item.tanggal_publish);
                            const now = new Date();
                            if (pengumumanTimeFilter === 'thisWeek') {
                                const oneWeekAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                                matchesTime = itemDate >= oneWeekAgo;
                            } else if (pengumumanTimeFilter === 'thisMonth') {
                                matchesTime = itemDate.getMonth() === now.getMonth() && itemDate.getFullYear() === now.getFullYear();
                            }
                            return matchesTime;
                        })
                        .sort((a, b) => {
                            const dateA = new Date(a.tanggal_publish);
                            const dateB = new Date(b.tanggal_publish);
                            return pengumumanTimeFilter === 'oldest' ? dateA - dateB : dateB - dateA;
                        })
                        .length === 0 ? (
                        <View style={{ padding: 40, alignItems: 'center', opacity: 0.7 }}>
                            <View style={{ width: 80, height: 80, borderRadius: 40, backgroundColor: isDarkMode ? '#1e293b' : '#f1f5f9', justifyContent: 'center', alignItems: 'center', marginBottom: 16 }}>
                                <WebIcon name="speaker" size={32} color={theme.textMuted} />
                            </View>
                            <Text style={{ color: theme.textMuted, fontSize: 16 }}>{t('noAnnouncement')}</Text>
                        </View>
                    ) : (
                        pengumumanList
                            .filter(item => {
                                let matchesTime = true;
                                const itemDate = new Date(item.tanggal_publish);
                                const now = new Date();
                                if (pengumumanTimeFilter === 'thisWeek') {
                                    const oneWeekAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                                    matchesTime = itemDate >= oneWeekAgo;
                                } else if (pengumumanTimeFilter === 'thisMonth') {
                                    matchesTime = itemDate.getMonth() === now.getMonth() && itemDate.getFullYear() === now.getFullYear();
                                }
                                return matchesTime;
                            })
                            .sort((a, b) => {
                                const dateA = new Date(a.tanggal_publish);
                                const dateB = new Date(b.tanggal_publish);
                                return pengumumanTimeFilter === 'oldest' ? dateA - dateB : dateB - dateA;
                            })
                            .map((item, index) => {
                                const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'];
                                const accentColor = colors[index % colors.length];
                                return (
                                    <View key={index} style={{
                                        backgroundColor: theme.card, borderRadius: 20, marginBottom: 20,
                                        overflow: 'hidden', borderWidth: 1, borderColor: theme.border, elevation: 3
                                    }}>
                                        <View style={{ height: 6, width: '100%', backgroundColor: accentColor }} />
                                        <View style={{ padding: 20 }}>
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 12 }}>
                                                <View style={{ flex: 1, paddingRight: 10 }}>
                                                    <Text style={{ color: theme.text, fontSize: 18, fontWeight: 'bold' }}>{item.judul}</Text>
                                                    <Text style={{ color: theme.textMuted, fontSize: 12, marginTop: 4 }}>{new Date(item.tanggal_publish).toLocaleDateString()}</Text>
                                                </View>
                                                <View style={{ width: 40, height: 40, borderRadius: 12, backgroundColor: isDarkMode ? 'rgba(255,255,255,0.05)' : '#f8fafc', justifyContent: 'center', alignItems: 'center' }}>
                                                    <WebIcon name="speaker" size={20} color={accentColor} />
                                                </View>
                                            </View>
                                            <View style={{ backgroundColor: isDarkMode ? 'rgba(0,0,0,0.2)' : '#f8fafc', padding: 16, borderRadius: 12, marginBottom: 16 }}>
                                                <Text style={{ color: theme.text, fontSize: 14, lineHeight: 22 }} numberOfLines={3}>{item.isi}</Text>
                                            </View>
                                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                                    <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: accentColor, marginRight: 8 }} />
                                                    <Text style={{ fontSize: 12, fontWeight: '600', color: theme.textMuted }}>{item.target_role.toUpperCase()}</Text>
                                                </View>
                                                <TouchableOpacity style={{ flexDirection: 'row', alignItems: 'center' }} onPress={() => { setSelectedPengumuman(item); setDetailModalVisible(true); }}>
                                                    <Text style={{ fontSize: 13, fontWeight: 'bold', color: theme.primary, marginRight: 4 }}>{t('lihatSelengkapnya')}</Text>
                                                    <WebIcon name="back" size={14} color={theme.primary} style={{ transform: [{ rotate: '180deg' }] }} />
                                                </TouchableOpacity>
                                            </View>
                                        </View>
                                    </View>
                                );
                            })
                    )}
                </View>
            </ScreenTemplate>
        );
    };

    return (
        <SafeAreaView style={[styles.container, { backgroundColor: isDarkMode ? '#0f172a' : '#f3f4f6' }]}>
            <StatusBar style="light" />
            {currentView === 'login' && renderLogin()}
            {currentView === 'dashboard' && renderDashboard()}
            {currentView === 'scanner' && renderScanner()}
            {currentView === 'profil' && renderProfil()}
            {currentView === 'kehadiran' && renderKehadiran()}
            {currentView === 'monitoring' && renderMonitoringKelas()}
            {currentView === 'pembayaran' && renderPembayaran()}

            {currentView === 'pengumuman' && renderPengumuman()}
            {currentView === 'jadwal' && renderJadwal()}
            {currentView === 'elearning' && renderElearning()}
            {currentView === 'perizinan' && renderPlaceholder('Perizinan')}
            {currentView === 'nilai' && renderNilai()}
            {currentView === 'tentang' && renderTentangAplikasi()}

            {/* Announcement Detail Modal - Bottom Sheet Style */}
            <Modal visible={detailModalVisible} transparent animationType="fade" onRequestClose={() => setDetailModalVisible(false)}>
                <View style={{ flex: 1, backgroundColor: 'transparent', justifyContent: 'flex-end' }}>
                    {/* Backdrop tap to close */}
                    <TouchableOpacity style={{ flex: 1 }} onPress={() => setDetailModalVisible(false)} />

                    <Animated.View
                        style={{
                            backgroundColor: theme.card,
                            borderTopLeftRadius: 30,
                            borderTopRightRadius: 30,
                            height: modalAnimation.interpolate({
                                inputRange: [-1, 0, 1],
                                outputRange: ['0%', '50%', '90%']
                            }),
                            opacity: modalAnimation.interpolate({
                                inputRange: [-1, 0],
                                outputRange: [0, 1],
                                extrapolate: 'clamp'
                            }),
                            padding: 0,
                            shadowColor: "#000",
                            shadowOffset: { width: 0, height: -5 },
                            shadowOpacity: 0.1,
                            shadowRadius: 20,
                            elevation: 20
                        }}>
                        {/* Drag Handle & Header - Attach PanResponder Here */}
                        <View {...panResponder.panHandlers}>
                            <View style={{ alignItems: 'center', paddingTop: 12, paddingBottom: 8 }}>
                                <View style={{ width: 60, height: 6, borderRadius: 3, backgroundColor: isDarkMode ? '#334155' : '#e2e8f0' }} />
                            </View>

                            {/* Modal Header */}
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 24, paddingBottom: 16, borderBottomWidth: 1, borderBottomColor: theme.border }}>
                                <Text style={{ fontSize: 20, fontWeight: 'bold', color: theme.text }}>{t('detailPengumuman')}</Text>
                                <TouchableOpacity onPress={() => setDetailModalVisible(false)} style={{ padding: 4, backgroundColor: isDarkMode ? '#1e293b' : '#f1f5f9', borderRadius: 20 }}>
                                    <WebIcon name="close" size={20} color={theme.textMuted} />
                                </TouchableOpacity>
                            </View>
                        </View>

                        <ScrollView style={{ paddingHorizontal: 24 }} showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 40, paddingTop: 20 }}>
                            {selectedPengumuman && (
                                <>
                                    {/* Meta Tags */}
                                    <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 20 }}>
                                        <View style={{
                                            flexDirection: 'row', alignItems: 'center',
                                            backgroundColor: selectedPengumuman.target_role === 'guru' ? '#f0fdf4' : (selectedPengumuman.target_role === 'siswa' ? '#eff6ff' : '#f5f3ff'),
                                            paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20,
                                            borderWidth: 1, borderColor: selectedPengumuman.target_role === 'guru' ? '#dcfce7' : (selectedPengumuman.target_role === 'siswa' ? '#dbeafe' : '#ede9fe')
                                        }}>
                                            <WebIcon name="user" size={14} color={selectedPengumuman.target_role === 'guru' ? '#16a34a' : (selectedPengumuman.target_role === 'siswa' ? '#2563eb' : '#7c3aed')} style={{ marginRight: 6 }} />
                                            <Text style={{ fontSize: 12, fontWeight: 'bold', color: selectedPengumuman.target_role === 'guru' ? '#15803d' : (selectedPengumuman.target_role === 'siswa' ? '#1d4ed8' : '#6d28d9') }}>
                                                {selectedPengumuman.target_role === 'semua' ? t('semuaWarga') : (selectedPengumuman.target_role === 'guru' ? t('khususGuru') : t('khususSiswa'))}
                                            </Text>
                                        </View>

                                        <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: isDarkMode ? '#334155' : '#f8fafc', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20 }}>
                                            <WebIcon name="calendar" size={14} color={theme.textMuted} style={{ marginRight: 6 }} />
                                            <Text style={{ fontSize: 12, color: theme.textMuted }}>
                                                {new Date(selectedPengumuman.tanggal_publish).toLocaleDateString(language === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'long', year: 'numeric' })}
                                            </Text>
                                        </View>
                                    </View>

                                    <Text style={{ fontSize: 26, fontWeight: '900', color: theme.text, marginBottom: 20, lineHeight: 34 }}>{selectedPengumuman.judul}</Text>

                                    <View style={{ backgroundColor: isDarkMode ? 'rgba(255,255,255,0.05)' : '#f8fafc', padding: 20, borderRadius: 16 }}>
                                        <Text style={{ fontSize: 16, color: theme.text, lineHeight: 28 }}>{selectedPengumuman.isi}</Text>
                                    </View>

                                    <View style={{ marginTop: 30, alignItems: 'center' }}>
                                        <Text style={{ fontSize: 12, color: theme.textMuted, textAlign: 'center' }}>
                                            {t('diterbitkanPada')} {new Date(selectedPengumuman.created_at).toLocaleString()}
                                        </Text>
                                    </View>
                                </>
                            )}
                        </ScrollView>

                        {/* Footer Close Button */}
                        <View style={{ padding: 24, borderTopWidth: 1, borderTopColor: theme.border }}>
                            <TouchableOpacity
                                style={{ backgroundColor: theme.primary, padding: 16, borderRadius: 16, alignItems: 'center', shadowColor: theme.primary, shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 10 }}
                                onPress={() => setDetailModalVisible(false)}
                            >
                                <Text style={{ color: 'white', fontWeight: 'bold', fontSize: 16 }}>{t('tutup')}</Text>
                            </TouchableOpacity>
                        </View>
                    </Animated.View>
                </View>
            </Modal>

            {/* Global QR Modal */}
            <Modal visible={qrModalVisible} transparent animationType="fade">
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { backgroundColor: theme.card }]}>
                        <View style={styles.modalHeaderFlex}>
                            <Text style={[styles.modalTitleWeb, { color: theme.text }]}>QR Code Absensi</Text>
                            <TouchableOpacity onPress={() => setQrModalVisible(false)}>
                                <WebIcon name="close" size={24} color={theme.textMuted} />
                            </TouchableOpacity>
                        </View>
                        <Text style={[styles.modalSubWeb, { color: theme.textMuted }]}>Tunjukkan QR Code ini untuk absensi</Text>
                        <View style={[styles.qrInnerBox, { backgroundColor: isDarkMode ? '#0f172a' : '#f5f3ff' }]}>
                            <QRCode value={userData?.user?.kode_qr || 'EMPTY'} size={210} color={isDarkMode ? '#ffffff' : '#000000'} backgroundColor="transparent" />
                        </View>
                        <View style={[styles.qrValueBox, { backgroundColor: isDarkMode ? '#334155' : '#f9fafb' }]}><Text style={[styles.qrValueTxt, { color: isDarkMode ? '#e2e8f0' : '#4b5563' }]}>{userData?.user?.kode_qr}</Text></View>
                        <TouchableOpacity style={[styles.btnDownloadWeb, { backgroundColor: theme.primary }]}>
                            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                <WebIcon name="download" size={20} color="white" style={{ marginRight: 10 }} />
                                <Text style={styles.btnDownloadTxt}>Download QR Code</Text>
                            </View>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>

            {/* Profile Photo Modal */}
            <Modal visible={profileModalVisible} transparent animationType="slide">
                <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.9)', justifyContent: 'center', alignItems: 'center' }}>
                    <TouchableOpacity
                        style={{ position: 'absolute', top: 50, right: 20, zIndex: 10, padding: 10, backgroundColor: 'rgba(255,255,255,0.2)', borderRadius: 20 }}
                        onPress={() => setProfileModalVisible(false)}
                    >
                        <WebIcon name="close" size={30} color="white" />
                    </TouchableOpacity>

                    <View style={{ width: width - 40, height: width - 40, borderRadius: 20, overflow: 'hidden', borderWidth: 2, borderColor: 'rgba(255,255,255,0.2)', backgroundColor: '#1e293b' }}>
                        {userData?.user?.foto_profil ? (
                            <Image
                                source={{ uri: `${BASE_URL}/uploads/${userData?.role}/${encodeURIComponent(userData?.user?.foto_profil)}` }}
                                style={{ width: '100%', height: '100%' }}
                                resizeMode="cover"
                            />
                        ) : (
                            <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
                                <WebIcon name="user" size={100} color="#cbd5e1" />
                            </View>
                        )}
                    </View>

                    <Text style={{ color: 'white', fontSize: 24, fontWeight: 'bold', marginTop: 30 }}>{userData?.user?.nama}</Text>
                    <Text style={{ color: '#94a3b8', fontSize: 16, marginTop: 5 }}>{userData?.user?.nama_kelas || (userData?.role === 'guru' ? 'Guru' : 'Siswa')}</Text>
                </View>
            </Modal>
            {/* Menu Modal - Detail Lengkap */}
            <Modal visible={menuModalVisible} transparent animationType="slide">
                <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' }}>
                    <TouchableOpacity style={{ flex: 1 }} onPress={() => setMenuModalVisible(false)} />
                    <View style={{
                        backgroundColor: theme.card,
                        borderTopLeftRadius: 30,
                        borderTopRightRadius: 30,
                        padding: 30,
                        paddingBottom: 50,
                        shadowColor: "#000",
                        shadowOffset: { width: 0, height: -10 },
                        shadowOpacity: 0.1,
                        shadowRadius: 20,
                        elevation: 20
                    }}>
                        <View style={{ alignSelf: 'center', width: 50, height: 6, borderRadius: 3, backgroundColor: theme.border, marginBottom: 25 }} />

                        <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 25 }}>
                            <WebIcon name="fileText" size={28} color={theme.primary} style={{ marginRight: 15 }} />
                            <Text style={{ fontSize: 24, fontWeight: 'bold', color: theme.text }}>Menu Lengkap</Text>
                        </View>

                        {[
                            { label: 'Biodata Lengkap', desc: 'Lihat data diri secara penuh', icon: 'fileText', color: '#3b82f6' },
                            { label: 'Edit Profil', desc: 'Perbarui nama & foto profil', icon: 'user', color: '#8b5cf6' },
                            { label: 'Ganti Password', desc: 'Amankan akun anda', icon: 'lock', color: '#f59e0b' },
                            { label: 'Pusat Bantuan', desc: 'Laporkan masalah & FAQ', icon: 'info', color: '#10b981' },
                        ].map((m, i) => (
                            <TouchableOpacity
                                key={i}
                                style={{ flexDirection: 'row', alignItems: 'center', paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: isDarkMode ? '#334155' : '#f1f5f9' }}
                                onPress={() => {
                                    setMenuModalVisible(false);
                                    showCustomAlert(m.label, "Fitur ini akan segera hadir.", [], 'info');
                                }}
                            >
                                <View style={{ width: 44, height: 44, borderRadius: 22, backgroundColor: isDarkMode ? m.color + '20' : m.color + '15', justifyContent: 'center', alignItems: 'center', marginRight: 16 }}>
                                    <WebIcon name={m.icon} size={22} color={m.color} />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={{ fontSize: 16, fontWeight: 'bold', color: theme.text }}>{m.label}</Text>
                                    <Text style={{ fontSize: 13, color: theme.textMuted }}>{m.desc}</Text>
                                </View>
                                <WebIcon name="back" size={18} color={theme.textMuted} style={{ transform: [{ rotate: '180deg' }] }} />
                            </TouchableOpacity>
                        ))}

                        <TouchableOpacity
                            style={{ marginTop: 25, backgroundColor: isDarkMode ? '#334155' : '#f1f5f9', padding: 18, borderRadius: 16, alignItems: 'center' }}
                            onPress={() => setMenuModalVisible(false)}
                        >
                            <Text style={{ fontWeight: 'bold', color: theme.text }}>Tutup Menu</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>

            {/* Top Up Input Modal */}
            <Modal visible={showTopUpModal} transparent animationType="fade">
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { backgroundColor: theme.card, width: '90%' }]}>
                        <Text style={[styles.modalTitleWeb, { color: theme.text, marginBottom: 20 }]}>Top Up Saldo</Text>

                        <View style={{ width: '100%', marginBottom: 20 }}>
                            <Text style={{ color: theme.text, marginBottom: 8, fontWeight: 'bold' }}>Nominal Top Up</Text>
                            <TextInput
                                style={[styles.input, { color: theme.text, borderColor: theme.border, backgroundColor: isDarkMode ? '#1e293b' : 'white' }]}
                                placeholder="Min. Rp 10.000"
                                placeholderTextColor={theme.textMuted}
                                keyboardType="numeric"
                                value={topUpAmount}
                                onChangeText={setTopUpAmount}
                            />
                        </View>

                        <View style={{ flexDirection: 'row', width: '100%', gap: 10 }}>
                            <TouchableOpacity
                                style={{ flex: 1, padding: 16, backgroundColor: isDarkMode ? '#334155' : '#e2e8f0', borderRadius: 12, alignItems: 'center' }}
                                onPress={() => setShowTopUpModal(false)}
                            >
                                <Text style={{ color: theme.text, fontWeight: 'bold' }}>Batal</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={{ flex: 1, padding: 16, backgroundColor: theme.primary, borderRadius: 12, alignItems: 'center' }}
                                onPress={handleTopUp}
                            >
                                <Text style={{ color: 'white', fontWeight: 'bold' }}>Lanjut Bayar</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            {/* Payment Webview Modal */}
            <Modal visible={showPaymentModal} animationType="slide" onRequestClose={() => setShowPaymentModal(false)}>
                <SafeAreaView style={{ flex: 1, backgroundColor: 'white' }}>
                    <View style={{ flexDirection: 'row', alignItems: 'center', padding: 16, borderBottomWidth: 1, borderBottomColor: '#e2e8f0' }}>
                        <TouchableOpacity onPress={() => { setShowPaymentModal(false); fetchSaldo(); }}>
                            <WebIcon name="close" size={24} color="black" />
                        </TouchableOpacity>
                        <Text style={{ fontSize: 18, fontWeight: 'bold', marginLeft: 16 }}>Pembayaran</Text>
                    </View>
                    {paymentUrl && (
                        <WebView
                            source={{ uri: paymentUrl }}
                            style={{ flex: 1 }}
                            onNavigationStateChange={(navState) => {
                                // Detect if payment finished/redirected
                                if (navState.url.includes('finish') || navState.url.includes('example.com')) {
                                    // Assuming user configured callback to example.com or similar
                                    // In real scenario midtrans redirects to main site or something.
                                }
                            }}
                        />
                    )}
                </SafeAreaView>
            </Modal>

            {/* Top Up Input Modal */}
            <Modal visible={showTopUpModal} transparent animationType="fade">
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { backgroundColor: theme.card, width: '90%' }]}>
                        <Text style={[styles.modalTitleWeb, { color: theme.text, marginBottom: 20 }]}>Top Up Saldo</Text>

                        <View style={{ width: '100%', marginBottom: 20 }}>
                            <Text style={{ color: theme.text, marginBottom: 8, fontWeight: 'bold' }}>Nominal Top Up</Text>
                            <TextInput
                                style={[styles.input, { color: theme.text, borderColor: theme.border, backgroundColor: isDarkMode ? '#1e293b' : 'white' }]}
                                placeholder="Min. Rp 10.000"
                                placeholderTextColor={theme.textMuted}
                                keyboardType="numeric"
                                value={topUpAmount}
                                onChangeText={setTopUpAmount}
                            />
                        </View>

                        <View style={{ flexDirection: 'row', width: '100%', gap: 10 }}>
                            <TouchableOpacity
                                style={{ flex: 1, padding: 16, backgroundColor: isDarkMode ? '#334155' : '#e2e8f0', borderRadius: 12, alignItems: 'center' }}
                                onPress={() => setShowTopUpModal(false)}
                            >
                                <Text style={{ color: theme.text, fontWeight: 'bold' }}>Batal</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={{ flex: 1, padding: 16, backgroundColor: theme.primary, borderRadius: 12, alignItems: 'center' }}
                                onPress={handleTopUp}
                            >
                                <Text style={{ color: 'white', fontWeight: 'bold' }}>Lanjut Bayar</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            {/* Payment Webview Modal */}
            <Modal visible={showPaymentModal} animationType="slide" onRequestClose={() => setShowPaymentModal(false)}>
                <SafeAreaView style={{ flex: 1, backgroundColor: 'white' }}>
                    <View style={{ flexDirection: 'row', alignItems: 'center', padding: 16, borderBottomWidth: 1, borderBottomColor: '#e2e8f0' }}>
                        <TouchableOpacity onPress={() => { setShowPaymentModal(false); fetchSaldo(); }}>
                            <WebIcon name="close" size={24} color="black" />
                        </TouchableOpacity>
                        <Text style={{ fontSize: 18, fontWeight: 'bold', marginLeft: 16 }}>Pembayaran</Text>
                    </View>
                    {paymentUrl && (
                        <WebView
                            source={{ uri: paymentUrl }}
                            style={{ flex: 1 }}
                            onNavigationStateChange={async (navState) => {
                                // Midtrans default redirect is often example.com if not configured
                                if (navState.url.includes('example.com') || navState.url.includes('finish') || navState.url.includes('gopay/partner/app')) {
                                    setShowPaymentModal(false);

                                    // Manual Check Status to Backend
                                    try {
                                        if (currentOrderId) {
                                            const checkResp = await fetch(`${BASE_URL}/app/api/payment/check_status.php?order_id=${currentOrderId}`);
                                            const checkResult = await checkResp.json();
                                            if (checkResult.status === 'success') {
                                                showCustomAlert('Berhasil', 'Pembayaran berhasil dikonfirmasi!', [], 'success');
                                            }
                                        }
                                    } catch (e) {
                                        console.log("Check status error", e);
                                    }

                                    fetchSaldo(); // Refresh balance
                                }
                            }}
                        />
                    )}
                </SafeAreaView>
            </Modal>

            {/* --- GLOBAL CUSTOM ALERT MODAL --- */}
            <Modal
                visible={alertConfig.visible}
                transparent={true}
                animationType="fade"
                onRequestClose={closeCustomAlert}
            >
                <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center', padding: 20 }}>
                    <View style={{ backgroundColor: theme.card, borderRadius: 24, padding: 24, width: '100%', maxWidth: 340, alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.25, shadowRadius: 10, elevation: 10 }}>

                        {/* Icon Based on Type */}
                        <View style={{
                            width: 72, height: 72, borderRadius: 36,
                            backgroundColor: alertConfig.type === 'success' ? '#dcfce7' : (alertConfig.type === 'error' ? '#fee2e2' : (alertConfig.type === 'warning' ? '#fef9c3' : '#e0e7ff')),
                            justifyContent: 'center', alignItems: 'center', marginBottom: 20
                        }}>
                            <WebIcon
                                name={alertConfig.type === 'success' ? 'check' : (alertConfig.type === 'error' ? 'close' : (alertConfig.type === 'warning' ? 'tag' : 'info'))}
                                size={32}
                                color={alertConfig.type === 'success' ? '#16a34a' : (alertConfig.type === 'error' ? '#ef4444' : (alertConfig.type === 'warning' ? '#ca8a04' : '#4f46e5'))}
                            />
                        </View>

                        <Text style={{ fontSize: 20, fontWeight: 'bold', color: theme.text, textAlign: 'center', marginBottom: 12 }}>
                            {alertConfig.title}
                        </Text>

                        <Text style={{ fontSize: 15, color: theme.textMuted, textAlign: 'center', lineHeight: 22, marginBottom: 28 }}>
                            {alertConfig.message}
                        </Text>

                        <View style={{ flexDirection: 'row', justifyContent: 'center', width: '100%', gap: 12 }}>
                            {alertConfig.buttons.map((btn, index) => (
                                <TouchableOpacity
                                    key={index}
                                    style={{
                                        flex: 1,
                                        backgroundColor: btn.style === 'cancel' ? (isDarkMode ? '#334155' : '#f1f5f9') : theme.primary,
                                        paddingVertical: 14,
                                        borderRadius: 14,
                                        alignItems: 'center'
                                    }}
                                    onPress={() => {
                                        closeCustomAlert();
                                        if (btn.onPress) btn.onPress();
                                    }}
                                >
                                    <Text style={{ color: btn.style === 'cancel' ? theme.text : 'white', fontWeight: 'bold', fontSize: 16 }}>
                                        {btn.text}
                                    </Text>
                                </TouchableOpacity>
                            ))}
                        </View>
                    </View>
                </View>
            </Modal>
        </SafeAreaView >
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    // Login
    loginContainer: { flex: 1, padding: 30, justifyContent: 'center' },
    loginHeader: { alignItems: 'center', marginBottom: 50 },
    logoCircle: { width: 80, height: 80, borderRadius: 40, justifyContent: 'center', alignItems: 'center', marginBottom: 20 },
    loginTitle: { fontSize: 28, fontWeight: '900' },
    loginSubtitle: { fontSize: 16, marginTop: 8 },
    loginForm: { width: '100%' },
    inputWrapper: { marginBottom: 20 },
    inputLabel: { fontSize: 14, fontWeight: '600', marginBottom: 8 },
    input: { padding: 16, borderRadius: 12, fontSize: 16, borderWidth: 1 },
    loginButton: { padding: 18, borderRadius: 12, alignItems: 'center', marginTop: 10, shadowColor: "#4f46e5", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 10, elevation: 5 },
    loginButtonText: { color: 'white', fontSize: 16, fontWeight: 'bold' },

    // Dashboard Wrapper
    dashboardWrapper: { flex: 1 },
    scrollView: { flex: 1 },
    mainContent: { paddingHorizontal: 20, marginTop: -40 },

    // Web Style Header
    webHeader: { backgroundColor: '#7c3aed', paddingBottom: 30, paddingTop: 50, paddingHorizontal: 20 },
    headerFlex: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    webHeaderTitle: { color: 'white', fontSize: 22, fontWeight: 'bold' },
    webHeaderSubtitle: { color: '#ddd6fe', fontSize: 14, marginTop: 4 },

    // Profile Card
    webProfileCard: { backgroundColor: '#7c3aed', borderRadius: 24, padding: 24, flexDirection: 'row', alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.2, shadowRadius: 20, elevation: 15 },
    avatarCircle: { width: 68, height: 68, borderRadius: 34, backgroundColor: 'rgba(255,255,255,0.2)', justifyContent: 'center', alignItems: 'center', borderWidth: 2, borderColor: 'rgba(255,255,255,0.3)', overflow: 'hidden' },
    avatarCircleLarge: { width: 160, height: 160, borderRadius: 80, backgroundColor: 'rgba(255,255,255,0.1)', justifyContent: 'center', alignItems: 'center', borderWidth: 6, borderColor: 'white', overflow: 'hidden' },
    avatarImg: { width: '100%', height: '100%', borderRadius: 34 },
    avatarImgLarge: { width: '100%', height: '100%' },
    profileTextInfo: { flex: 1, marginLeft: 16 },
    profileNameTxt: { fontSize: 20, fontWeight: 'bold', color: 'white', marginBottom: 4 },
    badgeRow: { flexDirection: 'row', alignItems: 'center', marginTop: 2 },
    badgeLabel: { color: '#ddd6fe', fontSize: 13, fontWeight: '600' },
    badgeValue: { color: 'white', fontSize: 13, marginLeft: 6 },

    // Info List Style
    infoItemCard: { flexDirection: 'row', alignItems: 'center', padding: 20, borderRadius: 24, marginBottom: 12, shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 3 },
    infoIconCircle: { width: 48, height: 48, borderRadius: 16, justifyContent: 'center', alignItems: 'center' },

    // Status Section
    webStatusContainer: { borderRadius: 24, padding: 24, marginTop: 20, shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 5 },
    sectionTitleWeb: { fontSize: 18, fontWeight: 'bold' },
    dateLabelWeb: { fontSize: 14, color: '#6b7280', marginTop: 4, marginBottom: 16 },
    statusInnerBox: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 20, borderRadius: 20, borderWidth: 2 },
    statusMainLabel: { fontSize: 20, fontWeight: '900' },
    statusJam: { fontSize: 14, marginTop: 4 },
    checkCircle: { width: 44, height: 44, borderRadius: 22, justifyContent: 'center', alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 },
    checkIcon: { fontSize: 20, color: 'white', fontWeight: 'bold' },

    // New Status Styles
    typeBadge: { paddingHorizontal: 12, paddingVertical: 4, borderRadius: 10 },
    typeBadgeTxt: { fontSize: 11, fontWeight: 'bold' },
    roleLabel: { fontSize: 13, marginTop: 2 },
    timeInfoItem: { flex: 1 },
    timeLabel: { fontSize: 12, color: '#94a3b8', marginBottom: 2 },
    timeValue: { fontSize: 18, fontWeight: 'bold' },

    // Menu Grid
    webGrid: { marginTop: 20 },
    webRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 16 },
    webMenuCard: { width: (width - 56) / 2, padding: 24, borderRadius: 24, alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2 },
    iconCircleWeb: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center', marginBottom: 16 },
    menuTitleWeb: { fontSize: 16, fontWeight: 'bold' },
    menuSubWeb: { fontSize: 12, marginTop: 4, textAlign: 'center' },

    // Bottom Nav
    webBottomNav: { position: 'absolute', bottom: 0, left: 0, right: 0, height: 100, flexDirection: 'row', justifyContent: 'space-between', paddingHorizontal: 50, alignItems: 'center', borderTopWidth: 1, zIndex: 1000, elevation: 50, paddingBottom: 10 },
    navBtn: { alignItems: 'center' },
    navBtnCenter: { top: -25, alignItems: 'center' },
    qrCircleFab: { width: 68, height: 68, borderRadius: 34, backgroundColor: '#7c3aed', justifyContent: 'center', alignItems: 'center', shadowColor: "#7c3aed", shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.3, shadowRadius: 15, elevation: 10 },
    navLabelBlue: { fontSize: 11, fontWeight: 'bold', color: '#3b82f6', marginTop: 4 },
    navLabelGray: { fontSize: 11, marginTop: 4 },
    navLabelCenter: { fontSize: 11, fontWeight: 'bold', marginTop: 8 },

    // Logout Button Profile
    logoutBtnFull: { backgroundColor: '#ef4444', height: 65, borderRadius: 20, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', marginTop: 10, shadowColor: "#ef4444", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8, elevation: 5 },
    logoutBtnText: { color: 'white', fontSize: 18, fontWeight: 'bold' },

    // Modal
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.85)', justifyContent: 'center', padding: 24 },
    modalContent: { borderRadius: 32, padding: 32, alignItems: 'center' },
    modalHeaderFlex: { flexDirection: 'row', justifyContent: 'space-between', width: '100%', alignItems: 'center', marginBottom: 12 },
    modalTitleWeb: { fontSize: 24, fontWeight: 'bold' },
    modalSubWeb: { fontSize: 14, marginBottom: 30 },
    qrInnerBox: { padding: 24, borderRadius: 24, marginBottom: 24 },
    qrValueBox: { padding: 16, borderRadius: 12, width: '100%', marginBottom: 24 },
    qrValueTxt: { textAlign: 'center', fontFamily: 'monospace' },
    btnDownloadWeb: { width: '100%', padding: 18, borderRadius: 16, alignItems: 'center' },
    btnDownloadTxt: { color: 'white', fontWeight: 'bold', fontSize: 16 },

    // Scanner
    scannerContainer: { flex: 1, backgroundColor: 'black' },
    overlayScanner: { position: 'absolute', bottom: 50, width: '100%', alignItems: 'center' },
    scanText: { color: 'white', fontSize: 16, marginBottom: 20, backgroundColor: 'rgba(0,0,0,0.6)', padding: 12, borderRadius: 12 },
    closeBtnScanner: { backgroundColor: 'white', paddingVertical: 14, paddingHorizontal: 40, borderRadius: 30 },
    closeTxtScanner: { fontWeight: 'bold' }
});
