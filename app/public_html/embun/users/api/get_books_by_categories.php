<?php
include "../../admin/api/config.php";
header('Content-Type: application/json');

if (!isset($_GET['category_id'])) {
    echo json_encode(["error" => "Kategori tidak ditemukan."]);
    exit;
}

$category_id = intval($_GET['category_id']);

try {
    // Gunakan prepared statement agar aman dari SQL Injection
    $stmt = $pdo->prepare("SELECT id, title FROM books WHERE category_id = :category_id ORDER BY title ASC");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();

    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($books);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Gagal mengambil data buku: " . $e->getMessage()]);
}
?>

