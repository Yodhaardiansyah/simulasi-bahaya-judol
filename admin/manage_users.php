<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";

// Update saldo user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_balance'])) {
    $user_id = $_POST['user_id'];
    $new_balance = $_POST['new_balance'];

    $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->execute([$new_balance, $user_id]);

    $message = "Saldo berhasil diperbarui.";
}

// Mengambil semua user
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'user'");
$users = $stmt->fetchAll();

// Inisialisasi variabel pengaturan
$max_win_slots = 0;
$max_bet = 0;
$next_win = 0;
$user_id = 0;
$username = "";

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $username = $user['username'];

    $stmt = $pdo->prepare("SELECT max_win_slots, max_bet, next_win FROM settings WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();

    if ($settings) {
        $max_win_slots = $settings['max_win_slots'];
        $max_bet = $settings['max_bet'];
        $next_win = $settings['next_win'];
    }
}

// Simpan pengaturan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $user_id = $_POST['user_id'];
    $max_win_slots = $_POST['max_win_slots'];
    $max_bet = $_POST['max_bet'];
    $next_win = isset($_POST['next_win']) ? 1 : 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $stmt = $pdo->prepare("UPDATE settings SET max_win_slots = ?, max_bet = ?, next_win = ? WHERE id_user = ?");
        $stmt->execute([$max_win_slots, $max_bet, $next_win, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (id_user, max_win_slots, max_bet, next_win) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $max_win_slots, $max_bet, $next_win]);
    }

    $message = "Pengaturan berhasil disimpan!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User dan Pengaturan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        h1 {
            color: #4CAF50;
            text-align: center;
            width: 100%;
        }

        table {
            width: 60%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        .message {
            text-align: center;
            color: green;
            margin-bottom: 20px;
            width: 100%;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            align-items: center;
        }

        label {
            font-weight: bold;
            text-align: center;
        }

        input[type="number"], input[type="checkbox"], select {
            padding: 8px;
            width: 100%;
            max-width: 250px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 150px;
            align-self: center;
        }

        button:hover {
            background-color: #388E3C;
        }

        a {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            text-decoration: none;
        }

        a:hover {
            background-color: #388E3C;
        }
        
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #1976D2;
        }
    </style>
</head>
<body>
    <!-- Back to Dashboard Button -->
    <a href="dashboard.php" class="back-btn">Kembali ke Dashboard</a>

    <div class="container">
        <h1>Kelola User dan Pengaturan</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Saldo</th>
                    <th>Kemenangan</th>
                    <th>Kekalahan</th>
                    <th>Update Saldo</th>
                    <th>Pengaturan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= number_format($user['balance'], 2) ?></td>
                    <td><?= $user['win_count'] ?></td>
                    <td><?= $user['loss_count'] ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="number" name="new_balance" step="0.01" value="<?= $user['balance'] ?>" required>
                            <button type="submit" name="update_balance">Update</button>
                        </form>
                    </td>
                    <td>
                        <a href="?user_id=<?= $user['id'] ?>">Atur</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($user_id): ?>
        <div>
            <h2>Pengaturan untuk <?= htmlspecialchars($username) ?></h2>
            <form method="post">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                <label for="max_win_slots">Max Win Slots:</label>
                <input type="number" name="max_win_slots" value="<?= htmlspecialchars($max_win_slots) ?>" required>
                <label for="max_bet">Max Bet:</label>
                <input type="number" name="max_bet" value="<?= htmlspecialchars($max_bet) ?>" required>
                <label for="next_win">Selanjutnya Pasti Menang:</label>
                <input type="checkbox" name="next_win" <?= $next_win ? 'checked' : '' ?>>
                <button type="submit" name="update_settings">Simpan</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
