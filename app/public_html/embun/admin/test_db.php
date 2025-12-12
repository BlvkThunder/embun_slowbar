<?php
// test_db.php - Test database connection
require_once 'api/config.php';

header('Content-Type: application/json');

if (!isset($pdo) || $pdo === null) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection is null. Check config.php'
    ]);
    exit();
}

try {
    // Test database connection
    $stmt = $pdo->query("SELECT 1");
    $test = $stmt->fetch();
    
    // Test if tables exist
    $tables = ['menu_items', 'menu_categories', 'books', 'book_categories', 'boardgames'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            $tableStatus[$table] = ['exists' => true, 'count' => $result['count']];
        } catch (Exception $e) {
            $tableStatus[$table] = ['exists' => false, 'error' => $e->getMessage()];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful!',
        'tables' => $tableStatus
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
}
?>