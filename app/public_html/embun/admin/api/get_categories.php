<?php
// get_categories.php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// Disarankan: jangan tutup tag PHP untuk hindari spasi trailing

try {
    // Tabelmu: book_categories (bukan categories)
    $stmt = $pdo->query("SELECT id, name FROM book_categories ORDER BY name");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Gagal mengambil kategori',
        'debug' => $e->getMessage()
    ]);
    exit;
}
