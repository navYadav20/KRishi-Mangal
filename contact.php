<?php
session_start();
require_once 'includes/lang_helper.php'; // Include language configuration
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('contactUs'); ?> - Krishi Mangal</title>
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

    <!-- Contact Us Section -->
    <section class="contact-section">
        <div class="container">
            <h1><?php echo get_translation('contactUs'); ?></h1>
            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <form action="submit_contact.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label"><?php echo get_translation('name'); ?></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><?php echo get_translation('email'); ?></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label"><?php echo get_translation('message'); ?></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo get_translation('submit'); ?></button>
                    </form>
                </div>
            </div>
            <!-- <h3><center><?php echo get_translation('Contact Info'); ?></center></h3> -->
            <h3 class="text-center"><?php echo get_translation('Contact Info'); ?></h3>
            <p><i class="fas fa-envelope me-2"></i> support@krishimangal.com</p>
            <p><i class="fas fa-phone me-2"></i> +91 7727998148</p>
            <p><i class="fas fa-map-marker-alt me-2"></i> C-124, Bhamashah Mandi, Kota, Rajasthan India</p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© <?php echo date('Y'); ?> Krishi Mangal. <?php echo get_translation('allRightsReserved'); ?></p>
            <div>
                <a href="about.php"><?php echo get_translation('aboutUs'); ?></a> |
                <a href="contact.php"><?php echo get_translation('contactUs'); ?></a> |
                <a href="terms.php"><?php echo get_translation('termsOfService'); ?></a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS and Custom JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme-Toggle.js"></script>
</body>
</html>