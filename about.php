<?php
session_start();
require_once 'includes/lang_helper.php'; // Include language configuration
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('aboutUs'); ?> - Krishi Mangal</title>
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

    <!-- About Us Section -->
    <div class="page-wrapper">
        <!-- About Us Section -->
        <section class="about-section">
            <div class="container">
                <h1><?php echo get_translation('aboutUs'); ?></h1>
                <!-- <p><?php echo get_translation('aboutUsIntro'); ?></p> -->
                <h3><?php echo get_translation('🌿 Our Mission'); ?></h3>
                <p>At Krishi Mangal, our mission is to empower Indian farmers, especially small-scale producers, by offering a transparent and direct digital platform where they can predict crops, get expert suggestions, and sell their produce directly to buyers.</p>
                <h3><?php echo get_translation('🌾 Our Vision'); ?></h3>
                <p>We envision a future where technology bridges the gap between rural farming and urban markets, bringing sustainable agricultural practices and economic growth. Our goal is to positively impact the lives of 10,000+ farmers by 2030.</p>
                <h3><?php echo get_translation('🏬 About the Founder'); ?></h3>
                <p><b>Krishi Mangal is backed by "Thakur Shree Banke Bihari Trading Company" </b>, a trusted grain merchant at Seth Bhamashah Mandi, Kota, Rajasthan. With years of experience in grain trade and mandi operations, we understand the ground-level challenges farmers face daily. Our deep mandi network and industry knowledge help us combine traditional agriculture with modern technology to bring meaningful change.</p> </br>
                <p>Founder’s Business Details:  <b>Thakur Shree Banke Bihari Trading Company</b>, Shop No.: C-124, Bhamashah Krishi Upaz Mandi, Bhamashah Mandi,City/District: Kota, Rajasthan, PIN Code: 324005</p>
                <p>GSTIN: 08AAXFT0244J1ZP </p>
                <p>Contact no. 9784478426 </p>

           
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