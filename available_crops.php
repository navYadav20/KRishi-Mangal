<?php
session_start();
include 'includes/db_connect.php';
include 'includes/lang_helper.php';

// Define crop types (should match what's in your database)
$crop_types = [
    'Cereal Crops',
    'Pulses (Legumes)',
    'Oilseeds',
    'Cash Crops',
    'Fruits',
    'Vegetables',
    'Spices',
    'Tea and Coffee',
    'Flowers and Ornamental Plants'
];

// Initial fetch of crops for the first load
$query = "SELECT id, name, description, photo, base_price, quantity, crop_type, is_organic FROM crops";
$stmt = $pdo->prepare($query);
$stmt->execute();
$initial_crops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique categories for filter dropdown
$categories = $pdo->query("SELECT DISTINCT crop_type FROM crops")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('available_crops'); ?> - Crop Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .organic-badge {
            font-size: 0.8rem;
            vertical-align: middle;
            margin-left: 5px;
        }
        .crop-card {
            transition: transform 0.2s;
        }
        .crop-card:hover {
            transform: translateY(-5px);
        }
        .description-preview {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .full-description {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <h1 class="text-center mb-4"><?php echo get_translation('available_crops'); ?></h1>

        <div class="row">
            <!-- Sidebar for Filters -->
            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo get_translation('filter_crops'); ?></h5>
                        <form id="filter-form">
                            <!-- Crop Type Filter -->
                            <div class="mb-3">
                                <label for="crop_type" class="form-label"><?php echo get_translation('crop_type'); ?></label>
                                <select name="crop_type" id="crop_type" class="form-select">
                                    <option value=""><?php echo get_translation('all_types'); ?></option>
                                    <?php foreach ($crop_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>">
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Organic Filter -->
                            <div class="mb-3">
                                <label class="form-label"><?php echo get_translation('organic'); ?></label>
                                <div class="form-check">
                                    <input type="checkbox" name="organic" id="organic" value="1" class="form-check-input">
                                    <label for="organic" class="form-check-label"><?php echo get_translation('organic_only'); ?></label>
                                </div>
                            </div>

                            <!-- Price Range Filter -->
                            <div class="mb-3">
                                <label for="price_range" class="form-label"><?php echo get_translation('price_range'); ?></label>
                                <div class="d-flex align-items-center">
                                    <input type="number" name="price_min" id="price_min" class="form-control me-2" placeholder="Min" value="0" min="0" step="0.01">
                                    <input type="number" name="price_max" id="price_max" class="form-control" placeholder="Max" value="1000" min="0" step="0.01">
                                </div>
                            </div>

                            <!-- Availability Filter -->
                            <div class="mb-3">
                                <label class="form-label"><?php echo get_translation('availability'); ?></label>
                                <div class="form-check">
                                    <input type="checkbox" name="availability" id="availability" value="in_stock" class="form-check-input">
                                    <label for="availability" class="form-check-label"><?php echo get_translation('in_stock'); ?></label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Crops List -->
            <div class="col-md-9">
                <div id="loading-spinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                
                <div id="crops-container" class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($initial_crops as $crop): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm crop-card">
                                <img src="../uploads/crops/<?php echo htmlspecialchars($crop['photo']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($crop['name']); ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($crop['name']); ?>
                                        <?php if ($crop['is_organic'] == 1): ?>
                                            <span class="badge bg-success organic-badge">Organic</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="card-text description-preview"><?php echo htmlspecialchars($crop['description']); ?></p>
                                    <p class="card-text full-description"><?php echo htmlspecialchars($crop['description']); ?></p>
                                    <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($crop['crop_type']); ?></small></p>
                                    <p class="card-text"><strong><?php echo get_translation('base_price'); ?>:</strong> ₹<?php echo number_format($crop['base_price'], 2); ?></p>
                                    <p class="card-text"><strong><?php echo get_translation('available_quantity'); ?>:</strong> <?php echo $crop['quantity']; ?></p>
                                    <?php if ($crop['quantity'] > 0): ?>
                                        <button class="btn btn-outline-primary w-100 more-info-btn" data-crop-id="<?php echo $crop['id']; ?>">
                                            <?php echo get_translation('more_info'); ?>
                                        </button>
                                    <?php else: ?>
                                        <p class="text-danger fw-bold"><?php echo get_translation('out_of_stock'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($initial_crops)): ?>
                    <p class="text-center mt-4" id="no-crops-message"><?php echo get_translation('no_crops_available'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal for full crop details -->
    <div class="modal fade" id="cropDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cropModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="button" class="btn btn-primary" id="makeOfferBtn"><?php echo get_translation('make_offer'); ?></button>
                    <?php else: ?>
                        <a href="auth/choose_login.php" class="btn btn-primary"><?php echo get_translation('login_to_purchase'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Function to update crops based on filters
        function updateCrops() {
            const crop_type = $('#crop_type').val();
            const organic = $('#organic').is(':checked') ? 1 : '';
            const priceMin = $('#price_min').val();
            const priceMax = $('#price_max').val();
            const availability = $('#availability').is(':checked') ? 'in_stock' : '';

            $('#loading-spinner').show();
            $('#crops-container').hide();
            $('#no-crops-message').hide();

            $.ajax({
                url: 'get_filtered_crops.php',
                method: 'GET',
                data: {
                    crop_type: crop_type,
                    organic: organic,
                    price_min: priceMin,
                    price_max: priceMax,
                    availability: availability
                },
                dataType: 'json',
                success: function(crops) {
                    const cropsContainer = $('#crops-container');
                    const noCropsMessage = $('#no-crops-message');

                    cropsContainer.empty();

                    if (crops.length === 0) {
                        noCropsMessage.show();
                    } else {
                        crops.forEach(function(crop) {
                            let purchaseSection = '';
                            let organicBadge = crop.is_organic == 1 ? '<span class="badge bg-success organic-badge">Organic</span>' : '';
                            
                            if (crop.quantity > 0) {
                                purchaseSection = `
                                    <button class="btn btn-outline-primary w-100 more-info-btn" data-crop-id="${crop.id}">
                                        <?php echo get_translation('more_info'); ?>
                                    </button>
                                `;
                            } else {
                                purchaseSection = `<p class="text-danger fw-bold"><?php echo get_translation('out_of_stock'); ?></p>`;
                            }

                            const cropCard = `
                                <div class="col">
                                    <div class="card h-100 shadow-sm crop-card">
                                        <img src="../uploads/crops/${crop.photo}" class="card-img-top" alt="${crop.name}" style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title">${crop.name} ${organicBadge}</h5>
                                            <p class="card-text description-preview">${crop.description}</p>
                                            <p class="card-text full-description" style="display:none">${crop.description}</p>
                                            <p class="card-text"><small class="text-muted">${crop.crop_type}</small></p>
                                            <p class="card-text"><strong><?php echo get_translation('base_price'); ?>:</strong> ₹${parseFloat(crop.base_price).toFixed(2)}</p>
                                            <p class="card-text"><strong><?php echo get_translation('available_quantity'); ?>:</strong> ${crop.quantity}</p>
                                            ${purchaseSection}
                                        </div>
                                    </div>
                                </div>
                            `;
                            cropsContainer.append(cropCard);
                        });
                    }
                },
                complete: function() {
                    $('#loading-spinner').hide();
                    $('#crops-container').show();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching crops:', error);
                    $('#crops-container').html('<div class="alert alert-danger">Error loading crops. Please try again.</div>');
                }
            });
        }

        // Handle more info button clicks
        $(document).on('click', '.more-info-btn', function() {
            const cropId = $(this).data('crop-id');
            
            $.ajax({
                url: 'seller/get_crop_details.php',
                method: 'GET',
                data: { id: cropId },
                dataType: 'html',
                success: function(response) {
                    $('#cropModalBody').html(response);
                    $('#cropModalTitle').text($('#cropModalBody').find('h4').text() || 'Crop Details');
                    $('#makeOfferBtn').data('crop-id', cropId);
                    $('#cropDetailsModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching crop details:', error, xhr.responseText);
                    $('#cropModalBody').html('<div class="alert alert-danger">Error loading crop details. Please try again.</div>');
                    $('#cropDetailsModal').modal('show');
                }
            });
        });

        // Handle make offer button in modal
        $('#makeOfferBtn').on('click', function() {
            const cropId = $(this).data('crop-id');
            if ('<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>' === 'true') {
                // Show make offer form in modal if logged in
                const offerForm = `
                    <form method="post" action="seller/make_offer.php" class="mt-3">
                        <input type="hidden" name="crop_id" value="${cropId}">
                        <div class="mb-3">
                            <input type="number" name="quantity" class="form-control" min="1" max="${$('#cropModalBody').find('p:contains("Quantity:")').text().replace('Quantity: ', '').split(' ')[0]}" placeholder="<?php echo get_translation('quantity'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <input type="number" name="offered_price" class="form-control" step="0.01" min="0" placeholder="<?php echo get_translation('your_offer_price'); ?>" required>
                        </div>
                        <input type="hidden" name="buyer_unique_id" value="<?php echo htmlspecialchars($_SESSION['buyer_unique_id'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary w-100"><?php echo get_translation('make_offer'); ?></button>
                    </form>
                `;
                $('#cropModalBody').append(offerForm);
                $('#makeOfferBtn').hide(); // Hide the button after appending the form
            } else {
                window.location.href = 'auth/choose_login.php';
            }
        });

        // Trigger update on filter changes
        $('#crop_type, #organic, #price_min, #price_max, #availability').on('change', function() {
            updateCrops();
        });

        // Initial load
        updateCrops();
    });
    </script>

    <script src="assets/js/theme-toggle.js"></script>
</body>
</html>