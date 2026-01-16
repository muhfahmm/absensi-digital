import React, { useState, useEffect } from 'react';
import { StyleSheet, Text, View, TextInput, TouchableOpacity, Alert, ActivityIndicator, SafeAreaView, Modal, ScrollView, Image, Dimensions } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { CameraView, Camera } from "expo-camera";
import QRCode from 'react-native-qrcode-svg';

const { width } = Dimensions.get('window');

// GANTI IP INI SESUAI IP KOMPUTER ANDA!
const BASE_URL = 'http://192.168.0.103/absensi-digital%203';

export default function App() {
    const [currentView, setCurrentView] = useState('login'); // login, dashboard, scanner
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

    // UI Renders
    const renderLogin = () => (
        <View style={styles.loginContainer}>
            <View style={styles.loginHeader}>
                <View style={styles.logoCircle}>
                    <Text style={styles.logoIcon}>🎓</Text>
                </View>
                <Text style={styles.loginTitle}>Absensi Digital</Text>
                <Text style={styles.loginSubtitle}>Silakan login untuk melanjutkan</Text>
            </View>

            <View style={styles.loginForm}>
                <View style={styles.inputWrapper}>
                    <Text style={styles.inputLabel}>Username / NIS / NIP</Text>
                    <TextInput
                        style={styles.input}
                        value={username}
                        onChangeText={setUsername}
                        autoCapitalize="none"
                        placeholder="Contoh: 12345"
                        placeholderTextColor="#94a3b8"
                    />
                </View>

                <View style={styles.inputWrapper}>
                    <Text style={styles.inputLabel}>Password</Text>
                    <TextInput
                        style={styles.input}
                        value={password}
                        onChangeText={setPassword}
                        secureTextEntry
                        placeholder="••••••••"
                        placeholderTextColor="#94a3b8"
                    />
                </View>

                <TouchableOpacity style={styles.loginButton} onPress={handleLogin} disabled={loading}>
                    {loading ? <ActivityIndicator color="white" /> : <Text style={styles.loginButtonText}>Masuk Ke Akun</Text>}
                </TouchableOpacity>
            </View>
        </View>
    );

    const renderDashboard = () => {
        const user = userData.user;
        const role = userData.role;

        const getStatusStyles = (status) => {
            switch (status?.toLowerCase()) {
                case 'hadir': return { bg: '#dcfce7', border: '#bbf7d0', text: '#166534', icon: 'hadir' };
                case 'sakit': return { bg: '#dbeafe', border: '#bfdbfe', text: '#1e40af', icon: 'sakit' };
                case 'izin': return { bg: '#fef9c3', border: '#fef08a', text: '#854d0e', icon: 'izin' };
                case 'terlambat': return { bg: '#ffedd5', border: '#fed7aa', text: '#9a3412', icon: 'terlambat' };
                case 'alpa': return { bg: '#fee2e2', border: '#fecaca', text: '#991b1b', icon: 'alpa' };
                default: return { bg: '#fefce8', border: '#fef08a', text: '#854d0e', icon: 'none' };
            }
        };

        const statusStyle = getStatusStyles(attendanceStatus?.status);

        return (
            <View style={styles.dashboardWrapper}>
                <ScrollView style={styles.scrollView} bounces={false}>
                    {/* Header - EXACT REPLICA OF WEB */}
                    <View style={styles.webHeader}>
                        <View style={styles.headerFlex}>
                            <View>
                                <Text style={styles.webHeaderTitle}>Dashboard Siswa</Text>
                                <Text style={styles.webHeaderSubtitle}>{user.nama} • {user.nama_kelas || role.toUpperCase()}</Text>
                            </View>
                            <TouchableOpacity onPress={handleLogout}>
                                <Text style={styles.webLogoutIcon}>🚪</Text>
                            </TouchableOpacity>
                        </View>
                    </View>

                    <View style={styles.mainContent}>
                        {/* Student Detail Card - PURPLE GRADIENT */}
                        <View style={styles.webProfileCard}>
                            <View style={styles.avatarCircle}>
                                {user.foto_profil ? (
                                    <Image source={{ uri: `${BASE_URL}/uploads/${role}/${user.foto_profil}` }} style={styles.avatarImg} />
                                ) : (
                                    <Text style={styles.avatarPlaceholderIcon}>👤</Text>
                                )}
                            </View>
                            <View style={styles.profileTextInfo}>
                                <Text style={styles.profileNameTxt}>{user.nama}</Text>
                                <View style={styles.badgeRow}>
                                    <Text style={styles.badgeIcon}>🏷️</Text>
                                    <Text style={styles.badgeLabel}>NIS:</Text>
                                    <Text style={styles.badgeValue}>{user.nis || '-'}</Text>
                                </View>
                                <View style={styles.badgeRow}>
                                    <Text style={styles.badgeIcon}>🏫</Text>
                                    <Text style={styles.badgeLabel}>Kelas:</Text>
                                    <Text style={styles.badgeValue}>{user.nama_kelas || '-'}</Text>
                                </View>
                            </View>
                        </View>

                        {/* Status Absensi Card */}
                        <View style={styles.webStatusContainer}>
                            <Text style={styles.sectionTitleWeb}>Status Absensi Hari Ini</Text>
                            <Text style={styles.dateLabelWeb}>{new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</Text>

                            <View style={[styles.statusInnerBox, { backgroundColor: statusStyle.bg, borderColor: statusStyle.border }]}>
                                <View>
                                    <Text style={[styles.statusMainLabel, { color: statusStyle.text }]}>
                                        {attendanceStatus ? attendanceStatus.status.toUpperCase() : "BELUM ABSEN"}
                                    </Text>
                                    {attendanceStatus?.jam_masuk && <Text style={styles.statusJam}>Jam: {attendanceStatus.jam_masuk}</Text>}
                                    {!attendanceStatus && <Text style={styles.statusJam}>Segera lakukan absensi</Text>}
                                </View>
                                <View style={[styles.checkCircle, { backgroundColor: attendanceStatus ? '#b45309' : '#fef08a' }]}>
                                    <Text style={styles.checkIcon}>{attendanceStatus ? '✓' : '!'}</Text>
                                </View>
                            </View>
                        </View>

                        {/* Menu Grid */}
                        <View style={styles.webGrid}>
                            <View style={styles.webRow}>
                                <WebMenuItem icon="📋" iconBg="#dbeafe" title="Kehadiran" sub="Riwayat absensi" />
                                <WebMenuItem icon="👤" iconBg="#f5f3ff" title="Profil" sub="Data pribadi" />
                            </View>
                            <View style={styles.webRow}>
                                <WebMenuItem icon="💳" iconBg="#dcfce7" title="Pembayaran" sub="Status SPP" />
                                <WebMenuItem icon="📢" iconBg="#ffedd5" title="Pengumuman" sub="Info terbaru" />
                            </View>
                        </View>
                    </View>
                </ScrollView>

                {/* Bottom Nav */}
                <View style={styles.webBottomNav}>
                    <TouchableOpacity style={styles.navBtn}>
                        <Text style={styles.navIconBlue}>🏠</Text>
                        <Text style={styles.navLabelBlue}>Home</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.navBtnCenter} onPress={() => setQrModalVisible(true)}>
                        <View style={styles.qrCircleFab}>
                            <Text style={styles.qrFabEmoji}>📱</Text>
                        </View>
                        <Text style={styles.navLabelCenter}>QR Code</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.navBtn}>
                        <Text style={styles.navIconGray}>👤</Text>
                        <Text style={styles.navLabelGray}>Profil</Text>
                    </TouchableOpacity>
                </View>

                {/* QR Modal */}
                <Modal visible={qrModalVisible} transparent animationType="fade">
                    <View style={styles.modalOverlay}>
                        <View style={styles.modalContent}>
                            <View style={styles.modalHeaderFlex}>
                                <Text style={styles.modalTitleWeb}>QR Code Absensi</Text>
                                <TouchableOpacity onPress={() => setQrModalVisible(false)}><Text style={styles.closeModalTxt}>✕</Text></TouchableOpacity>
                            </View>
                            <Text style={styles.modalSubWeb}>Tunjukkan QR Code ini untuk absensi</Text>
                            <View style={styles.qrInnerBox}>
                                <QRCode value={user.kode_qr || 'EMPTY'} size={210} color="#4f46e5" />
                            </View>
                            <View style={styles.qrValueBox}><Text style={styles.qrValueTxt}>{user.kode_qr}</Text></View>
                            <TouchableOpacity style={styles.btnDownloadWeb}><Text style={styles.btnDownloadTxt}>Download QR Code</Text></TouchableOpacity>
                        </View>
                    </View>
                </Modal>
            </View>
        );
    };

    const WebMenuItem = ({ icon, iconBg, title, sub }) => (
        <TouchableOpacity style={styles.webMenuCard}>
            <View style={[styles.iconCircleWeb, { backgroundColor: iconBg }]}>
                <Text style={styles.iconEmojiWeb}>{icon}</Text>
            </View>
            <Text style={styles.menuTitleWeb}>{title}</Text>
            <Text style={styles.menuSubWeb}>{sub}</Text>
        </TouchableOpacity>
    );

    const renderScanner = () => {
        if (hasPermission === null) return <Text>Requesting for camera permission</Text>;
        if (hasPermission === false) return <Text>No access to camera</Text>;
        return (
            <View style={styles.scannerContainer}>
                <CameraView style={StyleSheet.absoluteFillObject} onBarcodeScanned={scanned ? undefined : handleBarCodeScanned} barcodeScannerSettings={{ barcodeTypes: ["qr"] }} />
                <View style={styles.overlayScanner}><Text style={styles.scanText}>Arahkan kamera ke QR Code Siswa</Text><TouchableOpacity style={styles.closeBtnScanner} onPress={() => setCurrentView('dashboard')}><Text style={styles.closeTxtScanner}>Batal</Text></TouchableOpacity></View>
            </View>
        );
    };

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar style="light" />
            {currentView === 'login' && renderLogin()}
            {currentView === 'dashboard' && renderDashboard()}
            {currentView === 'scanner' && renderScanner()}
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f3f4f6' },
    // Login
    loginContainer: { flex: 1, padding: 30, justifyContent: 'center', backgroundColor: '#ffffff' },
    loginHeader: { alignItems: 'center', marginBottom: 50 },
    logoCircle: { width: 80, height: 80, borderRadius: 40, backgroundColor: '#4f46e5', justifyContent: 'center', alignItems: 'center', marginBottom: 20 },
    logoIcon: { fontSize: 40 },
    loginTitle: { fontSize: 28, fontWeight: '900', color: '#1e293b' },
    loginSubtitle: { fontSize: 16, color: '#64748b', marginTop: 8 },
    loginForm: { width: '100%' },
    inputWrapper: { marginBottom: 20 },
    inputLabel: { fontSize: 14, fontWeight: '600', color: '#475569', marginBottom: 8 },
    input: { backgroundColor: '#f1f5f9', padding: 16, borderRadius: 12, fontSize: 16, borderWidth: 1, borderColor: '#e2e8f0' },
    loginButton: { backgroundColor: '#4f46e5', padding: 18, borderRadius: 12, alignItems: 'center', marginTop: 10, shadowColor: "#4f46e5", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 10, elevation: 5 },
    loginButtonText: { color: 'white', fontSize: 16, fontWeight: 'bold' },

    // Dashboard Wrapper
    dashboardWrapper: { flex: 1, backgroundColor: '#eef2ff' },
    scrollView: { flex: 1 },
    mainContent: { paddingHorizontal: 20, marginTop: -40 },

    // Web Style Header
    webHeader: { backgroundColor: '#7c3aed', paddingBottom: 60, paddingTop: 60, paddingHorizontal: 20 },
    headerFlex: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    webHeaderTitle: { color: 'white', fontSize: 22, fontWeight: 'bold' },
    webHeaderSubtitle: { color: '#ddd6fe', fontSize: 14, marginTop: 4 },
    webLogoutIcon: { fontSize: 24, color: 'white' },

    // Profile Card (Purple)
    webProfileCard: { backgroundColor: '#7c3aed', borderRadius: 24, padding: 24, flexDirection: 'row', alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.2, shadowRadius: 20, elevation: 15 },
    avatarCircle: { width: 68, height: 68, borderRadius: 34, backgroundColor: 'rgba(255,255,255,0.2)', justifyContent: 'center', alignItems: 'center', borderWidth: 2, borderColor: 'rgba(255,255,255,0.3)' },
    avatarImg: { width: '100%', height: '100%', borderRadius: 34 },
    avatarPlaceholderIcon: { fontSize: 32, color: 'white' },
    profileTextInfo: { flex: 1, marginLeft: 16 },
    profileNameTxt: { fontSize: 20, fontWeight: 'bold', color: 'white', marginBottom: 4 },
    badgeRow: { flexDirection: 'row', alignItems: 'center', marginTop: 2 },
    badgeIcon: { fontSize: 14, marginRight: 8 },
    badgeLabel: { color: '#ddd6fe', fontSize: 13, fontWeight: '600' },
    badgeValue: { color: 'white', fontSize: 13, marginLeft: 6 },

    // Status Section
    webStatusContainer: { backgroundColor: 'white', borderRadius: 24, padding: 24, marginTop: 20, shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 5 },
    sectionTitleWeb: { fontSize: 18, fontWeight: 'bold', color: '#1f2937' },
    dateLabelWeb: { fontSize: 14, color: '#6b7280', marginTop: 4, marginBottom: 16 },
    statusInnerBox: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 20, borderRadius: 20, borderWidth: 2 },
    statusMainLabel: { fontSize: 20, fontWeight: '900' },
    statusJam: { fontSize: 14, color: '#4b5563', marginTop: 4 },
    checkCircle: { width: 44, height: 44, borderRadius: 22, justifyContent: 'center', alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 },
    checkIcon: { fontSize: 20, color: 'white', fontWeight: 'bold' },

    // Menu Grid
    webGrid: { marginTop: 20, paddingBottom: 100 },
    webRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 16 },
    webMenuCard: { backgroundColor: 'white', width: (width - 56) / 2, padding: 24, borderRadius: 24, alignItems: 'center', shadowColor: "#000", shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2 },
    iconCircleWeb: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center', marginBottom: 16 },
    iconEmojiWeb: { fontSize: 28 },
    menuTitleWeb: { fontSize: 16, fontWeight: 'bold', color: '#111827' },
    menuSubWeb: { fontSize: 12, color: '#6b7280', marginTop: 4, textAlign: 'center' },

    // Bottom Nav
    webBottomNav: { position: 'absolute', bottom: 0, width: '100%', height: 90, backgroundColor: 'white', flexDirection: 'row', justifyContent: 'space-between', paddingHorizontal: 40, alignItems: 'center', borderTopWidth: 1, borderTopColor: '#f3f4f6' },
    navBtn: { alignItems: 'center' },
    navBtnCenter: { top: -25, alignItems: 'center' },
    qrCircleFab: { width: 68, height: 68, borderRadius: 34, backgroundColor: '#7c3aed', justifyContent: 'center', alignItems: 'center', shadowColor: "#7c3aed", shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.3, shadowRadius: 15, elevation: 10 },
    qrFabEmoji: { fontSize: 32 },
    navIconBlue: { fontSize: 24 },
    navLabelBlue: { fontSize: 11, fontWeight: 'bold', color: '#3b82f6', marginTop: 4 },
    navIconGray: { fontSize: 24, color: '#9ca3af' },
    navLabelGray: { fontSize: 11, color: '#9ca3af', marginTop: 4 },
    navLabelCenter: { fontSize: 11, fontWeight: 'bold', color: '#4b5563', marginTop: 8 },

    // Modal
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.7)', justifyContent: 'center', padding: 24 },
    modalContent: { backgroundColor: 'white', borderRadius: 32, padding: 32, alignItems: 'center' },
    modalHeaderFlex: { flexDirection: 'row', justifyContent: 'space-between', width: '100%', alignItems: 'center', marginBottom: 12 },
    modalTitleWeb: { fontSize: 24, fontWeight: 'bold', color: '#111827' },
    closeModalTxt: { fontSize: 24, color: '#9ca3af' },
    modalSubWeb: { fontSize: 14, color: '#6b7280', marginBottom: 30 },
    qrInnerBox: { backgroundColor: '#f5f3ff', padding: 24, borderRadius: 24, marginBottom: 24 },
    qrValueBox: { backgroundColor: '#f9fafb', padding: 16, borderRadius: 12, width: '100%', marginBottom: 24 },
    qrValueTxt: { textAlign: 'center', fontFamily: 'monospace', color: '#4b5563' },
    btnDownloadWeb: { backgroundColor: '#7c3aed', width: '100%', padding: 18, borderRadius: 16, alignItems: 'center' },
    btnDownloadTxt: { color: 'white', fontWeight: 'bold', fontSize: 16 },

    // Scanner
    scannerContainer: { flex: 1, backgroundColor: 'black' },
    overlayScanner: { position: 'absolute', bottom: 50, width: '100%', alignItems: 'center' },
    scanText: { color: 'white', fontSize: 16, marginBottom: 20, backgroundColor: 'rgba(0,0,0,0.6)', padding: 12, borderRadius: 12 },
    closeBtnScanner: { backgroundColor: 'white', paddingVertical: 14, paddingHorizontal: 40, borderRadius: 30 },
    closeTxtScanner: { fontWeight: 'bold' }
});
