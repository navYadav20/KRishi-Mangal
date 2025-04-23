<?php
$host = "localhost";
$dbname = "crop_market";
$username = "root";
$password = "root";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set the timezone to IST for the database session
    $conn->exec("SET time_zone = '+05:30'");
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>