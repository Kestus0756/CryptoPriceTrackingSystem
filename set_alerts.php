<?php

require 'vendor/autoload.php';

session_start();
$conn = new mysqli('localhost', 'root', '', 'kriptovaliutos_db');

$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id'])) {
    echo "Vartotojas neprisijungęs.";
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $symbol = $_POST['symbol']; 
    $price = $_POST['price'];  

    $stmt = $conn->prepare("INSERT INTO alerts (user_id, symbol, target_price) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $user_id, $symbol, $price);

    if ($stmt->execute()) {
        echo 'Perspėjimas dėl kainos sėkmingai sukurtas!';
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

?>