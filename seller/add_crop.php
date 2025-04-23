
<?php
session_start();
include '../includes/db_connect.php'; // Database connection (assumes PDO)
include '../includes/lang_helper.php'; // Language helper for translations

// Check if seller is on free tier and has reached their limit
$max_reached = false;
if (isset($_SESSION['seller_id'])) {
    $seller_id = $_SESSION['seller_id'];
    
    // Get seller's tier
    $stmt = $pdo->prepare("SELECT tier FROM sellers WHERE id = ?");
    $stmt->execute([$seller_id]);
    $seller_tier = $stmt->fetchColumn();
    
    // If free tier, check crop count
    if ($seller_tier === 'free') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM crops WHERE seller_id = ?");
        $stmt->execute([$seller_id]);
        $crop_count = $stmt->fetchColumn();
        
        if ($crop_count >= 3) {
            $max_reached = true;
        }
    }
}

// Define crop types with additional details
$crop_types = [
    'Cereal Crops' => ['icon' => '🌾', 'examples' => 'Wheat, Rice, Corn'],
    'Pulses (Legumes)' => ['icon' => '🫘', 'examples' => ' Beans, Lentils, Peas'],
    'Oilseeds' => ['icon' => '🫒', 'examples' => 'Sunflower, Soybean, Canola'],
    'Cash Crops' => ['icon' => '💰', 'examples' => 'Cotton, Tobacco, Sugarcane'],
    'Fruits' => ['icon' => '🍎', 'examples' => 'Apples, Oranges, Bananas'],
    'Vegetables' => ['icon' => '🥦', 'examples' => 'Broccoli, Carrots, Tomatoes'],
    'Spices' => ['icon' => '🌶️', 'examples' => 'Pepper, Cinnamon, Turmeric'],
    'Tea and Coffee' => ['icon' => '☕', 'examples' => 'Green Tea, Arabica Coffee'],
    'Flowers and Ornamental Plants' => ['icon' => '🌸', 'examples' => 'Roses, Tulips, Orchids']
];

