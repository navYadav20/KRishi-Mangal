<?php
include_once __DIR__ . '/lang_helper.php';

// Determine the number of directory levels to go up to reach the 'ecommerce' base directory
$script_path = dirname($_SERVER['PHP_SELF']);
$base_dir = '/ecommerce';
$dir_depth = substr_count($script_path, '/') - substr_count($base_dir, '/');
$base_path = str_repeat('../', max(0, $dir_depth));
?>

<style>
    .navbar {
        background: linear-gradient(90deg, #4CAF50, #81C784); /* Green gradient for a fresh look */
        padding: 15px 0;
        border-bottom: 2px solid #388E3C; /* Darker green border */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }
    .navbar-brand {
        font-size: 1.5rem;
        font-weight: bold;
        color: #fff !important;
        transition: color 0.3s ease;
    }
    .navbar-brand:hover {
        color: #E8F5E9 !important; /* Light green on hover */
    }
    .nav-link {
        color: #fff !important;
        font-weight: 500;
        padding: 8px 15px !important;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .nav-link:hover {
        background-color: #388E3C; /* Darker green on hover */
        color: #E8F5E9 !important;
    }
    .nav-link.active {
        background-color: #2E7D32; /* Even darker green for active link */
        color: #fff !important;
    }
    .dropdown-menu {
        background-color: #4CAF50; /* Match the navbar gradient */
        border: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .dropdown-item {
        color: #fff !important;
        transition: background-color 0.3s ease;
    }
    .dropdown-item:hover {
        background-color: #388E3C; /* Darker green on hover */
        color: #E8F5E9 !important;
    }
    .theme-toggle-btn {
        cursor: pointer;
        color: #fff !important;
        font-size: 1.2rem;
    }
    .theme-toggle-btn:hover {
        color: #E8F5E9 !important;
    }
    @media (max-width: 991px) {
        .navbar-nav {
            padding: 10px 0;
        }
        .nav-link {
            padding: 10px 20px !important;
        }
        .dropdown-menu {
            background-color: #4CAF50;
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light shadow-sm fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="http://krishimangal.infinityfreeapp.com/index.php">
            <?php echo get_translation('home'); ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isset($_SESSION['seller_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'pricing.php') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/seller/pricing.php">
                            <?php echo get_translation('pricing'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'seller_dashboard.php') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/seller/seller_dashboard.php">
                            <?php echo get_translation('dashboard'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'add_crop.php') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/seller/add_crop.php">
                            <?php echo get_translation('add_crop'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://krishimangal.infinityfreeapp.com/auth/seller/logout.php">
                            <?php echo get_translation('logout'); ?>
                        </a>
                    </li>
                <?php elseif (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'user_dashboard.php') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/buyer/user_dashboard.php">
                            <?php echo get_translation('my_offers'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://krishimangal.infinityfreeapp.com/auth/buyer/user_logout.php">
                            <?php echo get_translation('logout'); ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Services Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo get_translation('services'); ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
                        <li><a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) === 'available_crops.php') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/available_crops.php">
                            <?php echo get_translation('available_crops'); ?>
                        </a></li>
                        <li><a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) === 'Crop Recommender') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/crop_recommender/">
                            <?php echo get_translation('Crop Recommender'); ?>
                        </a></li>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (!isset($_SESSION['seller_id']) && !isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo get_translation('login'); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="loginDropdown">
                            <li><a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) === 'login.php' && dirname($_SERVER['PHP_SELF']) === '/auth/seller') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/auth/seller/login.php">
                                <?php echo get_translation('login_as_seller'); ?>
                            </a></li>
                            <li><a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) === 'user_login.php' && dirname($_SERVER['PHP_SELF']) === '/auth/buyer') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/auth/buyer/user_login.php">
                                <?php echo get_translation('login_as_buyer'); ?>
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="registerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo get_translation('register'); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="registerDropdown">
                            <li><a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) === 'register.php' && dirname($_SERVER['PHP_SELF']) === '/auth/seller') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/auth/seller/register.php">
                                <?php echo get_translation('register_as_seller'); ?>
                            </a></li>
                            <li><a class="dropdown-item <?php echo (basename($_SERVER['PHP_SELF']) === 'user_register.php' && dirname($_SERVER['PHP_SELF']) === '/auth/buyer') ? 'active' : ''; ?>" href="http://krishimangal.infinityfreeapp.com/auth/buyer/user_register.php">
                                <?php echo get_translation('register_as_buyer'); ?>
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo get_translation('language'); ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                        <li><a class="dropdown-item" href="?lang=en"><?php echo get_translation('english'); ?></a></li>
                        <li><a class="dropdown-item" href="?lang=hi"><?php echo get_translation('hindi'); ?></a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <span class="nav-link theme-toggle-btn" id="themeToggle">
                        <i class="bi bi-sun-fill"></i>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Include Bootstrap Icons for the Theme Toggle -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">