<?php
session_start();
require '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user dan pengaturan
$stmt = $pdo->prepare("SELECT users.*, settings.* 
    FROM users 
    LEFT JOIN settings ON users.id = settings.id_user 
    WHERE users.id = ?");
$stmt->execute([$user_id]);
$data = $stmt->fetch();

// Inisialisasi variabel
$balance = $data['balance'];
$multiplier = $data['multiplier'] ?? 1;
$free_spins = $data['free_spins'] ?? 0;
$symbols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'M']; // M = Mask Scatter
$reels = [];
$win = 0;
$cascade_level = 0;

// Proses taruhan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bet = $_POST['bet'];
    
    if($free_spins > 0) {
        $bet = 0;
        $free_spins--;
    }

    if ($bet > $balance && $free_spins === 0) {
        $message = "Saldo tidak cukup!";
    } else {
        // Generate reels Megaways (3-7 simbol per reel)
        $reels = array_map(function() use ($symbols) {
            $size = rand(3,7);
            return array_map(function() use ($symbols) {
                return $symbols[array_rand($symbols)];
            }, range(1, $size));
        }, range(1,5));

        // Proses cascade
        do {
            $win_this_level = 0;
            
            // Hitung kemenangan berdasarkan kombinasi simbol
            foreach($reels as $reel) {
                // Hitung frekuensi simbol
                $symbol_counts = array_count_values($reel);
                foreach($symbol_counts as $symbol => $count) {
                    // Pemenang jika ada lebih dari 2 simbol yang sama dalam satu reel
                    if ($count >= 3) {
                        $win_this_level += ($symbol_counts[$symbol]) * 50 * $multiplier;
                    }
                }
            }

            // Update total win dan multiplier
            if($win_this_level > 0) {
                $win += $win_this_level;
                $multiplier *= 1.5; // Multiplier akan naik lebih lambat untuk keseimbangan permainan
                $cascade_level++;
                
                // Hapus simbol yang menang
                foreach($reels as &$reel) {
                    // Hapus simbol yang menang dan ganti dengan simbol baru
                    foreach ($reel as $index => $symbol) {
                        if ($symbol === $symbol) {
                            unset($reel[$index]);
                            array_unshift($reel, $symbols[array_rand($symbols)]);
                        }
                    }
                }
            }
        } while($win_this_level > 0 && $cascade_level < 3); // Batasi cascade hingga 3 level saja

        // Hitung scatter
        $scatter_count = array_reduce($reels, function($carry, $reel) {
            return $carry + count(array_filter($reel, fn($s) => $s === 'M'));
        }, 0);

        if($scatter_count >= 3) {
            $free_spins += $scatter_count * 3; // Mengurangi jumlah free spins yang diberikan per scatter
            $win += $scatter_count * 500 * $multiplier; // Kemenangan dari scatter lebih rendah
        }

        // Update database
        $new_balance = $balance + $win - ($free_spins > 0 ? 0 : $bet);
        $stmt = $pdo->prepare("UPDATE users 
            SET balance = ?, 
                win_count = win_count + ?, 
                loss_count = loss_count + ? 
            WHERE id = ?");
        $stmt->execute([$new_balance, ($win > 0 ? 1 : 0), ($win === 0 ? 1 : 0), $user_id]);

        $stmt = $pdo->prepare("UPDATE settings 
            SET multiplier = ?, 
                free_spins = ? 
            WHERE id_user = ?");
        $stmt->execute([$multiplier, $free_spins, $user_id]);

        // Update session
        $_SESSION['game_result'] = [
            'reels' => $reels,
            'win' => $win,
            'multiplier' => $multiplier,
            'cascade_level' => $cascade_level,
            'free_spins' => $free_spins
        ];
        
        header("Refresh:0");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aztec Gems Megaways</title>
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
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
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
        }

        .symbol.win {
            background: #5a4525;
            animation: glow 1s infinite;
        }

        .symbol.scatter {
            color: var(--jade);
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
            0% { transform: translateY(-50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
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
            animation: reelSpin 0.5s ease-in-out;
        }

        .symbol.win {
            background: #5a4525;
            animation: glow 1s infinite;
        }

        .symbol.scatter {
            color: var(--jade);
            animation: scatterPulse 1s infinite;
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

        .game-info {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-bar">
            <div>Saldo: <?= number_format($balance) ?></div>
            <div>Multiplier: <?= $multiplier ?>x</div>
            <div>Free Spins: <?= $free_spins ?></div>
        </div>

        <div class="reels-container">
            <?php if(isset($_SESSION['game_result'])): ?>
                <?php foreach($_SESSION['game_result']['reels'] as $reel): ?>
                    <div class="reel-column">
                        <?php foreach($reel as $symbol): ?>
                            <div class="symbol <?= count(array_unique($reel)) === 1 ? 'win' : '' ?> <?= $symbol === 'M' ? 'scatter' : '' ?>">
                                <?= $symbol ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['game_result']) ?>
            <?php else: ?>
                <?php for($i=0; $i<5; $i++): ?>
                    <div class="reel-column">
                        <?php for($j=0; $j<5; $j++): ?>
                            <div class="symbol">?</div>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>

        <form method="post" class="controls">
            <input type="number" 
                   name="bet" 
                   value="10000" 
                   min="100" 
                   max="100000"
                   step="100"
                   <?= $free_spins > 0 ? 'disabled' : '' ?>>
            <button type="submit">
                <?= $free_spins > 0 ? "FREE SPIN ($free_spins)" : "SPIN" ?>
            </button>
        </form>

        <div class="game-info">
            <div>Kemenangan Terakhir: <?= $win ?? 0 ?></div>
            <div>Level Cascade: <?= $cascade_level ?? 0 ?></div>
        </div>
    </div>
</body>
</html>