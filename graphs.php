<?php
require 'vendor/autoload.php';

session_start();
$symbol = isset($_GET['symbol']) ? strtoupper($_GET['symbol']) : 'BTCEUR';
$escapedSymbol = escapeshellarg($symbol);

$rsiOutput = shell_exec(' node js\rsi_calc.js ' . $escapedSymbol . ' 2>&1');

$rsi = floatval(trim($rsiOutput));
$baseSymbol = substr($symbol, 0, -3);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Kriptovaliutu kainu stebejimas</title>
        <link rel="stylesheet" href="style_sheet.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="js/script.js"></script>
    </head>
    <body>
        <nav class="navbar">
        <div class="nav-logo">Kriptovaliutų kainos</div>
        <ul class="nav-links">
            <li><a href="index.php">Pagrindinis</a></li>
            <?php if (!isset($_SESSION['user_id'])) { ?>
                <li><a href="register.php">Registruotis</a></li>
                <li><a href="login.php">Prisijungti</a></li>
            <?php } ?>
            <?php if (isset($_SESSION['user_id'])) { ?>
            <li class="dropdown">
                <a href="account.php" class="dropbtn">Paskyra</a>
                <div class="dropdown-content">
                        <a href="account.php">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <a href="logout.php">Atsijungti</a>
                    <?php } ?>
                </div>
            </li>
        </ul>
    </nav>


        <div id="timePeriodSelector-text">
            Rodyti:
            <button class="timePeriodSelector" onclick="setTimeRange('24h')">24 val.</button>
            <button class="timePeriodSelector" onclick="setTimeRange('7d')">7 dienų</button>
            <button class="timePeriodSelector" onclick="setTimeRange('30d')">30 dienų</button>
        </div>

        <div class="chart-row">
            <div class="chart-wrapper">
                <canvas 
                    id="<?= strtolower($baseSymbol) ?>PriceChart" 
                    width="600" 
                    height="300" 
                    class="chart" 
                    data-symbol="<?= strtoupper($baseSymbol) ?>">
                </canvas>
            </div>
            <div class="rsi-card-small">
                    <p>Santykinis stiprumo indeksas (RSI) <?= htmlspecialchars($baseSymbol) ?> valiutai</p>
                    <?php
                    $class = 'mid';
                    if ($rsi < 30) $class = 'low';
                    elseif ($rsi > 70) $class = 'high';
                    ?>
                    <div class="rsi-value <?= $class ?>"><?= number_format($rsi, 2) ?></div>
                </div>
        </div>

            </body>
            </html>
    </body>
</html>