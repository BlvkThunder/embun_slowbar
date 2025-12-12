<?php
require_once '../../admin/api/config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY id");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rooms]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}