<?php
header('Content-Type: application/json');
include 'includes/db_connect.php';

$filters = [
    'crop_type' => isset($_GET['crop_type']) ? $_GET['crop_type'] : '',
    'organic' => isset($_GET['organic']) ? (int)$_GET['organic'] : '',
    'price_min' => isset($_GET['price_min']) ? (float)$_GET['price_min'] : 0,
    'price_max' => isset($_GET['price_max']) ? (float)$_GET['price_max'] : 1000,
    'availability' => isset($_GET['availability']) ? $_GET['availability'] : ''
];

try {
    $query = "SELECT id, name, description, photo, base_price, quantity, crop_type, is_organic FROM crops WHERE 1=1";
    $params = [];

    if (!empty($filters['crop_type'])) {
        $query .= " AND crop_type = ?";
        $params[] = $filters['crop_type'];
    }

    if ($filters['organic'] === 1) {
        $query .= " AND is_organic = 1";
    }

    if ($filters['price_min'] > 0) {
        $query .= " AND base_price >= ?";
        $params[] = $filters['price_min'];
    }

    if ($filters['price_max'] < 1000) {
        $query .= " AND base_price <= ?";
        $params[] = $filters['price_max'];
    }

    if ($filters['availability'] === 'in_stock') {
        $query .= " AND quantity > 0";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $crops = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($crops);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>