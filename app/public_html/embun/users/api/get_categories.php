<?php
include "../../admin/api/config.php";

try {
    // Ambil semua kategori buku
    $sql = "SELECT id, name FROM book_categories";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Gagal mengambil kategori: " . $e->getMessage()]);
}
?>
