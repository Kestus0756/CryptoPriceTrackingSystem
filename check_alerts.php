<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

//session_start();

$conn = new mysqli('localhost', 'root', '', 'kriptovaliutos_db');

//if (!isset($_SESSION['email'])) {
//    echo "Please log in first!";
//    exit();
//}

// Get the user_id from the session (assuming the user is logged in)
//$user_id = $_SESSION['user_id'];

function sendPriceAlertEmail($email, $symbol, $price, $alert_id) {
        $mail = new PHPMailer(true); 
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ccts_owner@gmail.com';
            $mail->Password = ''; // E-mail slaptazodis
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('ccts@gmail.com', 'sender');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Pranesimas del kainos";
            $mail->Body    = "Sveiki, <br><br> Pranešame kad {$symbol} nukrito iki €{$price}. <br><br> Ačiū kad naudojatės kriptovaliutų sistema.";

            $mail->send();

            global $conn; 
            $updateStmt = $conn->prepare("UPDATE alerts SET triggered = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $alert_id);
            $updateStmt->execute();
            $updateStmt->close();

            echo "El. laiškas išsiųstas $email!";
        } catch (Exception $e) {
            echo "Laiškas nebuvo išsiųstas. Klaida: {$mail->ErrorInfo}";
        }
    }

$alertsQuery = "SELECT a.*, u.email FROM alerts a JOIN users u ON a.user_id = u.id WHERE a.triggered = 0";
$stmt = $conn->prepare($alertsQuery);

if ($stmt === false) {
    die('MySQL prepare failed: ' . $conn->error);
}

$stmt->execute();

// Padaryti rezultat
$alertsResult = $stmt->get_result();

while ($alert = $alertsResult->fetch_assoc()) {
    $symbol = $alert['symbol'];
    $target_price = $alert['target_price'];
    $user_email = $alert['email'];
    $alert_id = $alert['id'];

    // Gauti dabartine kaina
    $apiUrl = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $symbol . 'EUR';
    $response = file_get_contents($apiUrl);
    $currentPriceData = json_decode($response, true);
    
    if (isset($currentPriceData['price'])) {
        $current_price = floatval($currentPriceData['price']); // Convert to float for accurate comparison
        $target_price = floatval($target_price); // Convert target price to float as well

        // Debugging: Log both the current price and target price
        error_log("Current price for {$symbol}: {$current_price}");
        error_log("Target price for {$symbol}: {$target_price}");

        // Compare the current price with the target price
        if ($current_price <= $target_price) {
            // Debugging: Check if the price condition is met
            error_log("Price condition met for {$symbol}. Sending email...");

            // Send the email if the price is met and update the triggered flag
            sendPriceAlertEmail($user_email, $symbol, $current_price, $alert_id);
        } else {
            // Debugging: If the price condition is not met
            error_log("Price condition NOT met for {$symbol}. Current price: {$current_price}, Target price: {$target_price}");
        }
    } else {
        // If API response is invalid, log an error
        error_log("Error: Could not fetch price for {$symbol}.");
    }
}

$stmt->close();
$conn->close();

?>
