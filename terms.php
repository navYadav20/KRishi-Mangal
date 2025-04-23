<?php
session_start();
require_once 'includes/lang_helper.php';// Include language configuration
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('termsOfService'); ?> - Krishi Mangal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/index.css?v=2">
    <link rel="stylesheet" href="assets/css/info.css?v=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'includes/navbar.php'; // Include language configuration ?>

    <!-- Terms of Service Section -->
    <div class="page-wrapper">
    <!-- Terms of Service Section -->
    <section class="terms-section">
        <div class="container">
            <h1><?php echo get_translation('Terms Of Service'); ?></h1>
            <h3><?php echo get_translation('Terms Introduction'); ?></h3>
            <p>Welcome to Krishi Mangal By accessing and using our platform, you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree, please do not use the site.</p>
            <h3><?php echo get_translation('Terms Usage'); ?></h3>
            <p>You may use Krishi Mangal only for lawful purposes. Prohibited activities include fraud, harassment, or any attempt to manipulate the marketplace. Violations will result in immediate account suspension.</p>
            <h3><?php echo get_translation('Terms Liability'); ?></h3>
            <p>Krishi Mangal acts as a facilitator and is not responsible for disputes between users. We do not guarantee the quality, safety, or legality of items listed on the platform.</p>
        </div>
    </section>
</div>

<!-- Footer -->
<footer>
    <div class="container">
        <p>© <?php echo date('Y'); ?> Krishi Mangal. <?php echo get_translation('allRightsReserved'); ?></p>
        <div>
            <a href="about.php"><?php echo get_translation('aboutUs'); ?></a> |
            <a href="contact.php"><?php echo get_translation('contactUs'); ?></a> |
            <a href="terms.php"><?php echo get_translation('termsOfService'); ?></a>
        </div>
    </footer>

    <!-- Bootstrap JS and Custom JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/themeToggle.js"></script>
</body>
</html>