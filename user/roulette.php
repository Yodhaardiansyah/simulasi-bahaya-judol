<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roulette Game</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
        }
        .roulette-container {
            position: relative;
            width: 400px;
            height: 400px;
            margin: 20px auto;
        }
        .roulette-wheel {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 5px solid black;
            position: absolute;
            background: conic-gradient(red 25%, black 25% 50%, red 50% 75%, black 75%);
            transition: transform 5s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .arrow {
            width: 0; 
            height: 0; 
            border-top: 20px solid transparent;
            border-bottom: 20px solid transparent;
            border-right: 40px solid black;
            position: absolute;
            left: 50%;
            top: -40px;
            transform: translateX(-50%);
        }
        .numbers-container {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }
        .number {
            position: absolute;
            font-size: 18px;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 3px black;
            transform: translate(-50%, -50%);
        }
        #result {
            font-size: 24px;
            font-weight: bold;
            color: darkblue;
            margin-top: 20px;
        }
        .balance {
            font-size: 20px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
    <h1>Roulette Game</h1>
    <p class="balance">Saldo: $<span id="balance">1000</span></p>
    <label for="betAmount">Taruhan ($):</label>
    <input type="number" id="betAmount" min="1" max="1000">
    <label for="betNumber">Angka (0-36):</label>
    <input type="number" id="betNumber" min="0" max="36">
    <button onclick="spinRoulette()">Putar Roda</button>
    
    <div class="roulette-container">
        <div class="roulette-wheel">
            <div class="numbers-container"></div>
        </div>
        <div class="arrow"></div>
    </div>
    
    <p id="result"></p>
    
    <script>
        let balance = 1000;
        let numbersContainer = document.querySelector(".numbers-container");
        let rouletteWheel = document.querySelector(".roulette-wheel");

        function createNumbers() {
            numbersContainer.innerHTML = "";
            let totalNumbers = 37;
            let radius = 160;
            let centerX = 200;
            let centerY = 200;
            
            for (let i = 0; i < totalNumbers; i++) {
                let angle = (i * (360 / totalNumbers) - 90) * (Math.PI / 180);
                let x = centerX + radius * Math.cos(angle);
                let y = centerY + radius * Math.sin(angle);
                
                let numDiv = document.createElement("div");
                numDiv.classList.add("number");
                numDiv.innerText = i;
                numDiv.style.left = `${x}px`;
                numDiv.style.top = `${y}px`;
                numbersContainer.appendChild(numDiv);
            }
        }

        createNumbers();

        function spinRoulette() {
            let betAmount = parseInt(document.getElementById("betAmount").value);
            let betNumber = parseInt(document.getElementById("betNumber").value);
            
            if (isNaN(betAmount) || isNaN(betNumber) || betAmount <= 0 || betNumber < 0 || betNumber > 36) {
                alert("Masukkan taruhan dan angka yang valid!");
                return;
            }
            if (betAmount > balance) {
                alert("Saldo tidak cukup!");
                return;
            }

            let winningNumber = Math.floor(Math.random() * 37);

            // Menghitung rotasi agar panah menunjuk angka pemenang
            let anglePerNumber = 360 / 37;
            let targetAngle = 3600 + (270 - (winningNumber * anglePerNumber));

            rouletteWheel.style.transition = "transform 5s cubic-bezier(0.25, 0.8, 0.25, 1)"; // Perubahan kecepatan rotasi
            rouletteWheel.style.transform = `rotate(${targetAngle}deg)`;

            setTimeout(() => {
                document.getElementById("result").innerText = `Angka pemenang: ${winningNumber}`;
                if (betNumber === winningNumber) {
                    balance += betAmount * 35;
                    document.getElementById("result").innerText += " - Selamat, Anda menang!";
                } else {
                    balance -= betAmount;
                    document.getElementById("result").innerText += " - Anda kalah.";
                }
                document.getElementById("balance").innerText = balance;
            }, 5000); // Menunggu 5 detik untuk menampilkan hasil
        }
    </script>
</body>
</html>
