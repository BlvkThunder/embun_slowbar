<?php
include '../../admin/api/config.php';

header('Content-Type: application/json');

try {
    // Ambil semua kategori buku
    $categories_stmt = $pdo->query("SELECT * FROM book_categories");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil semua buku
    $books_stmt = $pdo->query("
        SELECT b.*, bc.name as category_name, bc.slug as category_slug 
        FROM books b 
        JOIN book_categories bc ON b.category_id = bc.id 
        ORDER BY b.title
    ");
    $books = $books_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'categories' => $categories,
        'books' => $books
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data buku: ' . $e->getMessage()]);
}
?>