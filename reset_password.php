<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'kriptovaliutos_db');

$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$user = $result->fetch_assoc()) {
        die("Netinkama arba pasibaigusi nuoroda.");
    }

} else {
    die("Nenurodytas raktas.");
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Kriptovaliutų Kainos</title>
        <link rel="stylesheet" href="style_sheet.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <nav class="navbar">
            <div class="nav-logo">Kriptovaliutų kainos</div>
        </nav>

<form method="POST" id="login">
    <label>Naujas slaptažodis:</label>
    <input type="password" name="password" required>
    <button type="submit">Atnaujinti slaptažodį</button>
</form>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $update->bind_param("si", $newPassword, $user['id']);
        $update->execute();
        echo "<div style='display: flex; justify-content: center;'> Slaptažodis sėkmingai atnaujintas! Galite <a href='login.php'>prisijungti</a>.</div>";
        exit();
    }

?>
    <footer>
        KJ© 2025-2025
    </footer>
</body>