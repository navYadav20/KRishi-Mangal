<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/lang_helper.php';

// Redirect if not logged in as seller
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../auth/seller/login.php");
    exit();
}

// Get seller information
$seller_id = $_SESSION['seller_id'];
$stmt = $pdo->prepare("SELECT * FROM sellers WHERE id = ?");
$stmt->execute([$seller_id]);
$seller = $stmt->fetch();

// Get seller's tier and crop count
$tier = $seller['tier'] ?? 'free';
$stmt = $pdo->prepare("SELECT COUNT(*) FROM crops WHERE seller_id = ? AND status != 'sold'");
$stmt->execute([$seller_id]);
$active_crop_count = $stmt->fetchColumn();

// Calculate remaining crops for free tier
$max_crops = ($tier === 'free') ? 3 : 'Unlimited';
$remaining_crops = ($tier === 'free') ? max(0, $max_crops - $active_crop_count) : 'Unlimited';

// Get all crops with pending offers
$stmt = $pdo->prepare("
    SELECT c.*, o.id as offer_id, o.buyer_unique_id, o.offered_price, 
           o.quantity as offer_quantity, o.status as offer_status 
    FROM crops c 
    LEFT JOIN offers o ON c.id = o.crop_id AND o.status = 'pending'
    WHERE c.seller_id = ?
    ORDER BY c.status ASC, c.created_at DESC
");
$stmt->execute([$seller_id]);
$crops = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('seller_dashboard'); ?> - Crop Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* General Layout */
        .container.py-5 {
            padding-top: 5rem !important;
            padding-bottom: 5rem !important;
        }

        /* Dashboard Card Styles */
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            background-color: #fff;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        /* Stats Card Styles */
        .stats-card {
            border-left: 4px solid #0d6efd;
            padding: 0;
        }
        .stats-card .card-body {
            padding: 20px;
        }
        .stats-card .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 1.25rem;
            font-weight: 500;
        }
        .stats-card .card-title i {
            font-size: 1.5rem;
            color: #0d6efd;
        }
        .stats-card p {
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .stats-card p strong {
            font-weight: 600;
        }

        /* Crop Card Styles */
        .sold-badge {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .offer-badge {
            background-color: #ffc107;
            color: black;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .edit-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        .crop-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .crop-img {
            height: 180px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        /* Theme Toggle Styles */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        body.light-theme {
            background-color: #fff;
            color: #333;
        }
        body.dark-theme {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }
        .card.dark-theme {
            background-color: #2c2c2c;
            border-color: #444;
        }
        .card-body.dark-theme {
            color: #e0e0e0;
        }
        .text-muted.dark-theme {
            color: #a0a0a0 !important;
        }
        .bg-light.dark-theme {
            background-color: #333 !important;
        }
        .alert.dark-theme {
            background-color: #444;
            color: #e0e0e0;
        }
        .form-control.dark-theme {
            background-color: #333;
            color: #e0e0e0;
            border-color: #555;
        }
        .input-group-text.dark-theme {
            background-color: #444;
            color: #e0e0e0;
            border-color: #555;
        }

        /* Ensure Bootstrap Grid Works */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        .col-md-4 {
            flex: 0 0 auto;
            width: 33.333333%;
            padding-right: 15px;
            padding-left: 15px;
        }
        @media (max-width: 767px) {
            .col-md-4 {
                width: 100%;
            }
        }
    </style>
</head>
<body class="light-theme">
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <!-- Dashboard Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-speedometer2"></i> <?php echo get_translation('seller_dashboard'); ?>
            </h1>
            <div>
                <a href="../auth/seller/logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> <?php echo get_translation('logout'); ?>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card stats-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-person-badge"></i> <?php echo get_translation('account_info'); ?>
                        </h5>
                        <p class="mb-1"><strong><?php echo get_translation('name'); ?>:</strong> <?php echo htmlspecialchars($seller['name']); ?></p>
                        <p class="mb-1"><strong><?php echo get_translation('tier'); ?>:</strong> <?php echo ucfirst($tier); ?></p>
                        <p class="mb-1"><strong><?php echo get_translation('whatsapp'); ?>:</strong> <?php echo htmlspecialchars($seller['whatsapp_number'] ?? 'Not set'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card stats-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-basket"></i> <?php echo get_translation('crop_stats'); ?>
                        </h5>
                        <p class="mb-1"><strong><?php echo get_translation('active_crops'); ?>:</strong> <?php echo $active_crop_count; ?></p>
                        <p class="mb-1"><strong><?php echo get_translation('remaining_slots'); ?>:</strong> <?php echo $remaining_crops; ?></p>
                        <p class="mb-1"><strong><?php echo get_translation('total_listed'); ?>:</strong> <?php echo count($crops); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card stats-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-lightning-charge"></i> <?php echo get_translation('quick_actions'); ?>
                        </h5>
                        <div class="d-grid gap-2">
                            <a href="add_crop.php" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> <?php echo get_translation('add_crop'); ?>
                            </a>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#whatsappModal">
                                <i class="bi bi-whatsapp"></i> <?php echo get_translation('update_whatsapp'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Crops Listing -->
        <h2 class="mb-3">
            <i class="bi bi-list-ul"></i> <?php echo get_translation('your_crops'); ?>
        </h2>

        <?php if (empty($crops)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <?php echo get_translation('no_crops_listed'); ?>
                <a href="add_crop.php" class="alert-link"><?php echo get_translation('add_first_crop'); ?></a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($crops as $crop): ?>
                    <div class="col">
                        <div class="card dashboard-card h-100">
                            <?php if ($crop['photo']): ?>
                                <img src="../uploads/crops/<?php echo htmlspecialchars($crop['photo']); ?>" class="card-img-top crop-img" alt="<?php echo htmlspecialchars($crop['name']); ?>">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <!-- Crop Status Badges -->
                                <div class="d-flex justify-content-between mb-2">
                                    <?php if ($crop['status'] === 'sold'): ?>
                                        <span class="sold-badge">
                                            <i class="bi bi-check-circle"></i> <?php echo get_translation('sold'); ?>
                                        </span>
                                    <?php elseif ($crop['offer_id']): ?>
                                        <span class="offer-badge">
                                            <i class="bi bi-arrow-left-right"></i> <?php echo get_translation('offer_pending'); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($crop['is_organic']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-leaf"></i> <?php echo get_translation('organic'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h5 class="card-title"><?php echo htmlspecialchars($crop['name']); ?></h5>
                                <p class="card-text text-muted">
                                    <i class="bi bi-tag"></i> <?php echo htmlspecialchars($crop['crop_type']); ?>
                                </p>
                                
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <div class="border p-2 rounded text-center">
                                            <small class="text-muted"><?php echo get_translation('price'); ?></small>
                                            <div class="fw-bold">₹<?php echo number_format($crop['base_price'], 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border p-2 rounded text-center">
                                            <small class="text-muted"><?php echo get_translation('quantity'); ?></small>
                                            <div class="fw-bold"><?php echo $crop['quantity']; ?> <?php echo get_translation('units'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($crop['offer_id']): ?>
                                    <div class="offer-details mt-3 p-3 bg-light rounded">
                                        <h6><i class="bi bi-megaphone"></i> <?php echo get_translation('offer_details'); ?></h6>
                                        <p class="mb-1"><i class="bi bi-person"></i> <strong><?php echo get_translation('buyer'); ?>:</strong> <?php echo htmlspecialchars($crop['buyer_unique_id']); ?></p>
                                        <p class="mb-1"><i class="bi bi-currency-rupee"></i> <strong><?php echo get_translation('offered_price'); ?>:</strong> ₹<?php echo number_format($crop['offered_price'], 2); ?></p>
                                        <p class="mb-2"><i class="bi bi-box-seam"></i> <strong><?php echo get_translation('quantity'); ?>:</strong> <?php echo $crop['offer_quantity']; ?></p>
                                        <form method="post" action="../includes/process_offer.php" class="d-flex gap-2">
                                            <input type="hidden" name="offer_id" value="<?php echo $crop['offer_id']; ?>">
                                            <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                            <button type="submit" name="accept" class="btn btn-success btn-sm flex-grow-1">
                                                <i class="bi bi-check-lg"></i> <?php echo get_translation('accept'); ?>
                                            </button>
                                            <button type="submit" name="reject" class="btn btn-danger btn-sm flex-grow-1">
                                                <i class="bi bi-x-lg"></i> <?php echo get_translation('reject'); ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="crop-actions">
                                    <?php if ($crop['status'] !== 'sold'): ?>
                                        <button class="btn btn-outline-primary btn-sm edit-crop-btn" 
                                                data-crop-id="<?php echo $crop['id']; ?>">
                                            <i class="bi bi-pencil"></i> <?php echo get_translation('edit'); ?>
                                        </button>
                                        <form method="post" action="../seller/functions/mark_as_sold.php" class="d-inline">
                                            <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-check-circle"></i> <?php echo get_translation('mark_as_sold'); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="../seller/functions/delete_crops.php" class="d-inline">
                                        <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                onclick="return confirm('<?php echo get_translation('confirm_delete_crop'); ?>?')">
                                            <i class="bi bi-trash"></i> <?php echo get_translation('delete'); ?>
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="edit-form" id="edit-form-<?php echo $crop['id']; ?>">
                                    <form method="post" action="../seller/functions/update_crop.php">
                                        <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                        <div class="mb-2">
                                            <label class="form-label"><?php echo get_translation('price'); ?></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" name="price" class="form-control" 
                                                       value="<?php echo $crop['base_price']; ?>" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label"><?php echo get_translation('quantity'); ?></label>
                                            <input type="number" name="quantity" class="form-control form-control-sm" 
                                                   value="<?php echo $crop['quantity']; ?>" min="0" required>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                                <i class="bi bi-save"></i> <?php echo get_translation('save'); ?>
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm flex-grow-1 cancel-edit">
                                                <i class="bi bi-x"></i> <?php echo get_translation('cancel'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?php echo get_translation('listed'); ?>: 
                                    <?php echo date('M d, Y', strtotime($crop['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- WhatsApp Update Modal -->
    <div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="whatsappModalLabel">
                        <i class="bi bi-whatsapp"></i> <?php echo get_translation('update_whatsapp'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="../seller/functions/update_whatsapp.php ">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="whatsapp_number" class="form-label"><?php echo get_translation('whatsapp_number'); ?></label>
                            <input type="tel" name="whatsapp_number" id="whatsapp_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($seller['whatsapp_number'] ?? ''); ?>" 
                                   required placeholder="+1234567890">
                            <div class="form-text"><?php echo get_translation('whatsapp_help_text'); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> <?php echo get_translation('close'); ?>
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?php echo get_translation('save_changes'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle edit forms
            $('.edit-crop-btn').click(function() {
                const cropId = $(this).data('crop-id');
                $(`#edit-form-${cropId}`).show();
                $(this).hide();
            });
            
            $('.cancel-edit').click(function() {
                $(this).closest('.edit-form').hide();
                $(this).closest('.card-body').find('.edit-crop-btn').show();
            });

            // Theme Toggle Functionality
            const themeToggleBtn = $('#themeToggle');
            const body = $('body');
            const themeIcon = themeToggleBtn.find('i');

            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme') || 'light';
            body.removeClass('light-theme dark-theme').addClass(`${savedTheme}-theme`);
            themeIcon.removeClass('bi-sun-fill bi-moon-fill').addClass(savedTheme === 'light' ? 'bi-sun-fill' : 'bi-moon-fill');

            themeToggleBtn.click(function() {
                const currentTheme = body.hasClass('light-theme') ? 'light' : 'dark';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                body.removeClass('light-theme dark-theme').addClass(`${newTheme}-theme`);
                themeIcon.removeClass('bi-sun-fill bi-moon-fill').addClass(newTheme === 'light' ? 'bi-sun-fill' : 'bi-moon-fill');

                // Save the theme preference
                localStorage.setItem('theme', newTheme);

                // Apply dark theme classes to specific elements
                $('.card').toggleClass('dark-theme', newTheme === 'dark');
                $('.card-body').toggleClass('dark-theme', newTheme === 'dark');
                $('.text-muted').toggleClass('dark-theme', newTheme === 'dark');
                $('.bg-light').toggleClass('dark-theme', newTheme === 'dark');
                $('.alert').toggleClass('dark-theme', newTheme === 'dark');
                $('.form-control').toggleClass('dark-theme', newTheme === 'dark');
                $('.input-group-text').toggleClass('dark-theme', newTheme === 'dark');
            });

            // Show success/error messages from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                const msg = urlParams.get('success');
                let alertClass = 'alert-success';
                let alertMsg = '';
                
                switch(msg) {
                    case 'whatsapp_updated':
                        alertMsg = '<?php echo get_translation('whatsapp_update_success'); ?>';
                        break;
                    case 'crop_updated':
                        alertMsg = '<?php echo get_translation('crop_update_success'); ?>';
                        break;
                    case 'crop_sold':
                        alertMsg = '<?php echo get_translation('crop_marked_sold'); ?>';
                        break;
                    case 'crop_deleted':
                        alertMsg = '<?php echo get_translation('crop_deleted_success'); ?>';
                        break;
                    default:
                        alertMsg = '<?php echo get_translation('operation_success'); ?>';
                }
                
                showAlert(alertMsg, alertClass);
            }
            
            if (urlParams.has('error')) {
                const msg = urlParams.get('error');
                let alertMsg = '';
                
                switch(msg) {
                    case 'whatsapp_update_failed':
                        alertMsg = '<?php echo get_translation('whatsapp_update_failed'); ?>';
                        break;
                    case 'crop_update_failed':
                        alertMsg = '<?php echo get_translation('crop_update_failed'); ?>';
                        break;
                    case 'offer_process_failed':
                        alertMsg = '<?php echo get_translation('offer_process_failed'); ?>';
                        break;
                    case 'crop_sold_failed':
                        alertMsg = '<?php echo get_translation('crop_sold_failed'); ?>';
                        break;
                    case 'crop_already_sold':
                        alertMsg = '<?php echo get_translation('crop_already_sold'); ?>';
                        break;
                    case 'crop_delete_failed':
                        alertMsg = '<?php echo get_translation('crop_delete_failed'); ?>';
                        break;
                    default:
                        alertMsg = '<?php echo get_translation('operation_failed'); ?>';
                }
                
                showAlert(alertMsg, 'alert-danger');
            }
            
            function showAlert(message, alertClass) {
                const alertDiv = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    '<i class="bi ' + (alertClass === 'alert-success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') + '"></i> ' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');
                
                $('.container.py-5').prepend(alertDiv);
                
                // Remove success/error parameters from URL
                if (window.history.replaceState) {
                    const cleanUrl = window.location.href.split('?')[0];
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            }
        });
    </script>
</body>
</html>