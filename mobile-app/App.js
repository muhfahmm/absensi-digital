import React, { useState, useEffect } from 'react';
import { StyleSheet, Text, View, TextInput, TouchableOpacity, Alert, ActivityIndicator, SafeAreaView, Modal } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { CameraView, Camera } from "expo-camera";
import QRCode from 'react-native-qrcode-svg';

// GANTI IP INI SESUAI IP KOMPUTER ANDA!
// Cara cek IP: Buka CMD -> ketik ipconfig -> lihat IPv4 Address
const BASE_URL = 'http://192.168.0.103/absensi-digital%203';

export default function App() {
    const [currentView, setCurrentView] = useState('login'); // login, dashboard, scanner
    const [userData, setUserData] = useState(null);

    // Login State
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);

    // Scanner State
    const [hasPermission, setHasPermission] = useState(null);
    const [scanned, setScanned] = useState(false);

    useEffect(() => {
        const getCameraPermissions = async () => {
            const { status } = await Camera.requestCameraPermissionsAsync();
            setHasPermission(status === "granted");
        };
        getCameraPermissions();
    }, []);

    const handleLogin = async () => {
        if (!username || !password) {
            Alert.alert('Error', 'Mohon isi username dan password');
            return;
        }

        setLoading(true);
        try {
            const response = await fetch(`${BASE_URL}/app/api/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                }),
            });

            const data = await response.json();

            if (data.success) {
                setUserData(data);
                setCurrentView('dashboard');
                // Clear password
                setPassword('');
            } else {
                Alert.alert('Login Gagal', data.message || 'Periksa kembali data anda');
            }
        } catch (error) {
            Alert.alert('Error', 'Gagal menghubungkan ke server. Pastikan IP Address benar dan HP terhubung ke WiFi yang sama.\n\nError: ' + error.message);
        } finally {
            setLoading(false);
        }
    };

    const handleLogout = () => {
        setUserData(null);
        setCurrentView('login');
        setUsername('');
    };

    const handleBarCodeScanned = async ({ type, data }) => {
        if (scanned) return;
        setScanned(true);

        try {
            const response = await fetch(`${BASE_URL}/app/api/scan.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    qr_code: data,
                    scanner_role: userData?.role
                }),
            });

            const result = await response.json();

            if (result.success) {
                Alert.alert(
                    "✅ Berhasil!",
                    `${result.data.nama}\nStatus: ${result.data.status}\nJam: ${result.data.jam_masuk}`,
                    [{ text: "OK", onPress: () => setScanned(false) }]
                );
            } else {
                Alert.alert(
                    "❌ Gagal",
                    result.message || "QR Code tidak valid",
                    [{ text: "Scan Lagi", onPress: () => setScanned(false) }]
                );
            }
        } catch (error) {
            Alert.alert("Error", "Gagal memproses data", [{ text: "OK", onPress: () => setScanned(false) }]);
        }
    };

    // VIEWS
    const renderLogin = () => (
        <View style={styles.contentContainer}>
            <Text style={styles.title}>Absensi Digital</Text>
            <Text style={styles.subtitle}>Mobile App</Text>

            <View style={styles.inputContainer}>
                <TextInput
                    style={styles.input}
                    placeholder="Username / NIS / NIP"
                    value={username}
                    onChangeText={setUsername}
                    autoCapitalize="none"
                    placeholderTextColor="#9ca3af"
                />
                <TextInput
                    style={styles.input}
                    placeholder="Password"
                    value={password}
                    onChangeText={setPassword}
                    secureTextEntry
                    placeholderTextColor="#9ca3af"
                />

                <TouchableOpacity
                    style={styles.button}
                    onPress={handleLogin}
                    disabled={loading}
                >
                    {loading ? (
                        <ActivityIndicator color="white" />
                    ) : (
                        <Text style={styles.buttonText}>LOGIN</Text>
                    )}
                </TouchableOpacity>

                <Text style={styles.hint}>
                    *Gunakan akun yang sama dengan website
                </Text>
            </View>
        </View>
    );

    const renderDashboard = () => (
        <View style={styles.contentContainer}>
            <View style={styles.header}>
                <Text style={styles.welcomeText}>Halo, {userData?.user?.nama}</Text>
                <Text style={styles.roleText}>{userData?.role?.toUpperCase()}</Text>
            </View>

            <View style={styles.card}>
                {userData?.role === 'siswa' && (
                    <View style={styles.qrContainer}>
                        <Text style={styles.sectionTitle}>QR Code Saya</Text>
                        {userData?.user?.kode_qr ? (
                            <View style={styles.qrBorder}>
                                <QRCode
                                    value={userData?.user?.kode_qr}
                                    size={200}
                                />
                            </View>
                        ) : (
                            <Text>QR Code belum digenerate</Text>
                        )}
                        <Text style={styles.qrText}>{userData?.user?.kode_qr}</Text>
                    </View>
                )}

                {(userData?.role === 'admin' || userData?.role === 'guru') && (
                    <View style={styles.menuContainer}>
                        <Text style={styles.sectionTitle}>Menu Admin/Guru</Text>
                        <TouchableOpacity
                            style={[styles.button, styles.scanButton]}
                            onPress={() => setCurrentView('scanner')}
                        >
                            <Text style={styles.buttonText}>SCAN QR SISWA</Text>
                        </TouchableOpacity>
                        <Text style={styles.hint}>Gunakan untuk scan kehadiran siswa</Text>
                    </View>
                )}
            </View>

            <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
                <Text style={styles.logoutText}>Keluar</Text>
            </TouchableOpacity>
        </View>
    );

    const renderScanner = () => {
        if (hasPermission === null) {
            return <Text>Requesting for camera permission</Text>;
        }
        if (hasPermission === false) {
            return <Text>No access to camera</Text>;
        }

        return (
            <View style={styles.scannerContainer}>
                <CameraView
                    style={StyleSheet.absoluteFillObject}
                    onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
                    barcodeScannerSettings={{
                        barcodeTypes: ["qr"],
                    }}
                />
                <View style={styles.overlay}>
                    <Text style={styles.scanText}>Arahkan kamera ke QR Code Siswa</Text>
                    <TouchableOpacity
                        style={styles.closeButton}
                        onPress={() => setCurrentView('dashboard')}
                    >
                        <Text style={styles.closeText}>Batal</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    };

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar style="auto" />
            {currentView === 'login' && renderLogin()}
            {currentView === 'dashboard' && renderDashboard()}
            {currentView === 'scanner' && renderScanner()}
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#f3f4f6',
    },
    contentContainer: {
        flex: 1,
        padding: 24,
        justifyContent: 'center',
        alignItems: 'center',
    },
    scannerContainer: {
        flex: 1,
        backgroundColor: 'black',
    },
    title: {
        fontSize: 28,
        fontWeight: 'bold',
        color: '#1f2937',
        marginBottom: 8,
    },
    subtitle: {
        fontSize: 18,
        color: '#4b5563',
        marginBottom: 48,
    },
    inputContainer: {
        width: '100%',
        backgroundColor: 'white',
        padding: 20,
        borderRadius: 16,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 8,
        elevation: 3,
    },
    input: {
        backgroundColor: '#f9fafb',
        padding: 16,
        borderRadius: 12,
        marginBottom: 16,
        borderWidth: 1,
        borderColor: '#e5e7eb',
        fontSize: 16,
    },
    button: {
        backgroundColor: '#2563eb',
        padding: 16,
        borderRadius: 12,
        alignItems: 'center',
        marginBottom: 16,
    },
    scanButton: {
        backgroundColor: '#10b981',
        marginTop: 20,
        width: '100%',
    },
    buttonText: {
        color: 'white',
        fontWeight: 'bold',
        fontSize: 16,
    },
    hint: {
        textAlign: 'center',
        fontSize: 12,
        color: '#9ca3af',
    },
    // Dashboard Styles
    header: {
        marginBottom: 30,
        alignItems: 'center',
    },
    welcomeText: {
        fontSize: 24,
        fontWeight: 'bold',
        color: '#111827',
    },
    roleText: {
        fontSize: 14,
        color: '#6b7280',
        marginTop: 4,
        letterSpacing: 1,
    },
    card: {
        backgroundColor: 'white',
        width: '100%',
        padding: 24,
        borderRadius: 20,
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 12,
        elevation: 5,
    },
    qrContainer: {
        alignItems: 'center',
    },
    qrBorder: {
        padding: 10,
        borderWidth: 2,
        borderColor: '#e5e7eb',
        borderRadius: 10,
        marginBottom: 10,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        marginBottom: 16,
        color: '#374151',
    },
    qrText: {
        fontSize: 12,
        color: '#6b7280',
        marginTop: 8,
        fontFamily: 'monospace',
    },
    menuContainer: {
        width: '100%',
        alignItems: 'center',
    },
    logoutButton: {
        marginTop: 30,
        padding: 10,
    },
    logoutText: {
        color: '#ef4444',
        fontWeight: '600',
    },
    // Scanner Styles
    overlay: {
        position: 'absolute',
        bottom: 50,
        left: 20,
        right: 20,
        alignItems: 'center',
    },
    scanText: {
        color: 'white',
        fontSize: 16,
        marginBottom: 20,
        backgroundColor: 'rgba(0,0,0,0.5)',
        padding: 10,
        borderRadius: 8,
        overflow: 'hidden',
    },
    closeButton: {
        backgroundColor: 'white',
        paddingVertical: 12,
        paddingHorizontal: 30,
        borderRadius: 25,
    },
    closeText: {
        color: 'black',
        fontWeight: 'bold',
    }
});
