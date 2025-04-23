<?php
session_start();
include '../includes/db_connect.php';
include '../includes/lang_helper.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('login'); ?> - Crop Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="bg-light min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h1 class="mb-4"><?php echo get_translation('login'); ?> to Crop Market</h1>
                            <a href="../auth/seller/login.php" class="btn btn-primary mb-2 w-100"><?php echo get_translation('login_as_seller'); ?></a>
                            <a href="../auth/buyer/user_login.php" class="btn btn-primary mb-2 w-100"><?php echo get_translation('login_as_buyer'); ?></a>
                            <p class="mb-3"><?php echo get_translation('register_new_account'); ?>? <a href="../auth/seller/register.php" class="text-decoration-none"><?php echo get_translation('register_as_seller'); ?></a> or <a href="../auth/buyer/user_register.php" class="text-decoration-none"><?php echo get_translation('register_as_buyer'); ?></a></p>
                            <a href="../index.php" class="btn btn-outline-secondary"><?php echo get_translation('home'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/theme-toggle.js"></script>
</body>
</html>