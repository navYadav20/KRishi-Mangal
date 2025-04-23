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
        header("Location: ../../seller/seller_dashboard.php?error=crop_delete_failed");
        exit();
    }

    try {
        // Verify that the crop belongs to the seller
        $stmt = $pdo->prepare("SELECT seller_id, photo FROM crops WHERE id = ?");
        $stmt->execute([$crop_id]);
        $crop = $stmt->fetch();

        if (!$crop || $crop['seller_id'] != $seller_id) {
            header("Location: ../../seller/seller_dashboard.php?error=crop_delete_failed");
            exit();
        }

        // Begin transaction to ensure atomicity
        $pdo->beginTransaction();

        // Delete associated offers
        $stmt = $pdo->prepare("DELETE FROM offers WHERE crop_id = ?");
        $stmt->execute([$crop_id]);

        // Delete the crop
        $stmt = $pdo->prepare("DELETE FROM crops WHERE id = ?");
        $stmt->execute([$crop_id]);

        // Delete the crop's photo file if it exists
        if ($crop['photo']) {
            $photo_path = "../../uploads/crops/" . $crop['photo'];
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }

        // Commit the transaction
        $pdo->commit();

        // Redirect with success message
        header("Location: ../../seller/seller_dashboard.php?success=crop_deleted");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Delete crop error: " . $e->getMessage());
        header("Location: ../../seller/seller_dashboard.php?error=crop_delete_failed");
        exit();
    }
} else {
    // If the form is not submitted, redirect back
    header("Location: ../../seller/seller_dashboard.php?error=invalid_request");
    exit();
}