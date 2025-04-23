<?php
session_start();
include 'includes/db_connect.php';
include 'includes/lang_helper.php';

// No redirects or header modifications are needed for a landing page, but logic is at the top just in case
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Krishi Mangal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/index.css?v=2"> <!-- Added version query to avoid cache -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4"><?php echo get_translation('Welcome to कृषि मंगल'); ?></h1>
            <p class="lead"><?php echo get_translation('खेती का नया युग-ज्ञान, सुझाव, बिक्री, और समाधान कृषि मंगल के साथ!'); ?></p>
            <div class="mt-4">
                <a href="available_crops.php" class="btn btn-primary btn-lg mx-2"><?php echo get_translation('exploreCrops'); ?></a>
                <?php if (!isset($_SESSION['userId'])): ?>
                    <a href="/auth/choose_login.php" class="btn btn-primary btn-lg mx-2"><?php echo get_translation('loginOrRegister'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="text-center"><?php echo get_translation('whyChooseUs'); ?></h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card feature-card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-cart3"></i>
                            <h5 class="card-title"><?php echo get_translation('buySellCrops'); ?></h5>
                            <p class="card-text">Easily buy fresh crops like wheat, rice, and corn directly from local farmers, or sell your harvest to buyers nationwide.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card feature-card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-cash"></i>
                            <h5 class="card-title"><?php echo get_translation('makeOffers'); ?></h5>
                            <p class="card-text">Negotiate the best prices by making offers on crops like soybeans and barley, ensuring fair deals for both parties.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card feature-card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-shield-lock"></i>
                            <h5 class="card-title"><?php echo get_translation('secureTransactions'); ?></h5>
                            <p class="card-text">Trade with confidence using our secure platform, protecting payments for crops like tomatoes and potatoes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container">
            <h2 class="text-center"><?php echo get_translation('Pricing Plans'); ?></h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card pricing-card h-100 text-center">
                        <div class="card-header">Basic Plan</div>
                        <div class="card-body">
                            <div class="price">₹0/month</div>
                            <ul>
                                <li><i class="bi bi-check-circle"></i> List up to 5 crops</li>
                                <li><i class="bi bi-check-circle"></i> Access to basic buyer network</li>
                                <li><i class="bi bi-check-circle"></i> Standard support</li>
                            </ul>
                            <a href="auth/seller/register.php" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card pricing-card h-100 text-center">
                        <div class="card-header">Pro Plan</div>
                        <div class="card-body">
                            <div class="price">₹49/month</div>
                            <ul>
                                <li><i class="bi bi-check-circle"></i> List up to 20 crops</li>
                                <li><i class="bi bi-check-circle"></i> Access to premium buyer network</li>
                                <li><i class="bi bi-check-circle"></i> Priority support</li>
                            </ul>
                            <a href="auth/seller/register.php" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card pricing-card h-100 text-center">
                        <div class="card-header">Enterprise Plan</div>
                        <div class="card-body">
                            <div class="price">₹129/month</div>
                            <ul>
                                <li><i class="bi bi-check-circle"></i> Unlimited crop listings</li>
                                <li><i class="bi bi-check-circle"></i> Access to global buyer network</li>
                                <li><i class="bi bi-check-circle"></i> Dedicated support</li>
                            </ul>
                            <a href="auth/seller/register.php" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="text-center"><?php echo get_translation('whatOurUsersSay'); ?></h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <p class="quote">"Krishi Mangal market ne meri fasal ko ache daam par bechne mein madad ki. Ab main apne gao se hi buyers tak pahunch sakta hoon!"</p>
                            <p class="author">Ramesh Patel</p>
                            <p class="role">Farmer, Gujarat</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <p class="quote">"Maine Krishi Mangal ke through apni sabziyaan bechi aur payment bhi turant mil gaya. Yeh platform kisanon ke liye sach mein dost hai!"</p>
                            <p class="author">Sunita Devi</p>
                            <p class="role">Farmer, Uttar Pradesh</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <p class="quote">"Krishi Mangal ke saath maine apne dhaan ke liye ek accha buyer dhoondha. Ab mujhe mandi jaane ki zarurat nahi padti!"</p>
                            <p class="author">Anil Kumar</p>
                            <p class="role">Farmer, Rajasthan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container">
            <h2><?php echo get_translation('joinOurMarketplace'); ?></h2>
            <p class="lead"><?php echo get_translation('joinOurMarketplaceDesc'); ?></p>
            <div class="mt-4">
                <a href="auth/seller/register.php" class="btn btn-primary btn-lg mx-2"><?php echo get_translation('registerAsSeller'); ?></a>
                <a href="auth/buyer/user_login.php" class="btn btn-primary btn-lg mx-2"><?php echo get_translation('registerAsBuyer'); ?></a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class=" py-4">
        <div class="container text-center">
            <p>© <?php echo date('Y'); ?> Krishi Mangal. <?php echo get_translation('allRightsReserved'); ?></p>
            <div class="mt-2">
                <a href="about.php" class="mx-2"><?php echo get_translation('aboutUs'); ?></a>
                <a href="contact.php" class="mx-2"><?php echo get_translation('contactUs'); ?></a>
                <a href="terms.php" class="mx-2"><?php echo get_translation('termsOfService'); ?></a>
            </div>
        </div>
    </footer>

    <script src="assets/js/theme-Toggle.js"></script>
</body>
</html>