// Define allowed quantity units
$quantity_units = [
    'KG' => 'Kilogram',
    'Quintal' => 'Quintal',
    'Ton' => 'Metric Ton',
    'Litre' => 'Litre',
    'Piece' => 'Piece',
    'Dozen' => 'Dozen',
    'Bundle' => 'Bundle'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$max_reached) {
    // First check if free tier seller has reached limit (in case they bypass client-side)
    if (isset($_SESSION['seller_id'])) {
        $seller_id = $_SESSION['seller_id'];
        $stmt = $pdo->prepare("SELECT tier FROM sellers WHERE id = ?");
        $stmt->execute([$seller_id]);
        $seller_tier = $stmt->fetchColumn();
        
        if ($seller_tier === 'free') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM crops WHERE seller_id = ?");
            $stmt->execute([$seller_id]);
            $crop_count = $stmt->fetchColumn();
            
            if ($crop_count >= 3) {
                header("Location: add_crop.php?error=limit_reached");
                exit();
            }
        }
    }

    // Get form data
    $name = trim($_POST['name'] ?? '');
    $crop_type = trim($_POST['crop_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price = floatval($_POST['base_price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $quantity_unit = trim($_POST['quantity_unit'] ?? '');
    $is_organic = isset($_POST['is_organic']) ? 1 : 0;
    $harvest_date = !empty($_POST['harvest_date']) ? $_POST['harvest_date'] : null;
    $location = trim($_POST['location'] ?? '');
    $certification = trim($_POST['certification'] ?? '');
    $seller_notes = trim($_POST['seller_notes'] ?? '');
    $seller_id = $_SESSION['seller_id'] ?? null;

    // Validate required fields
    if (empty($name) || empty($crop_type) || empty($description) || $base_price <= 0 || $quantity < 0 || empty($quantity_unit) || !array_key_exists($quantity_unit, $quantity_units) || !$seller_id) {
        $error = "Please fill in all required fields with valid values.";
    } else {
        // Handle file upload
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_name = basename($_FILES['photo']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            // Check file extension and size (max 5MB)
            if (in_array($file_ext, $allowed_ext) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                $new_file_name = uniqid() . '.' . $file_ext;
                $upload_dir = '../uploads/crops/'; // Relative path
                $photo = $new_file_name; // Store only the filename in the database

                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true); // Create directory with full permissions
                }

                $photo_path = $upload_dir . $new_file_name;
                if (!move_uploaded_file($file_tmp, $photo_path)) {
                    $error = "Failed to upload the photo.";
                }
            } else {
                $error = "Invalid file type or size. Only JPG, PNG, GIF (max 5MB) allowed.";
            }
        } else {
            $error = "Please upload a photo.";
        }

        // If no errors, insert into database
        if (!isset($error)) {
            try {
                $sql = "INSERT INTO crops (seller_id, name, crop_type, description, photo, base_price, quantity, quantity_unit, is_organic, harvest_date, location, certification, seller_notes) 
                        VALUES (:seller_id, :name, :crop_type, :description, :photo, :base_price, :quantity, :quantity_unit, :is_organic, :harvest_date, :location, :certification, :seller_notes)";
                $stmt = $pdo->prepare($sql); // $pdo from db_connect.php
                $stmt->execute([
                    ':seller_id' => $seller_id,
                    ':name' => $name,
                    ':crop_type' => $crop_type,
                    ':description' => $description,
                    ':photo' => $photo, // Store only the filename
                    ':base_price' => $base_price,
                    ':quantity' => $quantity,
                    ':quantity_unit' => $quantity_unit,
                    ':is_organic' => $is_organic,
                    ':harvest_date' => $harvest_date,
                    ':location' => $location,
                    ':certification' => $certification,
                    ':seller_notes' => $seller_notes
                ]);

                // Redirect with success message
                header("Location: add_crop.php?success=1");
                exit();
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
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
    <title><?php echo get_translation('add_new_crop'); ?> - Crop Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* General Layout */
        .container.py-5 {
            padding-top: 5rem !important;
            padding-bottom: 5rem !important;
        }

        /* Light Theme Styles */
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .crop-type-option {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .crop-type-option:hover {
            background-color: #e9ecef;
        }
        .crop-type-option .icon {
            font-size: 1.5rem;
            margin-right: 12px;
            width: 30px;
            text-align: center;
        }
        .crop-type-option .details {
            flex-grow: 1;
        }
        .crop-type-option .examples {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .file-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload-container:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .file-upload-container i {
            font-size: 2rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
        }
        .organic-badge {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        .upgrade-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid rgba(0,0,0,0.1);
        }

        /* Dark Theme Styles */
        body.dark-theme {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }
        .dark-theme .form-section {
            background-color: #2c2c2c;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .dark-theme .form-section h3 {
            color: #e0e0e0;
            border-bottom: 2px solid #444;
        }
        .dark-theme .crop-type-option:hover {
            background-color: #3c3c3c;
        }
        .dark-theme .crop-type-option .examples {
            color: #a0a0a0;
        }
        .dark-theme .file-upload-container {
            border-color: #555;
            background-color: #2c2c2c;
        }
        .dark-theme .file-upload-container:hover {
            border-color: #0d6efd;
            background-color: #3c3c3c;
        }
        .dark-theme .file-upload-container i {
            color: #a0a0a0;
        }
        .dark-theme .form-control,
        .dark-theme .form-select,
        .dark-theme textarea {
            background-color: #333;
            color: #e0e0e0;
            border-color: #555;
        }
        .dark-theme .form-control:focus,
        .dark-theme .form-select:focus,
        .dark-theme textarea:focus {
            background-color: #444;
            color: #e0e0e0;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25);
        }
        .dark-theme .input-group-text {
            background-color: #444;
            color: #e0e0e0;
            border-color: #555;
        }
        .dark-theme .card {
            background-color: #2c2c2c;
            border-color: #444;
        }
        .dark-theme .card-body {
            color: #e0e0e0;
        }
        .dark-theme .alert {
            background-color: #444;
            color: #e0e0e0;
        }
        .dark-theme .alert a {
            color: #0d6efd;
        }
        .dark-theme .btn-outline-secondary {
            color: #e0e0e0;
            border-color: #555;
        }
        .dark-theme .btn-outline-secondary:hover {
            background-color: #555;
            color: #e0e0e0;
        }
        .dark-theme .upgrade-card {
            background: linear-gradient(135deg, #2c2c2c 0%, #444 100%);
            border-color: #555;
        }
        .dark-theme .text-muted {
            color: #a0a0a0 !important;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-plus-circle"></i> <?php echo get_translation('add_new_crop'); ?>
                    </h1>
                    <div>
                        <a href="seller_dashboard.php" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> <?php echo get_translation('back_to_dashboard'); ?>
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'limit_reached'): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        You've reached your limit of 3 crop listings on the free plan. 
                        <a href="../seller/pricing.php" class="alert-link">Upgrade to paid</a> to list more crops.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> <?php echo get_translation('crop_added_successfully'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($max_reached): ?>
                    <div class="card border-0 shadow-sm upgrade-card">
                        <div class="card-body p-5 text-center">
                            <i class="bi bi-emoji-frown display-4 text-warning mb-4"></i>
                            <h3 class="mb-3">You've reached your free tier limit</h3>
                            <p class="lead mb-4">The free plan allows only 3 crop listings. Upgrade to our paid plan to list unlimited crops.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="../seller/pricing.php" class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-arrow-up-circle"></i> Upgrade Plan
                                </a>
                                <a href="seller_dashboard.php" class="btn btn-outline-secondary btn-lg px-4">
                                    <i class="bi bi-grid"></i> View Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form method="post" enctype="multipart/form-data" id="cropForm">
                                <!-- Basic Information Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-info-circle"></i> Basic Information</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label"><?php echo get_translation('crop_name'); ?></label>
                                            <input type="text" name="name" id="name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="crop_type" class="form-label"><?php echo get_translation('crop_type'); ?></label>
                                            <select name="crop_type" id="crop_type" class="form-select" required>
                                                <option value=""><?php echo get_translation('select_crop_type'); ?></option>
                                                <?php foreach ($crop_types as $type => $details): ?>
                                                    <option value="<?php echo htmlspecialchars($type); ?>">
                                                        <?php echo htmlspecialchars($type); ?> (<?php echo $details['examples']; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="description" class="form-label"><?php echo get_translation('description'); ?></label>
                                            <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                                            <div class="form-text">Describe your crop in detail (variety, quality, etc.)</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing & Quantity Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-tags"></i> Pricing & Quantity</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="base_price" class="form-label"><?php echo get_translation('base_price_label'); ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" name="base_price" id="base_price" class="form-control" step="0.01" min="0" required>
                                                <span class="input-group-text">per unit</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="quantity" class="form-label"><?php echo get_translation('quantity_label'); ?></label>
                                            <div class="input-group">
                                                <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                                                <select name="quantity_unit" id="quantity_unit" class="form-select" required>
                                                    <option value=""><?php echo get_translation('select_unit'); ?></option>
                                                    <?php foreach ($quantity_units as $unit => $label): ?>
                                                        <option value="<?php echo htmlspecialchars($unit); ?>">
                                                            <?php echo htmlspecialchars($label); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Details Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-card-checklist"></i> Additional Details</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_organic" id="is_organic" value="1">
                                                <label class="form-check-label" for="is_organic">
                                                    <?php echo get_translation('organic_crop'); ?>
                                                    <span class="organic-badge">Organic</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="harvest_date" class="form-label">Harvest Date</label>
                                            <input type="date" name="harvest_date" id="harvest_date" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" name="location" id="location" class="form-control" placeholder="Where is this crop available from?">
                                        </div>
                                        <div class="col-12">
                                            <label for="certification" class="form-label">Certification</label>
                                            <input type="text" name="certification" id="certification" class="form-control" placeholder="Any organic or quality certifications">
                                        </div>
                                        <div class="col-12">
                                            <label for="seller_notes" class="form-label">Seller Notes</label>
                                            <textarea name="seller_notes" id="seller_notes" class="form-control" rows="3" placeholder="Any special notes for buyers"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Upload Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-camera"></i> Crop Photo</h3>
                                    <div class="file-upload-container" onclick="document.getElementById('photo').click()">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <h5>Click to upload photo</h5>
                                        <p class="text-muted">JPG, PNG or GIF (Max. 5MB)</p>
                                        <input type="file" name="photo" id="photo" class="d-none" accept="image/*" required>
                                        <img id="previewImage" class="preview-image" alt="Preview">
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg py-3">
                                        <i class="bi bi-check-circle"></i> <?php echo get_translation('add_crop_button'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('previewImage');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        document.getElementById('cropForm').addEventListener('submit', function(e) {
            const price = parseFloat(document.getElementById('base_price').value);
            const quantity = parseInt(document.getElementById('quantity').value);
            const quantityUnit = document.getElementById('quantity_unit').value;
            
            if (price <= 0) {
                alert('Please enter a valid price greater than 0');
                e.preventDefault();
            }
            
            if (quantity < 0) {
                alert('Quantity cannot be negative');
                e.preventDefault();
            }
            
            if (!quantityUnit) {
                alert('Please select a quantity unit');
                e.preventDefault();
            }
        });

        // Dynamic organic badge
        document.getElementById('is_organic').addEventListener('change', function() {
            const badge = document.querySelector('.organic-badge');
            if (this.checked) {
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    </script>
    <script src="../assets/js/theme-toggle.js"></script>
</body>
</html>
