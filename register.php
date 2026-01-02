<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'kriptovaliutos_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    // Validacija
    if (substr_count($email, '@') > 1) {
        $_SESSION['error_message'] = "Netinkamas el. pašto formatas.";
        header('Location: register.php');
        exit();
    }

    if ($password !== $passwordConfirm) {
        $_SESSION['error_message'] = "Slaptažodžiai neatitinka.";
        header('Location: register.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Netinkamas el. pašto formatas.";
        header('Location: register.php');
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Paziureti ar egzistuoja el. pastas bei vartotojo vardas
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Vartotojo vardas arba el. pašto adresas užimtas.";
        $stmt->close();
        $conn->close();
        header('Location: register.php');
        exit();
    }
    $stmt->close();

    $insertStmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $insertStmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($insertStmt->execute()) {
        $_SESSION['message'] = "Registracija sėkminga.";
        $insertStmt->close();
        $conn->close();
        header('Location: register.php');
        exit();
    } else {
        echo "Error: " . $conn->error;
        $insertStmt->close();
        $conn->close();
    }
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

    <form method="POST" action="register.php" id="register">
        <label for="username">Vartotojo vardas</label>
        <input type="text" id="username" name="username" required>

        <label for="email">El. paštas</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Slaptažodis</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirm">Patvirtinti slaptažodį</label>
        <input type="password" id="password_confirm" name="password_confirm" required>

        <button type="submit">Registruotis</button>
    </form>
    
    <?php
        // Display error message if available
        if (isset($_SESSION['error_message'])) {
            echo "<p style='color: red; display: flex; justify-content: center'>" . $_SESSION['error_message'] . "</p>";
            unset($_SESSION['error_message']);  // Clear the error message after displaying
        }

        if (isset($_SESSION['message'])) {
            echo "<p style='padding-top: 10px; color: black; display: flex; justify-content: center'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']);  // Clear the error message after displaying
        }
        ?>


    <footer>
        KJ© 2025-2025
    </footer>    
    </body>

