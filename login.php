<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = $_POST['login_input']; // Either username or email
    $password = $_POST['password'];

    // Check if the input is a valid email or username
    if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    }

    $stmt->execute([$login_input]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid username, email, or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        .error {
            color: red;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label for="login_input">Username or Email:</label>
            <input type="text" id="login_input" name="login_input" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <div class="links">
            <a href="index.php">Kembali ke Beranda</a>
            <a href="register.php">Belum Punya Akun? Daftar</a>
        </div>
    </div>
</body>
</html>
