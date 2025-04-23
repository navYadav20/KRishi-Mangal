<?php
session_start();
include '../includes/db_connect.php';
include '../includes/lang_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/buyer/user_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_id = intval($_POST['crop_id']);
    $quantity = intval($_POST['quantity']);
    $offered_price = floatval($_POST['offered_price']);
    $buyer_unique_id = $_SESSION['buyer_unique_id'];

    // Debug: Log the input data
    error_log("Make offer attempt - crop_id: $crop_id, quantity: $quantity, offered_price: $offered_price, buyer_unique_id: $buyer_unique_id");

    // Validate inputs
    if ($quantity <= 0 || $offered_price <= 0) {
        $error = get_translation('invalid_offer');
        header("Location: ../index.php?error=" . urlencode($error));
        exit();
    }

    // Check if crop exists and has sufficient quantity
    $stmt = $pdo->prepare("SELECT quantity FROM crops WHERE id = ?");
    $stmt->execute([$crop_id]);
    $crop = $stmt->fetch();

    if (!$crop || $crop['quantity'] < $quantity) {
        $error = $crop ? get_translation('insufficient_quantity') : get_translation('crop_not_found');
        header("Location: ../index.php?error=" . urlencode($error));
        exit();
    }

    try {
        // Debug: Verify buyer_unique_id exists in users table
        $stmt = $pdo->prepare("SELECT id FROM users WHERE unique_id = ?");
        $stmt->execute([$buyer_unique_id]);
        $buyer_exists = $stmt->fetch();
        if (!$buyer_exists) {
            error_log("Foreign key violation: buyer_unique_id $buyer_unique_id not found in users table");
            throw new Exception("Buyer not found in database");
        }

        // Insert the offer into the offers table
        $stmt = $pdo->prepare("INSERT INTO offers (crop_id, buyer_unique_id, offered_price, quantity, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$crop_id, $buyer_unique_id, $offered_price, $quantity]);
        header("Location: ../buyer/user_dashboard.php?success=" . urlencode(get_translation('offer_submitted')));
        exit();
    } catch (PDOException $e) {
        error_log("Database error in make_offer.php: " . $e->getMessage());
        $error = get_translation('database_error') . ": " . $e->getMessage();
        header("Location: ../index.php?error=" . urlencode($error));
        exit();
    } catch (Exception $e) {
        error_log("General error in make_offer.php: " . $e->getMessage());
        $error = get_translation('database_error') . ": " . $e->getMessage();
        header("Location: ../index.php?error=" . urlencode($error));
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}