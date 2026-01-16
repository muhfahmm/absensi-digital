<?php
// app/functions/base_url.php

/**
 * Mendapatkan URL dasar aplikasi
 * 
 * @param string $path Path tambahan setelah base url (opsional)
 * @return string URL lengkap
 */
function base_url($path = '') {
    // Deteksi protokol (http/https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    
    // Deteksi host (localhost atau domain)
    $host = $_SERVER['HTTP_HOST'];
    
    // Nama folder project (asumsi di localhost/absensi-digital)
    // Sesuaikan jika folder project berbeda
    $projectDir = '/absensi-digital 3'; // Ubah sesuai nama folder di htdocs
    
    // Bersihkan slash di awal path
    $path = ltrim($path, '/');
    
    return $protocol . "://" . $host . $projectDir . '/' . $path;
}

/**
 * Redirect ke halaman tertentu
 */
function redirect($url) {
    header("Location: " . base_url($url));
    exit;
}
