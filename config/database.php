<?php
// Database configuration
$host = 'localhost';      // Ganti dengan host database Anda
$dbname = 'bahaya_judi'; // Ganti dengan nama database Anda
$username = 'root';        // Ganti dengan username database Anda
$password = '';            // Ganti dengan password database Anda

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode menjadi Exception untuk menangani kesalahan dengan lebih mudah
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan kesalahan
    echo "Koneksi gagal: " . $e->getMessage();
    exit();
}
?>
