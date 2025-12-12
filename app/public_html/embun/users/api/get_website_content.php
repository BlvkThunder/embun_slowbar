<?php
include '../../admin/api/config.php';

header('Content-Type: application/json');

try {
    // Get all website content
    $stmt = $pdo->query("SELECT * FROM website_content");
    $contentItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array with content_key as key
    $content = [];
    foreach ($contentItems as $item) {
        $content[$item['content_key']] = $item['content_value'];
    }
    
    echo json_encode([
        'success' => true,
        'content' => $content
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Gagal mengambil data website: ' . $e->getMessage()
    ]);
}
?>