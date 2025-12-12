<?php
/**
 * Admin API Configuration
 * 
 * This file includes the centralized database configuration
 * and provides backward compatibility for existing code.
 */

// Include centralized database configuration
require_once $_SERVER['DOCUMENT_ROOT'] . '/embun/config/database.php';

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
