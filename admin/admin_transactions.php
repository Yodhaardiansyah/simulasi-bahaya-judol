<?php
session_start();
require '../config/database.php';

// Ensure only the admin has access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle status change (confirm/reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $status = $_POST['status'];

    // Update the transaction status
    $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    $stmt->execute([$status, $transaction_id]);

    // If confirmed, update user's balance
    if ($status === 'Confirmed') {
        // Fetch transaction details to get user_id and the top-up amount
        $stmt = $pdo->prepare("SELECT user_id, amount FROM transactions WHERE id = ?");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch();

        if ($transaction) {
            // Update the user's balance by adding the top-up amount
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$transaction['amount'], $transaction['user_id']]);
            echo "Transaction confirmed. User's balance updated.";
        } else {
            echo "Transaction not found.";
        }
    }

    if ($status === 'Rejected') {
        echo "Transaction rejected.";
    }
}

// Get all users for the user selection dropdown
$stmt = $pdo->prepare("SELECT id, full_name FROM users");
$stmt->execute();
$users = $stmt->fetchAll();

// Handle transaction history filtering
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// Pagination setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get pending transactions for the selected user with pagination
$sql = "SELECT * FROM transactions WHERE user_id = :user_id AND status = 'Pending' ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pending_transactions = $stmt->fetchAll();

// Get the total number of pending transactions for pagination
$sql = "SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = 'Pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$total_pending_transactions = $stmt->fetchColumn();
$total_pending_pages = ceil($total_pending_transactions / $limit);

// Get all transactions for the selected user with pagination (all statuses)
$sql = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$all_transactions = $stmt->fetchAll();

// Get the total number of all transactions for pagination
$sql = "SELECT COUNT(*) FROM transactions WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$total_transactions = $stmt->fetchColumn();
$total_pages = ceil($total_transactions / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Top Up Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white p-3 mb-4">
        <h1 class="text-center">Manage Top Up Requests</h1>
    </header>

    <main class="container">
        <!-- Button to go back to dashboard -->
        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-info">Kembali ke Dashboard</a>
        </div>

        <section class="mb-4">
            <h2>Filter by User</h2>
            <form method="GET" action="" class="d-flex mb-3">
                <select name="user_id" class="form-select me-2">
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= $user_id == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </section>

        <section class="mb-5">
            <h2>Pending Transactions</h2>
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_transactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['user_id']) ?></td>
                                    <td>Rp<?= number_format($transaction['amount'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($transaction['status']) ?></td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                                            <button type="submit" name="status" value="Confirmed" class="btn btn-success btn-sm">Confirm</button>
                                            <button type="submit" name="status" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination for Pending Transactions -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?user_id=<?= $user_id ?>&page=<?= $page - 1 ?>" class="btn btn-secondary btn-sm">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $total_pending_pages): ?>
                            <a href="?user_id=<?= $user_id ?>&page=<?= $page + 1 ?>" class="btn btn-secondary btn-sm">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h2>Transaction History</h2>
            <?php if ($user_id): ?>
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($all_transactions): ?>
                                    <?php foreach ($all_transactions as $transaction): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($transaction['id']) ?></td>
                                            <td>Rp<?= number_format($transaction['amount'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($transaction['status']) ?></td>
                                            <td><?= htmlspecialchars($transaction['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No transactions found for this user.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination for Transaction History -->
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?user_id=<?= $user_id ?>&page=<?= $page - 1 ?>" class="btn btn-secondary btn-sm">Previous</a>
                            <?php endif; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="?user_id=<?= $user_id ?>&page=<?= $page + 1 ?>" class="btn btn-secondary btn-sm">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p>Please select a user to view their transaction history.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-dark text-white text-center p-3">
        <p>&copy; <?= date('Y') ?> Educational Betting App</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
