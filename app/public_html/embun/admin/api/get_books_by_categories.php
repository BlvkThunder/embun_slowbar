<?php
// get_books_by_categories.php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

$category_id = $_GET['category_id'] ?? '';
if ($category_id === '' || !ctype_digit($category_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Kategori tidak valid']); 
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.id,
            b.title,
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM loans l
                    WHERE l.book_id = b.id
                      AND l.return_date IS NULL
                ) THEN 1
                ELSE 0
            END AS is_borrowed
        FROM books b
        WHERE b.category_id = :category_id
        ORDER BY b.title ASC
    ");

    $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
    $stmt->execute();

    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($books, JSON_UNESCAPED_UNICODE);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Gagal mengambil data buku',
        'debug' => $e->getMessage()
    ]);
    exit;
}
