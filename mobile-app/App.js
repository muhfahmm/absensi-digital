import React, { useState, useEffect } from 'react';
import { StyleSheet, Text, View, TextInput, TouchableOpacity, Alert, ActivityIndicator, SafeAreaView, Modal, ScrollView, Image, Dimensions, useColorScheme } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { CameraView, Camera } from "expo-camera";
import QRCode from 'react-native-qrcode-svg';
import Svg, { Path } from 'react-native-svg';

const { width, height } = Dimensions.get('window');

// GANTI IP INI SESUAI IP KOMPUTER ANDA!
const BASE_URL = 'http://192.168.0.103/absensi-digital%203';

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
    calendar: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
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

export default function App() {
    const systemTheme = useColorScheme();
    const [isDarkMode, setIsDarkMode] = useState(systemTheme === 'dark');
    const [currentView, setCurrentView] = useState('login'); // login, dashboard, scanner, kehadiran, profil, pembayaran, pengumuman
    const [userData, setUserData] = useState(null);
    const [attendanceStatus, setAttendanceStatus] = useState(null);

    // Login State
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);

    // Scanner State
    const [hasPermission, setHasPermission] = useState(null);
    const [scanned, setScanned] = useState(false);

    // Modal State
    const [qrModalVisible, setQrModalVisible] = useState(false);

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
        if (userData && currentView === 'dashboard') {
            fetchAttendanceStatus();
        }
    }, [userData, currentView]);

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

    const handleLogin = async () => {
        if (!username || !password) {
            Alert.alert('Error', 'Mohon isi username dan password');
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
                Alert.alert('Login Gagal', data.message || 'Periksa kembali data anda');
            }
        } catch (error) {
            Alert.alert('Error', 'Gagal menghubungkan ke server. Pastikan IP Address benar dan HP terhubung ke WiFi yang sama.');
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
                Alert.alert("✅ Berhasil!", `${result.data.nama}\nStatus: ${result.data.status}\nJam: ${result.data.jam_masuk}`, [{ text: "OK", onPress: () => setScanned(false) }]);
            } else {
                Alert.alert("❌ Gagal", result.message || "QR Code tidak valid", [{ text: "Scan Lagi", onPress: () => setScanned(false) }]);
            }
        } catch (error) {
            Alert.alert("Error", "Gagal memproses data", [{ text: "OK", onPress: () => setScanned(false) }]);
        }
    };

    // View Components
    const ScreenTemplate = ({ title, subtitle, showBack = true, children }) => (
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
            <ScrollView style={styles.scrollView} bounces={false}>
                <View style={[styles.mainContent, { paddingBottom: 120 }]}>
                    {children}
                </View>
            </ScrollView>

            {/* Navigasi tetap tampil di semua view dashboard kecuali login/scanner */}
            {renderBottomNav()}
        </View>
    );

    const renderBottomNav = () => {
        const role = userData?.role;
        return (
            <View style={[styles.webBottomNav, { backgroundColor: theme.bottomNav, borderTopColor: isDarkMode ? '#334155' : '#f3f4f6' }]}>
                <TouchableOpacity style={styles.navBtn} onPress={() => setCurrentView('dashboard')}>
                    <WebIcon name="home" size={24} color={currentView === 'dashboard' ? '#3b82f6' : theme.navIconIdle} />
                    <Text style={[styles.navLabelGray, currentView === 'dashboard' && styles.navLabelBlue, { color: currentView === 'dashboard' ? '#3b82f6' : theme.navIconIdle }]}>Home</Text>
                </TouchableOpacity>

                <TouchableOpacity style={styles.navBtnCenter} onPress={() => {
                    if (role === 'siswa') setQrModalVisible(true);
                    else setCurrentView('scanner');
                }}>
                    <View style={styles.qrCircleFab}>
                        <WebIcon name={role === 'siswa' ? "qr" : "search"} size={32} color="white" />
                    </View>
                    <Text style={[styles.navLabelCenter, { color: theme.text }]}>QR Code</Text>
                </TouchableOpacity>

                <TouchableOpacity style={styles.navBtn} onPress={() => setCurrentView('profil')}>
                    <WebIcon name="user" size={24} color={currentView === 'profil' ? '#3b82f6' : theme.navIconIdle} />
                    <Text style={[styles.navLabelGray, currentView === 'profil' && styles.navLabelBlue, { color: currentView === 'profil' ? '#3b82f6' : theme.navIconIdle }]}>Profil</Text>
                </TouchableOpacity>
            </View>
        );
    }

    // UI Renders
    const renderLogin = () => (
        <View style={[styles.loginContainer, { backgroundColor: theme.card }]}>
            <View style={styles.loginHeader}>
                <View style={[styles.logoCircle, { backgroundColor: theme.primary }]}>
                    <WebIcon name="cap" size={40} color="white" />
                </View>
                <Text style={[styles.loginTitle, { color: theme.text }]}>Absensi Digital</Text>
                <Text style={[styles.loginSubtitle, { color: theme.textMuted }]}>Silakan login untuk melanjutkan</Text>
            </View>

            <View style={styles.loginForm}>
                <View style={styles.inputWrapper}>
                    <Text style={[styles.inputLabel, { color: theme.text }]}>Username / NIS / NIP</Text>
                    <TextInput
                        style={[styles.input, { backgroundColor: isDarkMode ? '#0f172a' : '#f1f5f9', color: theme.text, borderColor: theme.border }]}
                        value={username}
                        onChangeText={setUsername}
                        autoCapitalize="none"
                        placeholder="Contoh: 12345"
                        placeholderTextColor={theme.textMuted}
                    />
                </View>

                <View style={styles.inputWrapper}>
                    <Text style={[styles.inputLabel, { color: theme.text }]}>Password</Text>
                    <TextInput
                        style={[styles.input, { backgroundColor: isDarkMode ? '#0f172a' : '#f1f5f9', color: theme.text, borderColor: theme.border }]}
                        value={password}
                        onChangeText={setPassword}
                        secureTextEntry
                        placeholder="••••••••"
                        placeholderTextColor={theme.textMuted}
                    />
                </View>

                <TouchableOpacity style={[styles.loginButton, { backgroundColor: theme.primary }]} onPress={handleLogin} disabled={loading}>
                    {loading ? <ActivityIndicator color="white" /> : <Text style={styles.loginButtonText}>Masuk Ke Akun</Text>}
                </TouchableOpacity>
            </View>
        </View>
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
                <ScrollView style={styles.scrollView} bounces={false}>
                    {/* Header */}
                    <View style={styles.webHeader}>
                        <View style={styles.headerFlex}>
                            <View>
                                <Text style={styles.webHeaderTitle}>Dashboard Siswa</Text>
                                <Text style={styles.webHeaderSubtitle}>{user.nama} • {user.nama_kelas || role.toUpperCase()}</Text>
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

                    <View style={styles.mainContent}>
                        {/* Student Detail Card */}
                        <TouchableOpacity style={styles.webProfileCard} onPress={() => setCurrentView('profil')}>
                            <View style={styles.avatarCircle}>
                                {user.foto_profil ? (
                                    <Image source={{ uri: `${BASE_URL}/uploads/${role}/${user.foto_profil}` }} style={styles.avatarImg} />
                                ) : (
                                    <WebIcon name="user" size={32} color="white" />
                                )}
                            </View>
                            <View style={styles.profileTextInfo}>
                                <Text style={styles.profileNameTxt}>{user.nama}</Text>
                                <View style={styles.badgeRow}>
                                    <WebIcon name="tag" size={14} color="#ddd6fe" style={{ marginRight: 8 }} />
                                    <Text style={styles.badgeLabel}>NIS:</Text>
                                    <Text style={styles.badgeValue}>{user.nis || '-'}</Text>
                                </View>
                                <View style={styles.badgeRow}>
                                    <WebIcon name="building" size={14} color="#ddd6fe" style={{ marginRight: 8 }} />
                                    <Text style={styles.badgeLabel}>Kelas:</Text>
                                    <Text style={styles.badgeValue}>{user.nama_kelas || '-'}</Text>
                                </View>
                            </View>
                        </TouchableOpacity>

                        {/* Status Absensi Card */}
                        <View style={[styles.webStatusContainer, { backgroundColor: theme.card }]}>
                            <Text style={[styles.sectionTitleWeb, { color: theme.text }]}>Status Absensi Hari Ini</Text>
                            <Text style={styles.dateLabelWeb}>{new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</Text>

                            <View style={[styles.statusInnerBox, { backgroundColor: statusStyle.bg, borderColor: statusStyle.border }]}>
                                <View>
                                    <Text style={[styles.statusMainLabel, { color: statusStyle.text }]}>
                                        {attendanceStatus ? attendanceStatus.status.toUpperCase() : "BELUM ABSEN"}
                                    </Text>
                                    {attendanceStatus?.jam_masuk && <Text style={[styles.statusJam, { color: isDarkMode ? '#cbd5e1' : '#4b5563' }]}>Jam: {attendanceStatus.jam_masuk}</Text>}
                                    {!attendanceStatus && <Text style={[styles.statusJam, { color: isDarkMode ? '#cbd5e1' : '#4b5563' }]}>Segera lakukan absensi</Text>}
                                </View>
                                <View style={[styles.checkCircle, { backgroundColor: attendanceStatus ? '#b45309' : '#fef08a' }]}>
                                    <Text style={styles.checkIcon}>{attendanceStatus ? '✓' : '!'}</Text>
                                </View>
                            </View>
                        </View>

                        {/* Menu Grid */}
                        <View style={styles.webGrid}>
                            <View style={styles.webRow}>
                                <WebMenuItem iconName="clipboard" iconColor="#2563eb" iconBg="#dbeafe" title="Kehadiran" sub="Riwayat absensi" onPress={() => setCurrentView('kehadiran')} theme={theme} isDarkMode={isDarkMode} />
                                <WebMenuItem iconName="user" iconColor="#7c3aed" iconBg="#f5f3ff" title="Profil" sub="Data pribadi" onPress={() => setCurrentView('profil')} theme={theme} isDarkMode={isDarkMode} />
                            </View>
                            <View style={styles.webRow}>
                                <WebMenuItem iconName="card" iconColor="#16a34a" iconBg="#dcfce7" title="Pembayaran" sub="Status SPP" onPress={() => setCurrentView('pembayaran')} theme={theme} isDarkMode={isDarkMode} />
                                <WebMenuItem iconName="speaker" iconColor="#ea580c" iconBg="#ffedd5" title="Pengumuman" sub="Info terbaru" onPress={() => setCurrentView('pengumuman')} theme={theme} isDarkMode={isDarkMode} />
                            </View>
                        </View>
                    </View>
                </ScrollView>

                {/* Bottom Nav */}
                {renderBottomNav()}

                {/* QR Modal */}
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
                                <QRCode value={user.kode_qr || 'EMPTY'} size={210} color={isDarkMode ? '#ffffff' : '#000000'} backgroundColor="transparent" />
                            </View>
                            <View style={[styles.qrValueBox, { backgroundColor: isDarkMode ? '#334155' : '#f9fafb' }]}><Text style={[styles.qrValueTxt, { color: isDarkMode ? '#e2e8f0' : '#4b5563' }]}>{user.kode_qr}</Text></View>
                            <TouchableOpacity style={[styles.btnDownloadWeb, { backgroundColor: theme.primary }]}>
                                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                    <WebIcon name="download" size={20} color="white" style={{ marginRight: 10 }} />
                                    <Text style={styles.btnDownloadTxt}>Download QR Code</Text>
                                </View>
                            </TouchableOpacity>
                        </View>
                    </View>
                </Modal>
            </View>
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
                    <Text style={styles.scanText}>Arahkan kamera ke QR Code Siswa</Text>
                    <TouchableOpacity style={styles.closeBtnScanner} onPress={() => setCurrentView('dashboard')}>
                        <Text style={styles.closeTxtScanner}>Batal</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    };

    const renderProfil = () => {
        const user = userData?.user;
        const role = userData?.role;
        return (
            <ScreenTemplate title="Profil Saya" subtitle="Informasi data pribadi">
                {/* Main Profile Image Card */}
                <View style={[styles.webStatusContainer, { backgroundColor: theme.card, alignItems: 'center', padding: 40, marginTop: 10 }]}>
                    <View style={[styles.avatarCircleLarge, { borderColor: 'white', elevation: 10, shadowColor: '#000', shadowOpacity: 0.1, shadowRadius: 10 }]}>
                        {user.foto_profil ? (
                            <Image source={{ uri: `${BASE_URL}/uploads/${role}/${user.foto_profil}` }} style={styles.avatarImgLarge} />
                        ) : (
                            <WebIcon name="user" size={80} color={isDarkMode ? theme.textMuted : "#cbd5e1"} />
                        )}
                    </View>
                    <Text style={[styles.profileNameTxt, { color: theme.text, fontSize: 32, marginTop: 24, fontWeight: '900' }]}>{user.nama}</Text>
                    <Text style={[styles.webHeaderSubtitle, { color: theme.textMuted, fontSize: 18, marginTop: 4 }]}>{user.nama_kelas || '-'}</Text>
                </View>

                {/* Info List */}
                <View style={{ marginTop: 20 }}>
                    <InfoCard icon="tag" iconBg="#eef2ff" iconColor="#4f46e5" label="Nomor Induk Siswa" value={user.nis || user.nip || '-'} theme={theme} isDarkMode={isDarkMode} />
                    <InfoCard icon="building" iconBg="#f5f3ff" iconColor="#7c3aed" label="Kelas" value={user.nama_kelas || '-'} theme={theme} isDarkMode={isDarkMode} />
                    <InfoCard icon="qr" iconBg="#f0fdf4" iconColor="#16a34a" label="Kode QR" value={user.kode_qr || '-'} theme={theme} isDarkMode={isDarkMode} mono />
                    <InfoCard icon="calendar" iconBg="#eff6ff" iconColor="#3b82f6" label="Terdaftar Sejak" value={new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })} theme={theme} isDarkMode={isDarkMode} />
                </View>

                {/* Logout Button */}
                <TouchableOpacity style={styles.logoutBtnFull} onPress={handleLogout}>
                    <WebIcon name="logout" size={20} color="white" style={{ marginRight: 10 }} />
                    <Text style={styles.logoutBtnText}>Keluar dari Akun</Text>
                </TouchableOpacity>
            </ScreenTemplate>
        );
    };

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
        <ScreenTemplate title={title}>
            <View style={[styles.webStatusContainer, { backgroundColor: theme.card, alignItems: 'center', padding: 50, marginTop: 10 }]}>
                <WebIcon name="speaker" size={60} color={theme.textMuted} />
                <Text style={{ color: theme.text, fontSize: 18, marginTop: 20, textAlign: 'center', fontWeight: 'bold' }}>
                    Fitur {title}
                </Text>
                <Text style={{ color: theme.textMuted, fontSize: 14, textAlign: 'center', marginTop: 8 }}>
                    Halaman ini sedang dalam pengembangan dan akan segera tersedia.
                </Text>
                <TouchableOpacity
                    style={[styles.loginButton, { backgroundColor: theme.primary, width: '100%', marginTop: 30 }]}
                    onPress={() => setCurrentView('dashboard')}
                >
                    <Text style={styles.loginButtonText}>Selesai</Text>
                </TouchableOpacity>
            </View>
        </ScreenTemplate>
    );

    return (
        <SafeAreaView style={[styles.container, { backgroundColor: isDarkMode ? '#0f172a' : '#f3f4f6' }]}>
            <StatusBar style="light" />
            {currentView === 'login' && renderLogin()}
            {currentView === 'dashboard' && renderDashboard()}
            {currentView === 'scanner' && renderScanner()}
            {currentView === 'profil' && renderProfil()}
            {currentView === 'kehadiran' && renderPlaceholder('Kehadiran')}
            {currentView === 'pembayaran' && renderPlaceholder('Pembayaran')}
            {currentView === 'pengumuman' && renderPlaceholder('Pengumuman')}
        </SafeAreaView>
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
    webHeader: { backgroundColor: '#7c3aed', paddingBottom: 60, paddingTop: 60, paddingHorizontal: 20 },
    headerFlex: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    webHeaderTitle: { color: 'white', fontSize: 22, fontWeight: 'bold' },
    webHeaderSubtitle: { color: '#ddd6fe', fontSize: 14, marginTop: 4 },

    // Profile Card
    webProfileCard: { backgroundColor: '#7c3aed', borderRadius: 24, padding: 24, flexDirection: 'row', alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.2, shadowRadius: 20, elevation: 15 },
    avatarCircle: { width: 68, height: 68, borderRadius: 34, backgroundColor: 'rgba(255,255,255,0.2)', justifyContent: 'center', alignItems: 'center', borderWidth: 2, borderColor: 'rgba(255,255,255,0.3)' },
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

    // Menu Grid
    webGrid: { marginTop: 20, paddingBottom: 100 },
    webRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 16 },
    webMenuCard: { width: (width - 56) / 2, padding: 24, borderRadius: 24, alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2 },
    iconCircleWeb: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center', marginBottom: 16 },
    menuTitleWeb: { fontSize: 16, fontWeight: 'bold' },
    menuSubWeb: { fontSize: 12, marginTop: 4, textAlign: 'center' },

    // Bottom Nav
    webBottomNav: { position: 'absolute', bottom: 0, width: '100%', height: 90, flexDirection: 'row', justifyContent: 'space-between', paddingHorizontal: 40, alignItems: 'center', borderTopWidth: 1 },
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
