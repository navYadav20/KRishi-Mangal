    <?php
    session_start();
    include '../includes/db_connect.php';
    include '../includes/lang_helper.php';

    // Initialize user tier variable
    $userTier = 'free';
    $userId = $_SESSION['seller_id'] ?? null;
    echo($userId);

    // Check user's tier if logged in
    if ($userId) {
        $stmt = $pdo->prepare("SELECT tier FROM sellers WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch();
        
        if ($userData) {
            $userTier = $userData['tier'];
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo get_translation('pricing_plans'); ?> - Crop Market</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
        <link rel="stylesheet" href="../assets/css/style.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include '../includes/navbar.php'; ?>

        <div class="bg-light min-vh-100 py-5">
            <div class="container">
                <h1 class="text-center mb-4"><?php echo get_translation('pricing_plans'); ?></h1>
                <p class="text-center mb-5"><?php echo get_translation('pricing_hero_text'); ?></p>
                <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                    <!-- Basic Plan (Free) -->
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center">
                                <h2 class="card-title"><?php echo get_translation('basic_plan'); ?></h2>
                                <p class="card-text display-6 fw-bold text-primary mb-4">$0/month</p>
                                <ul class="list-unstyled mb-4">
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('list_up_to_5_crops'); ?></li>
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('access_to_basic_buyer_network'); ?></li>
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('standard_support'); ?></li>
                                </ul>
                                <?php if ($userTier === 'free'): ?>
                                    <p class="text-muted mb-4"><?php echo get_translation('current_plan'); ?></p>
                                    <button class="btn btn-outline-primary disabled w-100" disabled><?php echo get_translation('current_plan'); ?></button>
                                <?php else: ?>
                                    <form method="post" action="change_tier.php">
                                        <input type="hidden" name="new_tier" value="free">
                                        <button type="submit" class="btn btn-outline-primary w-100"><?php echo get_translation('downgrade'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paid Plan -->
                    <div class="col">
                        <div class="card h-100 shadow-lg border-0 <?php echo $userTier === 'paid' ? 'bg-success' : 'bg-primary'; ?> text-white">
                            <div class="card-body text-center">
                                <h2 class="card-title"><?php echo get_translation('paid_plan'); ?></h2>
                                <p class="card-text display-6 fw-bold mb-4">$10/month</p>
                                <ul class="list-unstyled mb-4">
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('list_up_to_20_crops'); ?></li>
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('access_to_premium_buyer_network'); ?></li>
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('priority_support'); ?></li>
                                </ul>
                                <?php if ($userTier === 'paid'): ?>
                                    <p class="text-light mb-4"><?php echo get_translation('current_plan'); ?></p>
                                    <button class="btn btn-light disabled w-100" disabled><?php echo get_translation('current_plan'); ?></button>
                                <?php else: ?>
                                    <form method="post" action="change_tier.php">
                                        <input type="hidden" name="new_tier" value="paid">
                                        <button type="submit" class="btn btn-light w-100"><?php echo get_translation('upgrade'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enterprise Plan (Hidden if only two tiers) -->
                    <?php /* Hide the Enterprise plan since we only have two tiers
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center">
                                <h2 class="card-title"><?php echo get_translation('enterprise_plan'); ?></h2>
                                <p class="card-text display-6 fw-bold text-primary mb-4">$25/month</p>
                                <ul class="list-unstyled mb-4">
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('unlimited_crop_listings'); ?></li>
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('access_to_global_buyer_network'); ?></li>
                                    <li class="mb-2"><span class="text-success">✔</span> <?php echo get_translation('dedicated_support'); ?></li>
                                </ul>
                                <?php if ($userTier === 'enterprise'): ?>
                                    <p class="text-muted mb-4"><?php echo get_translation('current_plan'); ?></p>
                                    <button class="btn btn-outline-primary disabled w-100" disabled><?php echo get_translation('current_plan'); ?></button>
                                <?php else: ?>
                                    <form method="post" action="upgrade.php">
                                        <input type="hidden" name="tier" value="enterprise">
                                        <button type="submit" class="btn btn-outline-primary w-100"><?php echo get_translation('upgrade'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    */ ?>
                </div>
            </div>
        </div>

        <script src="../assets/js/theme-toggle.js"></script>
    </body>
    </html>