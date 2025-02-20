<?php
session_start();
require '../config/database.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

// Fetch current user details
$stmt = $pdo->prepare("SELECT id, full_name, username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? $user['full_name'];
    $username = $_POST['username'] ?? $user['username'];
    $email = $_POST['email'] ?? $user['email'];
    $password = $_POST['password'] ?? null;

    // Hash the password if it's provided
    if ($password) {
        $password = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $password = $user['password']; // Keep the current password if no new password is entered
    }

    // Update user data in the database
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, password = ? WHERE id = ?");
    $stmt->execute([$full_name, $username, $email, $password, $user_id]);

    // Refresh the page after successful update
    header("Location: settings.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 500px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
        }

        label {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .updated-data {
            margin-top: 30px;
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .updated-data p {
            margin: 5px 0;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #4CAF50;
            font-weight: bold;
            text-decoration: none;
        }

        .back-link:hover {
            color: #388E3C;
        }

        .error-message {
            color: red;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Pengaturan Akun</h1>

        <form method="POST" action="">
            <label for="full_name">Nama Lengkap:</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="password">Password Baru:</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password baru jika ingin mengganti">

            <input type="submit" value="Simpan Perubahan">
        </form>

        <!-- Display the updated user data -->
        <div class="updated-data">
            <h3>Data Pengguna Terbaru</h3>
            <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Password:</strong> ***** (for security reasons, we don't show the password)</p>
        </div>

        <a href="dashboard.php" class="back-link">Kembali ke Dashboard</a>
    </div>

</body>
</html>
