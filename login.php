<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'kriptovaliutos_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $usernameOrEmail = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Paruosti SQL statementa
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Patikrinti slaptazodi
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            header('Location: account.php');
            exit();
        } else {
            $_SESSION['error_message_login'] =  "Netinkamas slaptažodis.";
        }
    } else {
        $_SESSION['error_message_login'] = "Nerastas vartotojas su tokiu vardu arba el. pašto adresu.";
    }

    $stmt->close();
    $conn->close();

    header("Location: login.php");
    exit();
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
            <ul class="nav-links">
                <li><a href="index.php">Pagrindinis</a></li>
                <li><a href="register.php">Registruotis</a></li>
                <li><a href="login.php">Prisijungti</a></li>
            </ul>
        </nav>

<form method="POST" action="login.php" id="login">
    <label for="username_or_email">Vartotojo vardas arba El. paštas</label>
    <input type="text" id="username_or_email" name="username_or_email" required>

    <label for="password">Slaptažodis</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Prisijungti</button>
</form>

<p style="text-align: center;">
    <a href="forgot_password.php">Pamiršote slaptažodį?</a>
</p>

    <?php
        // Display error message if available
        if (isset($_SESSION['error_message_login'])) {
            echo "<p style='color: red; display: flex; justify-content: center'>" . $_SESSION['error_message_login'] . "</p>";
            unset($_SESSION['error_message_login']);  // Clear the error message after displaying
        }
    ?>

<footer>
    KJ© 2025-2025
</footer>
</body>