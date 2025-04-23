<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['seller_id'])) {
    header("Location: ../auth/seller/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $offer_id = $_POST['offer_id'];
    $quantity = $_POST['quantity'];
    $status = isset($_POST['accept']) ? 'accepted' : 'rejected';
    
    if ($status === 'accepted') {
        // Update crop quantity when offer is accepted
        $stmt = $pdo->prepare("
            UPDATE crops c
            JOIN offers o ON c.id = o.crop_id
            SET c.quantity = c.quantity - ?
            WHERE o.id = ?
        ");
        $stmt->execute([$quantity, $offer_id]);

        // Get the seller's WhatsApp number and store it with the offer
        $seller_whatsapp = $_SESSION['whatsapp_number'];
        $stmt = $pdo->prepare("UPDATE offers SET status = ?, seller_whatsapp = ? WHERE id = ?");
        $stmt->execute([$status, $seller_whatsapp, $offer_id]);
    } else {
        // Update status without storing WhatsApp for rejected offers
        $stmt = $pdo->prepare("UPDATE offers SET status = ? WHERE id = ?");
        $stmt->execute([$status, $offer_id]);
    }
    
    header("Location: seller_dashboard.php");
}
?>