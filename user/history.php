<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// Number of records per page
$records_per_page = 10;

// Get the current page from the query string (default to 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL query
$offset = ($page - 1) * $records_per_page;

// Mengambil riwayat taruhan user dengan pagination
$stmt = $pdo->prepare("SELECT bet_amount, result, timestamp FROM bet_history WHERE user_id = ? ORDER BY timestamp DESC LIMIT ? OFFSET ?");
$stmt->bindParam(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);  // Bind LIMIT value
$stmt->bindParam(3, $offset, PDO::PARAM_INT);  // Bind OFFSET value
$stmt->execute();
$history = $stmt->fetchAll();

// Calculate the total number of records to determine the number of pages
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bet_history WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_records = $stmt->fetchColumn();

// Calculate the total number of pages
$total_pages = ceil($total_records / $records_per_page);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Taruhan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            padding: 20px;
            text-align: center;
        }

        h1 {
            color: #4CAF50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        tr:hover td {
            background-color: #f1f1f1;
        }

        .message {
            font-size: 1.1rem;
            color: #666;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            text-decoration: none;
            color: #4CAF50;
            margin: 0 5px;
            padding: 8px 16px;
            border: 1px solid #4CAF50;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
        }

        a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
            margin-top: 20px;
            display: inline-block;
        }

        a:hover {
            color: #388E3C;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Riwayat Taruhan</h1>

        <?php if (count($history) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Jumlah Taruhan</th>
                        <th>Hasil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $offset + 1; // Start numbering from the correct number based on offset
                    foreach ($history as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>  <!-- Display the row number -->
                            <td><?= date("d-m-Y H:i", strtotime($row['timestamp'])) ?></td>
                            <td><?= number_format($row['bet_amount'], 2) ?></td>
                            <td><?= ucfirst($row['result']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="message">Belum ada riwayat taruhan.</p>
        <?php endif; ?>

        <!-- Pagination Controls -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1">First</a>
                <a href="?page=<?= $page - 1 ?>">Previous</a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>">Next</a>
                <a href="?page=<?= $total_pages ?>">Last</a>
            <?php endif; ?>
        </div>

        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>
