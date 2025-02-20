<?php
session_start();
require '../config/database.php';

// Ensure only the user has access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// Get user's current balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$current_balance = $user['balance'];

// Handle top-up request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    if ($amount > 0) {
        // Insert transaction into the database
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, status) VALUES (?, ?, 'Pending')");
        $stmt->execute([$_SESSION['user_id'], $amount]);
        echo "<div class='alert alert-success fade show' role='alert'>Top-up request submitted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger fade show' role='alert'>Please enter a valid amount.</div>";
    }
}

// Get user's transaction history
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header, .footer {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px 0;
            border-radius: 8px;
        }

        /* Balance Card */
        .balance-card {
            background: #ffffff;
            color: #333;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .balance-card h2 {
            font-weight: 600;
            font-size: 28px;
        }
        .balance-card p {
            font-size: 24px;
            font-weight: 500;
            color: #28a745;
        }

        /* Top-up Section */
        .top-up-card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .top-up-card h2 {
            font-weight: 600;
            color: #333;
        }

        .form-control {
            border-radius: 8px;
        }
        .btn {
            border-radius: 25px;
            padding: 10px 30px;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #2575fc;
            border: none;
        }
        .btn-primary:hover {
            background-color: #6a11cb;
        }

        /* Transaction Table */
        .transaction-table {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .transaction-table th, .transaction-table td {
            text-align: center;
        }
        .transaction-table tbody tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }
        .status-confirm {
            background-color: #28a745;
            color: white;
        }
        .status-reject {
            background-color: #dc3545;
            color: white;
        }
        .status-progress {
            background-color: #ffc107;
            color: black;
        }

        /* Footer */
        .footer p {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <header class="header text-center">
        <h1 class="text-white">Top Up</h1>
        <a href="dashboard.php" class="btn btn-light btn-sm">Back to Dashboard</a>
    </header>

    <main class="container my-5">
        <!-- Display user's current balance -->
        <section class="balance-card">
            <h2>Your Current Balance</h2>
            <p>Rp <?= number_format($current_balance, 2, ',', '.') ?></p>
        </section>

        <!-- Section for top-up request -->
        <section class="top-up-card">
            <h2>Request a Top Up</h2>
            <form method="POST" action="" class="mb-3">
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (Rp)</label>
                    <input type="number" id="amount" name="amount" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </section>

        <!-- Section for transaction history -->
        <section class="transaction-table">
            <h2>Your Transaction History</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="status-<?= strtolower($transaction['status']) ?>">
                            <td>Rp <?= number_format($transaction['amount'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($transaction['status']) ?></td>
                            <td><?= $transaction['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="3" class="text-center">No transaction history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="footer text-white text-center">
        <p>&copy; <?= date('Y') ?> Educational Betting App</p>
        <a href="dashboard.php" class="btn btn-light btn-sm">Back to Dashboard</a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
