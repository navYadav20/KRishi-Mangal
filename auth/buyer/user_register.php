<?php
session_start();
include '../../includes/db_connect.php';
include '../../includes/lang_helper.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['buyer_unique_id'])) {
    header("Location: ../../buyer/user_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = get_translation('all_fields_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = get_translation('invalid_email');
    } elseif (strlen($password) < 6) {
        $error = get_translation('password_too_short');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = get_translation('email_already_exists');
        } else {
            // Generate a unique ID for the buyer
            $unique_id = 'BUYER-' . strtoupper(substr(md5(uniqid($email, true)), 0, 8));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Check if unique_id column exists and adjust query
            try {
                $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'unique_id'");
                $stmt->execute();
                $column_exists = $stmt->fetch();

                if ($column_exists) {
                    $stmt = $pdo->prepare("INSERT INTO users (email, password, unique_id) VALUES (?, ?, ?)");
                    $stmt->execute([$email, $hashed_password, $unique_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                    $stmt->execute([$email, $hashed_password]);
                    // Update unique_id after insertion if column exists but was missed
                    $stmt = $pdo->prepare("UPDATE users SET unique_id = ? WHERE email = ?");
                    $stmt->execute([$unique_id, $email]);
                }

                // Log the buyer in
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['buyer_unique_id'] = $user['unique_id'];
                error_log("Registration successful, session: " . print_r($_SESSION, true));
                header("Location: ../../buyer/user_dashboard.php");
                exit();
            } catch (PDOException $e) {
                error_log("Database error during registration: " . $e->getMessage());
                $error = get_translation('database_error') . ": " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('buyer_register'); ?></title>
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
                            <h1 class="card-title text-center mb-4"><?php echo get_translation('Buyer register'); ?></h1>
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
                                <button type="submit" class="btn btn-primary w-100"><?php echo get_translation('Register'); ?></button>
                            </form>
                            <p class="text-center mt-3"><?php echo get_translation('Already have account'); ?> <a href="user_login.php"><?php echo get_translation('login_here'); ?></a></p>
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