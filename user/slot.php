<?php
session_start();
require '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil pengaturan terkait user
$stmt = $pdo->prepare("SELECT max_win_slots, max_bet, next_win FROM settings WHERE id_user = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch();

if ($settings) {
    $max_win_slots = $settings['max_win_slots'];
    $max_bet = $settings['max_bet'];
    $next_win = $settings['next_win'];
} else {
    // Jika tidak ada pengaturan, atur nilai default
    $max_win_slots = 0;
    $max_bet = 1000;
    $next_win = 0;
}

// Ambil saldo user
$stmt = $pdo->prepare("SELECT balance, win_count, loss_count FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $balance = $user['balance'];
    $win_count = $user['win_count'];
    $loss_count = $user['loss_count'];
} else {
    // Jika user tidak ditemukan
    header("Location: ../login.php");
    exit;
}

// Set minimum bet
$min_bet = 10000;

$is_win = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bet = $_POST['bet'];

    if ($bet < $min_bet) {
        $message = "Taruhan minimal adalah Rp 10.000!";
    } elseif ($bet > $balance) {
        $message = "Saldo tidak cukup!";
    } elseif ($bet > $max_bet) {
        $message = "Taruhan melebihi batas maksimal!";
    } else {
        // Tentukan apakah user menang atau kalah berdasarkan pengaturan
        if ($next_win == 1) {
            $is_win = true; // User menang pasti jika next_win diatur ke 1
        } elseif ($win_count < $max_win_slots) {
            $is_win = mt_rand(0, 1) == 1; // Menang atau kalah secara acak (50:50) menggunakan mt_rand
        } else {
            $is_win = false; // User pasti kalah jika sudah mencapai max_win_slots
        }

        // Simpan data taruhan ke bet_history
        try {
            $stmt = $pdo->prepare("INSERT INTO bet_history (user_id, bet_amount, result, timestamp) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $bet, $is_win ? 'win' : 'lose', date("Y-m-d H:i:s")])) {
                // Data berhasil dimasukkan ke bet_history
            } else {
                throw new Exception("Gagal menyimpan data ke bet_history.");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }

        // Update saldo dan statistik user nanti setelah animasi selesai
        $balance_change = $is_win ? $bet : -$bet;
        try {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ?, win_count = ?, loss_count = ? WHERE id = ?");
            $stmt->execute([ 
                $balance_change, 
                $is_win ? $win_count + 1 : $win_count, 
                !$is_win ? $loss_count + 1 : $loss_count, 
                $user_id 
            ]);

            // Perbarui pengaturan kemenangan jika diperlukan
            if ($win_count >= $max_win_slots) {
                $stmt = $pdo->prepare("UPDATE settings SET next_win = 0 WHERE id_user = ?");
                $stmt->execute([$user_id]);
            } 
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Slot</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
    --gold: #FFD700;
    --jade: #00FF87;
    --pyramid: #8B4513;
    --bg: #1a1a1a;
}

body {
    background: var(--bg);
    color: var(--gold);
    font-family: 'Arial', sans-serif;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.container {
    background: linear-gradient(45deg, #2c1e15, #3a2b1f);
    border: 3px solid var(--pyramid);
    border-radius: 15px;
    padding: 2rem;
    max-width: 800px;
    width: 100%;
    text-align: center;
}

h1 {
    color: var(--gold);
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.balance-info {
    margin: 20px 0;
    font-size: 1.2rem;
}

.status-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #00000060;
    border-radius: 10px;
}

.reels-container {
    display: flex; /* Change from grid to flex for horizontal layout */
    justify-content: center; /* Center the reels horizontally */
    gap: 20px; /* Adjust the space between the reels */
    margin: 2rem 0;
}

.reel-column {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.symbol {
    width: 70px;
    height: 70px;
    background: #4a3525;
    border: 2px solid var(--pyramid);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    transition: all 0.3s;
    animation: reelSpin 0.5s ease-in-out; /* Apply spin animation */
}

.symbol.win {
    background: #5a4525;
    animation: glow 1s infinite;
}

.symbol.scatter {
    color: var(--jade);
    animation: scatterPulse 1s infinite;
}

@keyframes glow {
    0% { transform: scale(1); box-shadow: 0 0 10px var(--gold); }
    50% { transform: scale(1.1); box-shadow: 0 0 20px var(--gold); }
    100% { transform: scale(1); box-shadow: 0 0 10px var(--gold); }
}

@keyframes scatterPulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes reelSpin {
    0% { transform: rotateY(90deg); opacity: 0; }
    50% { transform: rotateY(0deg); opacity: 1; }
    100% { transform: rotateY(90deg); opacity: 0; }
}

.controls {
    display: grid;
    gap: 1rem;
    margin-top: 2rem;
}

input[type="number"] {
    background: #00000060;
    border: 2px solid var(--pyramid);
    color: var(--gold);
    padding: 1rem;
    border-radius: 10px;
    font-size: 1.1rem;
}

button {
    background: var(--pyramid);
    color: var(--gold);
    border: none;
    padding: 1rem 2rem;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
}

button:hover {
    background: var(--gold);
    color: var(--pyramid);
}

.message {
    margin-top: 20px;
    font-size: 1.3rem;
    font-weight: bold;
}

.message.win {
    color: var(--jade);
}

.message.lose {
    color: red;
}

.game-info {
    margin-top: 2rem;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.links {
    margin-top: 20px;
}

.links a {
    text-decoration: none;
    color: var(--pyramid);
    font-weight: bold;
}

</style>
</head>
<body>
    <div class="container">
        <h1>Permainan Slot</h1>
        <p class="balance-info">Saldo Anda: Rp <span id="balance"><?= number_format($balance, 0, ',', '.') ?></span></p>

        <!-- Slot Container -->
        <div class="reels-container">
            <div class="reel-column">
                <div class="symbol" id="slot1">-</div>
            </div>
            <div class="reel-column">
                <div class="symbol" id="slot2">-</div>
            </div>
            <div class="reel-column">
                <div class="symbol" id="slot3">-</div>
            </div>
        </div>

        <form method="post">
            <label for="bet">Taruhan:</label>
            <input type="number" id="bet" name="bet" value="10000" min="10000" max="<?= $max_bet ?>" required>
            <br>
            <button type="submit">Taruhkan</button>
        </form>

        <p id="finalMessage" class="message">
            <?= isset($message) ? $message : '' ?>
        </p>

        <script>
            var isWin = <?= isset($is_win) && $is_win ? 'true' : 'false' ?>;

            function animateSlots() {
                let slot1 = document.getElementById('slot1');
                let slot2 = document.getElementById('slot2');
                let slot3 = document.getElementById('slot3');
                let finalMessage = document.getElementById('finalMessage');

                let numbers = ['7', 'X', '1', '2', '3', '4', '5', '6'];

                function randomNumber() {
                    return numbers[Math.floor(Math.random() * numbers.length)];
                }

                let interval1 = setInterval(() => {
                    slot1.textContent = randomNumber();
                }, 100);

                let interval2 = setInterval(() => {
                    slot2.textContent = randomNumber();
                }, 150);

                let interval3 = setInterval(() => {
                    slot3.textContent = randomNumber();
                }, 200);

                setTimeout(() => {
                    clearInterval(interval1);
                    clearInterval(interval2);
                    clearInterval(interval3);

                    if (isWin) {
                        slot1.textContent = slot2.textContent = slot3.textContent = "7"; // Menang
                        slot1.classList.add("winning");
                        slot2.classList.add("winning");
                        slot3.classList.add("winning");
                        finalMessage.textContent = "Selamat, Anda menang!";
                        finalMessage.classList.add("win");
                    } else {
                        slot1.textContent = randomNumber();
                        slot2.textContent = randomNumber();
                        slot3.textContent = randomNumber();
                        finalMessage.textContent = "Maaf, Anda kalah!";
                        finalMessage.classList.add("lose");
                    }

                    // Update saldo setelah animasi selesai
                    let newBalance = <?= $balance ?> + <?= isset($balance_change) ? $balance_change : 0 ?>;
                    document.getElementById('balance').textContent = newBalance.toLocaleString();
                }, 2000); // Durasi animasi
            }

            animateSlots(); // Mulai animasi saat halaman dimuat
        </script>

        <div class="links">
            <a href="dashboard.php">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>