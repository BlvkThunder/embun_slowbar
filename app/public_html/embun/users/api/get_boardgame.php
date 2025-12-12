<?php
include '../../admin/api/config.php';

header('Content-Type: application/json');

try {
    // Ambil semua boardgames - FIXED: Remove display_order
    $stmt = $pdo->query("SELECT * FROM boardgames");
    $boardgames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($boardgames);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data boardgame: ' . $e->getMessage()]);
}
?>