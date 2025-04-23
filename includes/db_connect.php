<?php


// Initialize language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en'; // Default to English
}

// Check for language switch request
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, ['en', 'hi'])) {
        $_SESSION['language'] = $lang;
    }
    // Redirect to the same page without the lang parameter to avoid duplicate query strings
    $current_url = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $current_url");
    exit();
}

// Database connection
$host = 'sql300.infinityfree.com';
$db = 'if0_38754697_crop_market'; // Or 'weather_crop_app' if that's your database
$user = 'if0_38754697';
$pass = 'szxzha6g1GtF';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>