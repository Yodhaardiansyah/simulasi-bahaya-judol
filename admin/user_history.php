<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Menampilkan daftar user
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'user'");
$users = $stmt->fetchAll();

$user_id = $_GET['user_id'] ?? null;
$page = $_GET['page'] ?? 1; // Halaman saat ini
$per_page = 10; // Jumlah riwayat per halaman
$history = [];

// Mengambil riwayat taruhan user tertentu jika dipilih
if ($user_id) {
    // Hitung jumlah total riwayat untuk user tersebut
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bet_history WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $total_history = $stmt->fetchColumn();

    // Hitung offset untuk pagination
    $offset = ($page - 1) * $per_page;

    // Ambil riwayat taruhan untuk user tertentu dengan limit dan offset
    $stmt = $pdo->prepare("SELECT bet_amount, result, timestamp FROM bet_history WHERE user_id = :user_id ORDER BY timestamp DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $history = $stmt->fetchAll();
}

$total_pages = ceil($total_history / $per_page); // Total halaman

// Prepare the content for the template
$title = "Riwayat Taruhan User";
$content = 'history_content.php'; // The specific content file
?>

<?php include 'template.php'; ?>
