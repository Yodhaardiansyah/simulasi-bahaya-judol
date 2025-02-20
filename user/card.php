<?php
session_start();
require '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil saldo user
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$balance = $user ? $user['balance'] : 0;

// Inisialisasi game
$cards = array_fill(0, 9, '?'); // Semua kartu awal tanda tanya
$chosen_number = rand(1, 9); // Angka yang harus ditebak
$selected_card = null;
$is_win = false;
$game_over = false;
$game_started = false; // Untuk menandakan bahwa taruhan telah dikonfirmasi

// Proses taruhan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_bet'])) {
        $_SESSION['bet_amount'] = $_POST['bet'];
        $_SESSION['bet_confirmed'] = true;
        $game_started = true; // Menandakan permainan dimulai setelah taruhan dikonfirmasi
    } elseif (isset($_POST['pick_card']) && isset($_SESSION['bet_confirmed'])) {
        $selected_card = isset($_POST['selected_card']) ? (int)$_POST['selected_card'] : null;
        $bet = $_SESSION['bet_amount'];

        if ($selected_card !== null) {
            $is_win = ($selected_card == $chosen_number);
            $game_over = true; // Game berakhir setelah memilih

            // Simpan hasil ke database
            $stmt = $pdo->prepare("INSERT INTO bet_history (user_id, bet_amount, result, timestamp) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $bet, $is_win ? 'win' : 'lose', date("Y-m-d H:i:s")]);

            // Update saldo
            $balance_change = $is_win ? $bet : -$bet;
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$balance_change, $user_id]);

            // Perbarui saldo di sesi
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $balance = $stmt->fetchColumn();

            // Tampilkan semua kartu setelah dipilih
            for ($i = 0; $i < 9; $i++) {
                $cards[$i] = ($i + 1 == $chosen_number) ? $chosen_number : rand(1, 9);
            }
        }
    } elseif (isset($_POST['restart_game'])) {
        // Mengulang permainan
        unset($_SESSION['bet_confirmed']);
        unset($_SESSION['bet_amount']);
        $game_started = false;
        $game_over = false;
        $cards = array_fill(0, 9, '?');
        $chosen_number = rand(1, 9);
        $selected_card = null;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tebak Kartu</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f6f7f8, #e2e8f0);
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .container {
            width: 350px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 15px;
            padding: 30px;
            box-sizing: border-box;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 26px;
            color: #4caf50;
            margin-bottom: 20px;
        }

        .balance {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(90deg, #4caf50, #81c784);
            padding: 10px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }

        #balance {
            font-size: 32px;
            font-weight: bold;
        }


        .card-grid {
            display: grid;
            grid-template-columns: repeat(3, 90px);
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }

        .card {
            width: 90px;
            height: 90px;
            background-color: #f2f2f2;
            font-size: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            cursor: pointer;
            border: 2px solid #ddd;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .card.correct {
            background-color: #4caf50;
            color: white;
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.2);
        }

        .card.selected {
            background-color: #2196f3;
            color: white;
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
        }

        .hidden {
            background-color: #ccc;
            color: transparent;
        }

        .message {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            color: #555;
        }

        .bet-options {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .bet-button {
            background-color: #2196f3;
            color: white;
            padding: 8px 15px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .bet-button:hover {
            background-color: #1976d2;
        }

        .bet-button:active {
            background-color: #1565c0;
        }

        .bet-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        input[type="number"], button {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 16px;
            margin-top: 10px;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        button {
            background-color: #2196f3;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 18px;
            padding: 12px 0;
            border-radius: 10px;
        }

        button:hover {
            background-color: #1976d2;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tebak Kartu</h2>
        <p class="balance">Saldo Anda: Rp<span id="balance"><?= number_format($balance, 0, ',', '.') ?></span></p>


        <!-- Form taruhan -->
        <?php if (!$game_started): ?>
            <form method="post">
                <label for="bet">Taruhan:</label>
                <input type="number" name="bet" id="bet" min="5000" max="<?= $balance ?>" required>

                <!-- Taruhan Cepat -->
                <div class="bet-options">
                    <button type="button" class="bet-button" data-bet="5000">Rp 5.000</button>
                    <button type="button" class="bet-button" data-bet="10000">Rp 10.000</button>
                    <button type="button" class="bet-button" data-bet="25000">Rp 25.000</button>
                    <button type="button" class="bet-button" data-bet="50000">Rp 50.000</button>
                </div>

                <br>

                <button type="submit" name="confirm_bet">Konfirmasi Taruhan</button>
            </form>
        <?php endif; ?>

        <!-- Grid Kartu -->
        <div class="card-grid">
            <?php for ($i = 0; $i < 9; $i++): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="selected_card" value="<?= $i + 1 ?>">
                    <button type="submit" name="pick_card" class="card <?= $game_over ? ($i + 1 == $chosen_number ? 'correct' : '') : 'hidden' ?> <?= $selected_card == ($i + 1) ? 'selected' : '' ?>" <?= $game_over ? 'disabled' : '' ?>>
                        <?= $game_over ? $cards[$i] : '?' ?>
                    </button>
                </form>
            <?php endfor; ?>
        </div>

        <!-- Pesan menang/kalah -->
        <p class="message">
            <?php
            if ($game_over) {
                echo $is_win ? "Selamat! Anda menang!" : "Maaf, Anda kalah!";
            }
            ?>
        </p>

        <!-- Refresh saldo setelah permainan selesai -->
        <script>
            setTimeout(() => {
                document.getElementById('balance').textContent = "<?= number_format($balance, 0, ',', '.') ?>";
            }, 1000);

            document.querySelectorAll('.bet-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var betAmount = this.getAttribute('data-bet');

                    if (betAmount === 'custom') {
                        var customBet = prompt("Masukkan jumlah taruhan (minimal Rp 5.000):", "5000");
                        if (customBet && !isNaN(customBet) && customBet >= 5000) {
                            document.getElementById('bet').value = customBet;
                        } else {
                            alert("Taruhan harus lebih dari atau sama dengan Rp 5.000.");
                        }
                    } else {
                        document.getElementById('bet').value = betAmount;
                    }
                });
            });
        </script>

        <!-- Tombol Back to Dashboard -->
<a href="dashboard.php" class="back-to-dashboard">
    <button type="button">Kembali ke Dashboard</button>
</a>

    </div>

    
</body>
</html>
