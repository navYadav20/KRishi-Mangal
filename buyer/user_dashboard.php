<?php
session_start();
include '../includes/db_connect.php';
include '../includes/lang_helper.php';

// Debug: Log the initial session state
error_log("Initial session in user_dashboard.php: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || !isset($_SESSION['buyer_unique_id'])) {
    error_log("Redirecting to login due to missing session: " . print_r($_SESSION, true));
    header("Location: ../auth/buyer/user_login.php?error=" . urlencode(get_translation('session_expired')));
    exit();
}

$buyer_unique_id = $_SESSION['buyer_unique_id'];

// Fetch offers with crop and seller details
$stmt = $pdo->prepare("
    SELECT o.*, c.name, c.base_price, c.quantity as crop_quantity, s.whatsapp_number
    FROM offers o
    JOIN crops c ON o.crop_id = c.id
    JOIN sellers s ON c.seller_id = s.id
    WHERE o.buyer_unique_id = ?
");
$stmt->execute([$buyer_unique_id]);
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: Log the results to check if offers are being fetched
error_log("Fetched offers for buyer $buyer_unique_id: " . print_r($offers, true));
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('my_offers'); ?> - Crop Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .status-pending { color: yellow; font-weight: bold; }
        .status-accepted { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4"><?php echo get_translation('my_offers'); ?></h1>
        
        <?php if (empty($offers)): ?>
            <p class="text-center"><?php echo get_translation('no_offers_made'); ?></p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($offers as $offer): ?>
                    <div class="col">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($offer['name']); ?></h5>
                                <p class="card-text"><strong><?php echo get_translation('base_price'); ?>:</strong> ₹<?php echo number_format($offer['base_price'], 2); ?></p>
                                <p class="card-text"><strong><?php echo get_translation('available_quantity'); ?>:</strong> <?php echo $offer['crop_quantity']; ?></p>
                                <p class="card-text"><strong><?php echo get_translation('offered_price'); ?>:</strong> $<?php echo number_format($offer['offered_price'], 2); ?></p>
                                <p class="card-text"><strong><?php echo get_translation('quantity'); ?>:</strong> <?php echo $offer['quantity']; ?></p>
                                <?php if (strtoupper($offer['status']) !== 'PENDING'): ?>
                                    <p class="card-text"><strong><?php echo get_translation('whatsapp_number'); ?>:</strong> <?php echo htmlspecialchars($offer['whatsapp_number'] ?? get_translation('not_provided')); ?></p>
                                <?php endif; ?>
                                <p class="card-text"><strong><?php echo get_translation('status'); ?>:</strong> 
                                    <span class="status-<?php echo strtolower($offer['status']); ?>">
                                        <?php echo ucfirst($offer['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-3 text-center">
            <a href="../auth/buyer/user_logout.php" class="btn btn-danger"><?php echo get_translation('logout'); ?></a>
        </div>
    </div>

    <script src="../assets/js/theme-toggle.js"></script>
</body>
</html>