<?php
include '../../admin/api/config.php';

try {
    // Ambil parameter filter jika ada
    $category_filter = $_GET['category'] ?? 'all';
    
    // Query untuk categories
    $categories_stmt = $pdo->query("
        SELECT * FROM menu_categories 
    ");
    $categories = $categories_stmt->fetchAll();
    
    // Query untuk menu items dengan filter
    $menu_query = "
        SELECT mi.*, mc.name as category_name, mc.slug as category_slug 
        FROM menu_items mi 
        JOIN menu_categories mc ON mi.category_id = mc.id 
    ";
    
    if ($category_filter !== 'all') {
        $menu_query .= " WHERE mc.slug = :category";
        $menu_stmt = $pdo->prepare($menu_query);
        $menu_stmt->execute(['category' => $category_filter]);
    } else {
        $menu_stmt = $pdo->query($menu_query);
    }
    
    $menu_items = $menu_stmt->fetchAll();
    
    // Best sellers
    $best_seller_stmt = $pdo->query("
        SELECT * FROM menu_items 
        WHERE is_best_seller = TRUE 
    ");
    $best_sellers = $best_seller_stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'menu_items' => $menu_items,
        'best_sellers' => $best_sellers,
        'total_items' => count($menu_items)
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Gagal mengambil data menu',
        'debug' => $environment === 'development' ? $e->getMessage() : null
    ]);
}
?>