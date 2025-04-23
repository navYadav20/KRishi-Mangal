<?php
// Start session at the very top
session_start();
include '../../includes/db_connect.php';
include '../../includes/lang_helper.php';

// Handle login logic before any output
if (isset($_SESSION['seller_id'])) {
    header("Location: ../../seller/seller_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM sellers WHERE email = ?");
    $stmt->execute([$email]);
    $seller = $stmt->fetch();

    if ($seller && password_verify($password, $seller['password'])) {
        $_SESSION['seller_id'] = $seller['id'];
        $_SESSION['seller_name'] = $seller['name'];
        $_SESSION['whatsapp_number'] = $seller['whatsapp_number'];
        header("Location: ../../seller/seller_dashboard.php");
        exit();
    } else {
        $error = get_translation('invalid_credentials');
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('seller_login'); ?></title>
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
                            <h1 class="card-title text-center mb-4"><?php echo get_translation('seller_login'); ?></h1>
                            <?php if (isset($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo get_translation('email'); ?></label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?php echo get_translation('password'); ?></label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><?php echo get_translation('login_button'); ?></button>
                            </form>
                            <p class="text-center mt-3"><a href="register.php"><?php echo get_translation('register_new_account'); ?></a></p>
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