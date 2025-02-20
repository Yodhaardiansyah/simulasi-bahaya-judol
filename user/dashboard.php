<?php
session_start();
require '../config/database.php';

// Ensure only the user has access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// Get user data from the database
$stmt = $pdo->prepare("SELECT username, full_name, balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1d2b64, #f8cdda);
            color: #fff;
            box-sizing: border-box;
            transition: background 0.5s ease-in-out;
        }

        header {
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease-in-out;
        }

        header h1 {
            font-size: 1.6rem;
            margin: 0;
            text-transform: uppercase;
        }

        .logout-menu {
            position: relative;
        }

        .logout-menu button {
            background: none;
            border: none;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 12px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .logout-menu button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .logout-menu ul {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            list-style: none;
            margin: 0;
            padding: 10px 0;
        }

        .logout-menu ul li {
            padding: 12px 20px;
        }

        .logout-menu ul li a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        .logout-menu:hover ul {
            display: block;
        }

        main {
            padding: 30px;
            max-width: 900px;
            margin: 40px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 1s ease-out;
        }

        section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            margin-bottom: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            transition: transform 0.3s ease-in-out;
        }

        section:hover {
            transform: scale(1.05);
        }

        h2 {
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: 700;
            letter-spacing: 1px;
            color: #f0f0f0;
        }

        .wallet {
            background: #00b09b;
            color: #fff;
            border-radius: 12px;
            padding: 25px;
            font-size: 2rem;
            width: 80%;
            margin: 0 auto;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease-in-out;
        }

        .wallet span {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        .wallet:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .button-container {
            display: flex;
            justify-content: space-evenly;
            gap: 30px;
            margin-top: 30px;
        }

        .button-container a {
            text-decoration: none;
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
            background: #4CAF50;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .button-container a:hover {
            background: #388E3C;
            transform: scale(1.05);
        }

        .button-container a i {
            margin-right: 12px;
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            width: 100%;
            margin-top: auto;
            position: fixed;
            bottom: 0;
        }

        /* Fade-in Animation */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?= htmlspecialchars($user['full_name']) ?></h1>
        <div class="logout-menu">
            <button>MENU</button>
            <ul>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </header>
    
    <main>
        <section>
            <h2>Account Information</h2>
            <div class="wallet">
                <p><strong>Your Balance:</strong></p>
                <span>Rp<?= number_format($user['balance'], 2, ',', '.') ?></span>
            </div>
        </section>

        <section>
            <h2>Features</h2>
            <div class="button-container">
                <a href="slot.php"><i class="fas fa-gamepad"></i> Play Slot</a>
                <a href="card.php"><i class="fas fa-credit-card"></i> Play Card Game</a>
                <a href="history.php"><i class="fas fa-history"></i> Bet History</a>
                <a href="top_up.php"><i class="fas fa-arrow-up"></i> Top Up</a> <!-- New Top Up Button -->
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Educational Betting App</p>
    </footer>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
