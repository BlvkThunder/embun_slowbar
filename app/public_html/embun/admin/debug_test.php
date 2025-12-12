<?php
// debug_test.php
session_start();
require_once 'api/config.php';

// Test if we can get a specific menu item
$id = 1; // Change this to an ID that exists in your menu_items table
for($id = 1; $id <= 100; $id++) { // Loop through first 5 IDs for testing
try {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Database Test</h2>";
    echo "<pre>";
    print_r($menu);
    echo "</pre>";
    
    // Test API endpoint
    echo "<h2>API Test</h2>";
    $url = "admin_api.php?action=get_menu&id=" . $id;
    echo "Testing: $url<br>";
    
    $context = stream_context_create([
        'http' => [
            'header' => 'Cookie: ' . session_name() . '=' . session_id()
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
}
?>