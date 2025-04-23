<?php
session_start();
require_once '../../includes/db_connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../../auth/seller/login.php?error=" . urlencode(get_translation('session_expired')));
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_id = filter_input(INPUT_POST, 'crop_id', FILTER_VALIDATE_INT);

    // Validate input
    if ($crop_id === false) {
        header("Location: ../../seller/seller_dashboard.php?error=crop_sold_failed");
        exit();
    }

    try {
        // Verify that the crop belongs to the seller and is not already sold
        $stmt = $pdo->prepare("SELECT seller_id, status FROM crops WHERE id = ?");
        $stmt->execute([$crop_id]);
        $crop = $stmt->fetch();

        if (!$crop || $crop['seller_id'] != $seller_id) {
            header("Location: ../../seller/seller_dashboard.php?error=crop_sold_failed");
            exit();
        }

        if ($crop['status'] === 'sold') {
            header("Location: ../../seller/seller_dashboard.php?error=crop_already_sold");
            exit();
        }

        // Begin transaction to ensure atomicity
        $pdo->beginTransaction();

        // Mark the crop as sold
        $stmt = $pdo->prepare("UPDATE crops SET status = 'sold' WHERE id = ?");
        $stmt->execute([$crop_id]);

        // Reject any pending offers for this crop
        $stmt = $pdo->prepare("UPDATE offers SET status = 'rejected' WHERE crop_id = ? AND status = 'pending'");
        $stmt->execute([$crop_id]);

        // Commit the transaction
        $pdo->commit();

        // Redirect with success message
        header("Location: ../../seller/seller_dashboard.php?success=crop_sold");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Mark as sold error: " . $e->getMessage());
        header("Location: ../../seller/seller_dashboard.php?error=crop_sold_failed");
        exit();
    }
} else {
    // If the form is not submitted, redirect back
    header("Location: ../../seller/seller_dashboard.php?error=invalid_request");
    exit();
}