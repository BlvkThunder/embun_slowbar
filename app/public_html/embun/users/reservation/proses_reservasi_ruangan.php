<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include "../../admin/api/config.php"; // koneksi ke $pdo

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$user_id     = trim($_POST['user_id'] ?? ''); 
$room        = trim($_POST['room'] ?? '');
$people      = (int)($_POST['people'] ?? 0);
$date        = trim($_POST['date'] ?? '');
$sessionsRaw = trim($_POST['time'] ?? '');  
$notes       = trim($_POST['notes'] ?? '');
$whatsapp    = trim($_POST['whatsapp'] ?? '');

// ===== Validasi dasar =====
if ($user_id === '' || $room === '' || $people <= 0 || $date === '' || $sessionsRaw === '') {
    echo json_encode(['error' => 'Harap isi semua kolom wajib dan pilih minimal 1 sesi.']);
    exit;
}

if ($whatsapp === '') {
    echo json_encode(['error' => 'Nomor WhatsApp harus diisi.']);
    exit;
}

// Normalisasi nomor WA
$wa = preg_replace('/\D+/', '', $whatsapp);
if (strpos($wa, '0') === 0) {
    $wa = '62' . substr($wa, 1);
}

// ===== Parsing format sesi =====
$parts = array_filter(array_map('trim', explode(',', $sessionsRaw)));
if (empty($parts)) {
    echo json_encode(['error' => 'Format sesi tidak valid.']);
    exit;
}
if (count($parts) > 3) {
    echo json_encode(['error' => 'Maksimal 3 sesi.']);
    exit;
}

$sessions = [];
foreach ($parts as $p) {
    if (!preg_match('/^([0-2]\d):([0-5]\d)-([0-2]\d):([0-5]\d)$/', $p, $m)) {
        echo json_encode(['error' => 'Format sesi tidak valid.']);
        exit;
    }
    $sh = (int)$m[1];
    $sm = (int)$m[2];
    $eh = (int)$m[3];
    $em = (int)$m[4];

    $start = $sh * 60 + $sm;
    $end   = $eh * 60 + $em;

    if ($end <= $start) {
        echo json_encode(['error' => 'Rentang sesi tidak valid.']);
        exit;
    }

    if (($end - $start) !== 120) {
        echo json_encode(['error' => 'Setiap sesi harus berdurasi 2 jam.']);
        exit;
    }

    $sessions[] = ['start' => $start, 'end' => $end];
}

// ===== Validasi batas jam =====
$minStart = min(array_column($sessions, 'start'));
$maxEnd   = max(array_column($sessions, 'end'));

if ($minStart < 10 * 60) {
    echo json_encode(['error' => 'Jam mulai minimal pukul 10:00.']);
    exit;
}
if ($maxEnd > 24 * 60) {
    echo json_encode(['error' => 'Jam berakhir maksimal pukul 24:00.']);
    exit;
}

// Hitung total durasi
$totalMinutes = 0;
foreach ($sessions as $s) {
    $totalMinutes += ($s['end'] - $s['start']);
}
$duration = (int)round($totalMinutes / 60); 

// Kalau nanti ada login admin/user bisa ambil dari session
// session_start();
$action_by = 'user'; // atau $_SESSION['username'] ?? 'user';

try {
    // Mulai transaksi: reservations + reservations_history harus konsisten
    $pdo->beginTransaction();

    // === 1) INSERT ke tabel utama reservations ===
    $stmt = $pdo->prepare("
        INSERT INTO reservations 
            (user_id, room, people, date, time, duration, notes, whatsapp, created_at)
        VALUES 
            (:user_id, :room, :people, :date, :time, :duration, :notes, :whatsapp, NOW())
    ");

    $stmt->bindParam(':user_id',  $user_id,       PDO::PARAM_STR);
    $stmt->bindParam(':room',     $room,          PDO::PARAM_STR);
    $stmt->bindParam(':people',   $people,        PDO::PARAM_INT);
    $stmt->bindParam(':date',     $date,          PDO::PARAM_STR);
    $stmt->bindParam(':time',     $sessionsRaw,   PDO::PARAM_STR);
    $stmt->bindParam(':duration', $duration,      PDO::PARAM_INT);
    $stmt->bindParam(':notes',    $notes,         PDO::PARAM_STR);
    $stmt->bindParam(':whatsapp', $wa,            PDO::PARAM_STR);

    $stmt->execute();

    // Ambil id reservasi yang baru
    $reservation_id = $pdo->lastInsertId();

    // === 2) INSERT ke tabel history ===
    $stmtHist = $pdo->prepare("
        INSERT INTO reservations_history
            (reservation_id, user_id, whatsapp, book_id, notes, room, people, date, time, duration, created_at, action, action_by)
        VALUES
            (:reservation_id, :user_id, :whatsapp, :book_id, :notes, :room, :people, :date, :time, :duration, NOW(), :action, :action_by)
    ");

    $action  = 'created';
    $book_id = null; // ini reservasi ruangan, bukan buku

    $stmtHist->execute([
        ':reservation_id' => $reservation_id,
        ':user_id'        => $user_id,
        ':whatsapp'       => $wa,
        ':book_id'        => $book_id,
        ':notes'          => $notes,
        ':room'           => $room,
        ':people'         => $people,
        ':date'           => $date,
        ':time'           => $sessionsRaw,
        ':duration'       => $duration,
        ':action'         => $action,
        ':action_by'      => $action_by,
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi ruangan berhasil disimpan dan history tercatat!'
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
