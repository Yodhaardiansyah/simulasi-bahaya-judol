<?php
session_start();
require '../config/database.php';

// Check if the user is an admin
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

// Default to 'Admin' if full_name is not available
$user_name = $user_name ?: 'Admin';

// Fetch total balance and statistics for users
$stmt = $pdo->query("SELECT SUM(balance) as total_balance FROM users WHERE role = 'user'");
$total_balance = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(win_count) as total_wins, SUM(loss_count) as total_losses FROM users WHERE role = 'user'");
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        <h2>Admin Dashboard</h2>

        <!-- Stats Section -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>Total Saldo</h3>
                        <p>Rp <?= number_format($total_balance, 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>Total User</h3>
                        <p><?= $total_users ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>Total Kemenangan</h3>
                        <p><?= $stats['total_wins'] ?> | Total Kekalahan: <?= $stats['total_losses'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 