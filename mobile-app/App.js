import { StatusBar } from 'expo-status-bar';
import { StyleSheet, View } from 'react-native';
import { WebView } from 'react-native-webview';

export default function App() {
    return (
        <View style={styles.container}>
            <WebView
                source={{ uri: 'http://192.168.0.103/absensi%20digital/' }}
                style={{ flex: 1 }}
            />
            <StatusBar style="auto" />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#fff',
        marginTop: 30, // Basic safe area for status bar
    },
});
