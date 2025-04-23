<?php
/**
 * Authentication Helper v2.0
 * Enhanced security with CSRF protection and session management
 */

// Secure session initialization
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'name' => 'CropMarketSession',
            'cookie_lifetime' => 86400, // 1 day
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => true
        ]);
    }
}

// Generate and manage CSRF tokens
function manage_csrf_token() {
    init_secure_session();
    
    // Generate new token if none exists or is expired
    if (empty($_SESSION['csrf_token']) || time() > ($_SESSION['csrf_token_expiry'] ?? 0)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_expiry'] = time() + 3600; // 1 hour expiry
    }
    
    return $_SESSION['csrf_token'];
}

// Verify CSRF token with multiple checks
function verify_csrf_request() {
    init_secure_session();
    
    // Check if token exists in both places
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        error_log("CSRF Error: Missing token");
        return false;
    }
    
    // Verify token match and expiry
    $valid = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) && 
             time() <= ($_SESSION['csrf_token_expiry'] ?? 0);
    
    // Invalidate token after verification
    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_expiry']);
    
    return $valid;
}

// Seller authentication middleware
function authenticate_seller() {
    init_secure_session();
    
    if (empty($_SESSION['seller_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['auth_error'] = 'Please login to continue';
        header("Location: /auth/seller/login.php");
        exit();
    }
}

// Enhanced flash message system
function set_flash_message($type, $message) {
    init_secure_session();
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
    ];
}

// Display and clear flash messages
function display_flash_messages() {
    init_secure_session();
    
    if (!empty($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $message) {
            echo sprintf(
                '<div class="alert alert-%s alert-dismissible fade show" role="alert">%s
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>',
                htmlspecialchars($message['type']),
                htmlspecialchars($message['message'])
            );
        }
        unset($_SESSION['flash_messages']);
    }
}

// Secure redirect helper
function safe_redirect($url) {
    $allowed_hosts = ['yourdomain.com']; // Add your domains
    $parsed = parse_url($url);
    
    if (in_array($parsed['host'] ?? $_SERVER['HTTP_HOST'], $allowed_hosts, true)) {
        header("Location: $url");
        exit();
    }
    
    header("Location: /");
    exit();
}