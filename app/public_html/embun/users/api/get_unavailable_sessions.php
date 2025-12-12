<?php
// ../api/get_unavailable_sessions.php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$room_id = $_GET['room_id'] ?? '';
$date    = $_GET['date']    ?? '';

if ($room_id === '' || $date === '') {
    echo json_encode(['success' => false, 'error' => 'room_id dan date wajib diisi']);
    exit;
}

try {
    // asumsi: kolom `room` & `date` di tabel reservations
    // kolom `time` berisi string sesi, contoh: "10:00-12:00" atau "10:00-12:00, 12:00-14:00"
    $sql = "SELECT time FROM reservations WHERE room = :room AND date = :date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':room' => $room_id,
        ':date' => $date
    ]);

    $taken = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($row['time'])) continue;
        $t = trim((string)$row['time']);
        if ($t === '') continue;

        // dukung multi sesi yang disimpan comma separated
        $parts = explode(',', $t);
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $taken[$p] = true;
            }
        }
    }

    echo json_encode([
        'success'  => true,
        'sessions' => array_keys($taken)   // misal: ["10:00-12:00","12:00-14:00"]
    ]);

} catch (PDOException $e) {
    error_log('DB error get_unavailable_sessions: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
