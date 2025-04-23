<?php
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/lang_helper.php';

// Start output buffering to prevent header errors
ob_start();

// Check if the seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: /ecommerce/auth/seller/login.php?error=" . urlencode(get_translation('session_expired')));
    ob_end_flush();
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsapp_number = filter_input(INPUT_POST, 'whatsapp_number', FILTER_SANITIZE_STRING);

    // Validate WhatsApp number (basic validation)
    if (empty($whatsapp_number) || !preg_match('/^\+?[1-9]\d{9,14}$/', $whatsapp_number)) {
        header("Location: /ecommerce/seller/seller_dashboard.php?error=whatsapp_update_failed");
        ob_end_flush();
        exit();
    }

    try {
        // Update the seller's WhatsApp number
        $stmt = $pdo->prepare("UPDATE sellers SET whatsapp_number = ? WHERE id = ?");
        $stmt->execute([$whatsapp_number, $seller_id]);

        // Redirect with success message
        header("Location: /ecommerce/seller/seller_dashboard.php?success=whatsapp_updated");
        ob_end_flush();
        exit();
    } catch (PDOException $e) {
        error_log("Update WhatsApp error: " . $e->getMessage());
        header("Location: /ecommerce/seller/seller_dashboard.php?error=whatsapp_update_failed");
        ob_end_flush();
        exit();
    }
} else {
    // If the form is not submitted, redirect back
    header("Location: /ecommerce/seller/seller_dashboard.php?error=invalid_request");
    ob_end_flush();
    exit();
}