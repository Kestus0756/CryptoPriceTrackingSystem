<?php
require 'vendor/autoload.php';
session_start();
?>

<!DOCTYPE html>
<meta charset="UTF-8">
<html lang="en">
<head>
    <title>Kriptovaliutų Kainos</title>
    <link rel="stylesheet" href="style_sheet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="messageBox" class="box" style="display:none;"></div>
    <nav class="navbar">
        <div class="nav-logo">Kriptovaliutų kainos</div>
        <ul class="nav-links">
            <li><a href="#">Pagrindinis</a></li>
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

    <div class="sort-container">
    <label for="sort-select">Rūšiuoti pagal:</label>
    <select id="sort-select" class="fancy-select">
        <option value="name">Pavadinimas (A - Z)</option>
        <option value="price">Kaina (Aukščiausia - žemiausia)</option>
    </select>
    </div>

    <div class="tracker-container">
        <div class="price-cards">
            <div class="card" id="BTC" data-name="Bitcoin" data-price="0">
                <h2>Bitcoin (BTC)</h2>
                <a href="graphs.php?symbol=BTCEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="btc-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="btc-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="BTC">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="ETH" data-name="Ethereum" data-price="0">
                <h2>Ethereum (ETH)</h2>
                <a href="graphs.php?symbol=ETHEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="eth-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="eth-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="ETH">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="BNB" data-name="Binance Coin" data-price="0">
                <h2>Binance Coin (BNB)</h2>
                <a href="graphs.php?symbol=BNBEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="bnb-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="bnb-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="BNB">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="LTC" data-name="Lite Coin" data-price="0">
                <h2>Lite Coin (LTC)</h2>
                <a href="graphs.php?symbol=LTCEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="ltc-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="ltc-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="LTC">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="DOGE" data-name="Dogecoin" data-price="0">
                <h2>Doge Coin (DOGE)</h2>
                <a href="graphs.php?symbol=DOGEEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="doge-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="doge-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="DOGE">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="XRP" data-name="Ripple" data-price="0">
                <h2>Ripple (XRP)</h2>
                <a href="graphs.php?symbol=XRPEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="xrp-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="xrp-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="XRP">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="SOL" data-name="Solana" data-price="0">
                <h2>Solana (SOL)</h2>
                <a href="graphs.php?symbol=SOLEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="sol-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="sol-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="SOL">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="TRUMP" data-name="Trump Token" data-price="0">
                <h2>Trump (TRUMP)</h2>
                <a href="graphs.php?symbol=TRUMPEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="trump-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="trump-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="TRUMP">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="ADA" data-name="Cardano" data-price="0">
                <h2>Cardano (ADA)</h2>
                <a href="graphs.php?symbol=ADAEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="ada-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="ada-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="ADA">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="TRX" data-name="Tron" data-price="0">
                <h2>Tron (TRX)</h2>
                <a href="graphs.php?symbol=TRXEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="trx-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="trx-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="TRX">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="SUI" data-name="Sui" data-price="0">
                <h2>Sui (SUI)</h2>
                <a href="graphs.php?symbol=SUIEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="sui-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="sui-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="SUI">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
            <div class="card" id="LINK" data-name="Chainlink" data-price="0">
                <h2>Chainlink (LINK)</h2>
                <a href="graphs.php?symbol=LINKEUR"><p class="price">0.00€</p></a>
                <div>
                    <form class="alertForm" method="POST" action="set_alerts.php">
                        <label for="link-alert-price">Nustatykite kainos ribą. Jei kaina nukris žemiau jos, gausite pranešimą </label>
                        <input type="number" id="link-alert-price" name="price" placeholder="Įveskite kainą €" required>
                        <input type="hidden" name="symbol" value="LINK">
                        <button type="submit">Nustatyti</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <footer>
        KJ© 2025-2025
    </footer>
</body>
</html>
