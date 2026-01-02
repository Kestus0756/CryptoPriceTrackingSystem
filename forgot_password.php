<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $conn = new mysqli('localhost', 'root', '', 'kriptovaliutos_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 14400); 

        // Save token to DB
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expires, $email);
        $update->execute();

        // Send email
        $resetLink = "http://localhost:8000/reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kestus0756@gmail.com';
            $mail->Password = 'cabw hwih fhmg vesi';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('ponas@gmail.com', 'Ponas Kripto');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Slaptazodzio atstatymas";
            $mail->Body = "Spustelėkite šią nuorodą norėdami atstatyti slaptažodį: <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            $message = "Atstatymo nuoroda išsiųsta į el. paštą!";
        } catch (Exception $e) {
            $message = "Nepavyko išsiųsti laiško. Klaida: {$mail->ErrorInfo}";
        }
    } else {
        echo "Tokio el. pašto adreso nėra.";
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
        </nav>

<form method="POST" id="login">
    <label>Įveskite savo el. pašto adresą:</label>
    <input type="email" name="email" required>
    <button type="submit">Siųsti atstatymo nuorodą</button>
</form>

<?php if (!empty($message)): ?>
    <div style="display: flex; justify-content: center;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<footer>
    KJ© 2025-2025
</footer>
</body>