<?php
require '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ensure full_name is set from the database
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE username = :username");
$stmt->bindParam(':username', $_SESSION['username']);
$stmt->execute();
$user_name = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT username, full_name, balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();


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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Taruhan User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding-top: 80px;
        }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #343a40;
            color: white;
            padding: 15px;
            z-index: 1000;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 10px 15px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: #495057;
            border-radius: 5px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .header-user {
            position: fixed;
            top: 0;
            right: 0;
            padding: 15px;
            background-color: white;
            width: calc(100% - 250px);
            z-index: 1001;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h4 class="text-white mb-4 text-center">SITUSAJA</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="fa fa-tachometer"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="user_history.php"><i class="fa fa-users"></i> Riwayat User</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fa fa-user"></i> Kelola User</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_transactions.php"><i class="fa fa-credit-card"></i> Konfirmasi Top-Up</a></li>
        </ul>
    </div>

    <div class="header-user">
        <div class="dropdown ms-auto">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <?= htmlspecialchars($user['full_name']) ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="content">
        <h1>Riwayat Taruhan User</h1>
        <form method="get">
            <label for="user_id">Pilih User:</label>
            <select name="user_id" onchange="this.form.submit()">
                <option value="">-- Pilih User --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $user['id'] == $user_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($user_id && count($history) > 0): ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Jumlah Taruhan</th>
                        <th>Hasil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= $row['timestamp'] ?></td>
                            <td><?= number_format($row['bet_amount'], 2) ?></td>
                            <td><?= $row['result'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <a href="?user_id=<?= $user_id ?>&page=1" class="<?= $page == 1 ? 'disabled' : '' ?>">First</a>
                <a href="?user_id=<?= $user_id ?>&page=<?= $page - 1 ?>" class="<?= $page == 1 ? 'disabled' : '' ?>">Previous</a>
                <a href="?user_id=<?= $user_id ?>&page=<?= $page + 1 ?>" class="<?= $page == $total_pages ? 'disabled' : '' ?>">Next</a>
                <a href="?user_id=<?= $user_id ?>&page=<?= $total_pages ?>" class="<?= $page == $total_pages ? 'disabled' : '' ?>">Last</a>
            </div>
        <?php elseif ($user_id): ?>
            <p>Belum ada riwayat taruhan untuk user ini.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-primary mt-4">Kembali ke Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
