<?php

// Konfigurasi Koneksi Database
define('DB_HOST', '127.0.0.1:3308');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crud_mahasiswa');

// Buat koneksi menggunakan MySQLi OOP Style
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("<div style='color:red; padding:20px; font-family:sans-serif;'>
            <strong>Koneksi Database Gagal!</strong><br>
            Error: " . $conn->connect_error . "
         </div>");
}

// Set charset untuk mencegah encoding issue
$conn->set_charset("utf8mb4");
?>
