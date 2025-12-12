<?php
/**
 * Users API Configuration
 * 
 * This file includes the centralized database configuration
 * and defines upload paths for the user-facing features.
 */

// Include centralized database configuration
require_once $_SERVER['DOCUMENT_ROOT'] . '/embun/config/database.php';

// =============================================================================
// UPLOAD PATHS CONFIGURATION  
// =============================================================================

// Define base paths for uploads
define('PROJECT_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/embun/');
define('UPLOAD_PATH', PROJECT_ROOT . 'uploads/');
define('UPLOAD_URL', '/embun/uploads/');

// Create upload directories if they don't exist
$uploadDirs = ['rooms', 'menu', 'books', 'boardgames', 'website'];
foreach ($uploadDirs as $dir) {
    $fullPath = UPLOAD_PATH . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
}

// =============================================================================
// BACKWARD COMPATIBILITY
// =============================================================================

// $pdo, $mysqli, $MIDTRANS_SERVER_KEY, $MIDTRANS_CLIENT_KEY, 
// and $MIDTRANS_IS_PRODUCTION are already defined in database.php

// Legacy variable aliases (if any code uses these)
$host = $db_host ?? 'localhost';
$dbname = $db_name ?? 'embun_slowbar';
$username = $db_user ?? 'root';
$password = $db_password ?? '';

// Ensure $pdo is available globally
global $pdo;
?>