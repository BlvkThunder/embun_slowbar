<?php
// get_admin_profile.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// hanya untuk admin yang sudah login
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '../api/config.php'; // pastikan path sesuai (config memberikan $pdo)

// ambil username dari session
$username = $_SESSION['username'];

// ringan: release session lock supaya request lain tidak menunggu
session_write_close();

try {
    $stmt = $pdo->prepare('SELECT id, username, avatar_path FROM users WHERE username = :u AND role = :r LIMIT 1');
    $stmt->execute([':u' => $username, ':r' => 'admin']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['ok' => false, 'error' => 'Admin not found']);
        exit;
    }

    // kembalikan URL relatif (sesuaikan bila perlu prefix base URL)
    $row['avatar_url'] = !empty($row['avatar_path']) ? $row['avatar_path'] : null;

    echo json_encode(['ok' => true, 'row' => $row]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
}
