<?php
// admin/delete_order.php
require_once '../api/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$csrf     = $_POST['csrf_token'] ?? '';
$order_id = $_POST['order_id'] ?? '';

if (!$order_id || !$csrf || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
  header('Location: orders.php?msg=' . urlencode('Token/Order tidak valid')); exit;
}

// hapus log dulu (FK/relasi lunak)
$stm1 = $pdo->prepare("DELETE FROM payment_logs WHERE order_id = ?");
$stm1->execute([$order_id]);

// hapus order
$stm2 = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
$stm2->execute([$order_id]);

header('Location: orders.php?msg=' . urlencode("Order $order_id telah dihapus."));
