<?php
/**
 * Centralized Database Configuration
 * 
 * This file is the single source of truth for all database connections.
 * It reads credentials from environment variables (Docker-friendly) and
 * falls back to development defaults for local XAMPP.
 */

// Prevent direct access
if (!defined('EMBUN_APP')) {
    define('EMBUN_APP', true);
}

// =============================================================================
// ENVIRONMENT DETECTION
// =============================================================================

/**
 * Determine the environment:
 * - Docker: DB_HOST env is set (usually 'db')
 * - XAMPP: localhost with default credentials
 */
$isDocker = !empty(getenv('DB_HOST')) && getenv('DB_HOST') !== 'localhost';

// =============================================================================
// DATABASE CREDENTIALS
// =============================================================================

// Read from environment variables (Docker), or use XAMPP defaults
$db_host     = getenv('DB_HOST') ?: 'localhost';
$db_name     = getenv('DB_NAME') ?: 'embun_slowbar';
$db_user     = getenv('DB_USER') ?: 'root';
$db_password = getenv('DB_PASSWORD') ?: '';
$db_charset  = 'utf8mb4';

// =============================================================================
// ENVIRONMENT SETTINGS
// =============================================================================

$environment = $isDocker ? 'docker' : 'development';

if ($environment === 'development') {
    // Development: show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Docker/Production: log errors, don't display
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// =============================================================================
// PDO CONNECTION
// =============================================================================

$pdo = null;

try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_password, $options);
    
} catch (PDOException $e) {
    $error_message = ($environment === 'development')
        ? "Database connection failed: " . $e->getMessage()
        : "Database connection failed";
    
    error_log("PDO Connection Error: " . $e->getMessage());
    
    // Don't exit here, let the calling script handle the null $pdo
    $pdo = null;
}

// =============================================================================
// MYSQLI CONNECTION (for backward compatibility)
// =============================================================================

$mysqli = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
    $mysqli->set_charset($db_charset);
    
} catch (mysqli_sql_exception $e) {
    error_log("MySQLi Connection Error: " . $e->getMessage());
    $mysqli = null;
}

// =============================================================================
// MIDTRANS CONFIGURATION
// =============================================================================

$MIDTRANS_SERVER_KEY    = getenv('MIDTRANS_SERVER_KEY') ?: 'Mid-server-98kpatTHxQfwBNveH0ltKckl';
$MIDTRANS_CLIENT_KEY    = getenv('MIDTRANS_CLIENT_KEY') ?: 'Mid-client-QkOUo2wGLK0eGGQ3';
$MIDTRANS_IS_PRODUCTION = filter_var(getenv('MIDTRANS_IS_PRODUCTION') ?: 'false', FILTER_VALIDATE_BOOLEAN);

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get the PDO connection instance
 * @return PDO|null
 */
function getDbConnection(): ?PDO {
    global $pdo;
    return $pdo;
}

/**
 * Get the MySQLi connection instance
 * @return mysqli|null
 */
function getMysqliConnection(): ?mysqli {
    global $mysqli;
    return $mysqli;
}

/**
 * Check if database is connected
 * @return bool
 */
function isDbConnected(): bool {
    global $pdo;
    return $pdo !== null;
}

/**
 * Get current environment name
 * @return string
 */
function getEnvironment(): string {
    global $environment;
    return $environment;
}
?>
