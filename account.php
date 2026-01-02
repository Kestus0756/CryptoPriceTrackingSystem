<?php
require 'vendor/autoload.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kriptovaliutu kainu stebejimas</title>
    <link rel="stylesheet" href="style_sheet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <?php

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not, redirect to login page
        header('Location: login.php');
        exit();
    }
    else
    {
        $user_id = $_SESSION['user_id'];
    }

    // Take info from MYSQL DB
    $conn = new mysqli('localhost', 'root', '', 'kriptovaliutos_db');

    $query = "SELECT symbol, target_price, triggered FROM alerts WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // User is logged in, display their info
    echo '<h1>Sveiki atvykę, ' . $_SESSION['username'] . '!</h1>';
    echo '<p>Jūsų el. paštas: ' . $_SESSION['email'] . '</p>';
    ?>

<br>
<div class="container">
    <h1>Jūsų sukurti perspėjimai</h1>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Kriptovaliuta</th>
                    <th>Tikslinė kaina (€)</th>
                    <th>Statusas</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($alert = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($alert['symbol']) ?></td>
                    <td><?= number_format($alert['target_price'], 2) ?></td>
                    <td class="<?= $alert['triggered'] ? 'triggered' : 'pending' ?>">
                        <?= $alert['triggered'] ? 'Išsiųstas' : 'Laukiama' ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

    <script>
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    </script>
</body>
