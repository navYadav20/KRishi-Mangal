<?php
session_start();
include '../../includes/db_connect.php';
include '../../includes/lang_helper.php';

// Check if the seller is already logged in and redirect if true
if (isset($_SESSION['seller_id'])) {
    header("Location: ../../seller/seller_dashboard.php");
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $whatsapp_number = trim($_POST['whatsapp_number']);

    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($whatsapp_number)) {
        $error = get_translation('all_fields_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = get_translation('invalid_email');
    } elseif (strlen($password) < 6) {
        $error = get_translation('password_too_short');
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $whatsapp_number)) {
        $error = get_translation('invalid_whatsapp');
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = get_translation('email_already_exists');
        } else {
            // Hash password and insert new seller
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO sellers (name, email, password, whatsapp_number, tier) VALUES (?, ?, ?, ?, 'free')");
            $stmt->execute([$name, $email, $hashed_password, $whatsapp_number]);

            // Log the seller in
            $stmt = $pdo->prepare("SELECT * FROM sellers WHERE email = ?");
            $stmt->execute([$email]);
            $seller = $stmt->fetch();
            $_SESSION['seller_id'] = $seller['id'];
            $_SESSION['seller_name'] = $seller['name'];
            $_SESSION['whatsapp_number'] = $seller['whatsapp_number'];
            header("Location: ../../seller/seller_dashboard.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('seller_register'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="bg-light min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h1 class="card-title text-center mb-4"><?php echo get_translation('Seller Register'); ?></h1>
                            <?php if (isset($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?php echo get_translation('name'); ?></label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo get_translation('email'); ?></label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?php echo get_translation('password'); ?></label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="whatsapp_number" class="form-label"><?php echo get_translation('whatsapp_number'); ?></label>
                                    <input type="tel" name="whatsapp_number" id="whatsapp_number" class="form-control" required placeholder="e.g., +1234567890">
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><?php echo get_translation('Register'); ?></button>
                            </form>
                            <p class="text-center mt-3"><?php echo get_translation('Already have account'); ?> <a href="login.php"><?php echo get_translation('login_here'); ?></a></p>
                            <p class="text-center"><a href="../choose_login.php" class="btn btn-link"><?php echo get_translation('back_to_login_options'); ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/theme-toggle.js"></script>
</body>
</html>