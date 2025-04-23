<?php
session_start();
include '../includes/db_connect.php';

// Debug: Log the crop ID being requested
error_log("get_crop_details.php - Requested Crop ID: " . ($_GET['id'] ?? 'Not set'));

if (isset($_GET['id'])) {
    $crop_id = intval($_GET['id']);
    $query = "SELECT * FROM crops WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $crop_id]);
    $crop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crop) {
        // Debug: Log the fetched crop data
        error_log("get_crop_details.php - Fetched Crop: " . json_encode($crop));

        echo '<h4>' . htmlspecialchars($crop['name']) . '</h4>';
        echo '<p>' . nl2br(htmlspecialchars($crop['description'])) . '</p>';
        echo '<p><strong>Price:</strong> $' . number_format($crop['base_price'], 2) . '</p>';
        echo '<p><strong>Quantity:</strong> ' . htmlspecialchars($crop['quantity']) . '</p>';
        echo '<p><strong>Crop Type:</strong> ' . htmlspecialchars($crop['crop_type']) . '</p>';
        echo '<p><strong>Organic:</strong> ' . ($crop['is_organic'] ? 'Yes' : 'No') . '</p>';
        echo '<p><strong>Harvest Date:</strong> ' . ($crop['harvest_date'] ?: 'N/A') . '</p>';
        echo '<p><strong>Location:</strong> ' . htmlspecialchars($crop['location'] ?: 'Unknown') . '</p>';
        echo '<p><strong>Certification:</strong> ' . htmlspecialchars($crop['certification'] ?: 'None') . '</p>';
        echo '<p><strong>Seller Notes:</strong> ' . htmlspecialchars($crop['seller_notes'] ?: 'None') . '</p>';
        echo '<img src="./uploads/crops/' . htmlspecialchars($crop['photo']) . '" alt="' . htmlspecialchars($crop['name']) . '" style="max-width: 100%; height: auto;">';
    } else {
        echo '<div class="alert alert-danger">Crop not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}