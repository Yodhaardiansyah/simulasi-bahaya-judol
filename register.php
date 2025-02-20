<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input to avoid SQL injection
    $full_name = htmlspecialchars($_POST['full_name']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);
    $role = 'user';

    // Check if the username already exists in the database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $userCount = $stmt->fetchColumn();

    if ($userCount > 0) {
        $error = "Username already exists";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Hash the password
        $password = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Insert the user into the database
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $username, $email, $password, $role]);
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $error = "There was an error registering the user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 10px;
            font-weight: bold;
        }

        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #388E3C;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 10px;
        }

        .links {
            text-align: center;
            margin-top: 10px;
        }

        .links a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
            margin: 0 5px;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #388E3C;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label for="full_name">Nama Lengkap:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Konfirmasi Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <div class="links">
            <a href="index.php">Kembali ke Beranda</a>
            <a href="login.php">Sudah Punya Akun? Login</a>
        </div>
    </div>
</body>
</html>
