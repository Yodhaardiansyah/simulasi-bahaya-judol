<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahaya Judi Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 2rem;
        }

        main {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80vh;
            text-align: center;
            padding: 20px;
        }

        main h1 {
            font-size: 2.5rem;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        main p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 30px;
        }

        .button-container {
            display: flex;
            gap: 20px;
        }

        .button-container a {
            text-decoration: none;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            color: white;
            background-color: #4CAF50;
            transition: background-color 0.3s;
        }

        .button-container a:hover {
            background-color: #388E3C;
        }

        footer {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <h1>Selamat Datang di Bahaya Judi Online</h1>
    </header>

    <main>
        <h1>Platform Edukasi</h1>
        <p>Platform ini dibuat untuk memberikan edukasi kepada masyarakat tentang bahaya judi online. Jadilah bagian dari perubahan!</p>
        <div class="button-container">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Bahaya Judi Online. Semua Hak Dilindungi.</p>
    </footer>
</body>
</html>
