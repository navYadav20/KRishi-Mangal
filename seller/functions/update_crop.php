<?php
session_start();
require_once '../../includes/db_connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../auth/seller/login.php?error=" . urlencode(get_translation('session_expired')));
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_id = filter_input(INPUT_POST, 'crop_id', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    // Validate inputs
    if ($crop_id === false || $price === false || $quantity === false || $price < 0 || $quantity < 0) {
        header("Location: ../seller/seller_dashboard.php?error=crop_update_failed");
        exit();
    }

    try {
        // Verify that the crop belongs to the seller
        $stmt = $pdo->prepare("SELECT seller_id FROM crops WHERE id = ?");
        $stmt->execute([$crop_id]);
        $crop = $stmt->fetch();

        if (!$crop || $crop['seller_id'] != $seller_id) {
            header("Location: ../seller/seller_dashboard.php?error=crop_update_failed");
            exit();
        }

        // Update the crop details
        $stmt = $pdo->prepare("UPDATE crops SET base_price = ?, quantity = ? WHERE id = ?");
        $stmt->execute([$price, $quantity, $crop_id]);

        // Redirect with success message
        header("Location: ../seller_dashboard.php?success=crop_updated");
        exit();
    } catch (PDOException $e) {
        error_log("Update crop error: " . $e->getMessage());
        header("Location: ../seller/seller_dashboard.php?error=crop_update_failed");
        exit();
    }
} else {
    // If the form is not submitted, redirect back
    header("Location: ../seller/seller_dashboard.php?error=invalid_request");
    exit();
}