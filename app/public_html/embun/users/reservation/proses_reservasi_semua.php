<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once "../../admin/api/config.php"; // samakan dengan file lain

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid request method'
    ]);
    exit;
}

// Ambil data dari POST
$user_id     = trim($_POST['user_id']  ?? '');
$room        = trim($_POST['room']     ?? '');
$people      = (int)($_POST['people']  ?? 0);
$date        = trim($_POST['date']     ?? '');
$notes       = trim($_POST['notes']    ?? '');
$book_id     = !empty($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
$whatsapp    = trim($_POST['whatsapp'] ?? '');
$sessionsRaw = trim($_POST['time']     ?? '');   // string: "10:00-12:00, 12:00-14:00"

// === Validasi dasar ===
if ($user_id === '') {
    echo json_encode([
        'success' => false,
        'error'   => 'User tidak valid. Silakan login ulang.'
    ]);
    exit;
}

if ($room === '' || $people <= 0 || $date === '' || $sessionsRaw === '') {
    echo json_encode([
        'success' => false,
        'error'   => 'Data reservasi ruangan belum lengkap atau sesi belum dipilih.'
    ]);
    exit;
}

if ($book_id <= 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'Buku belum dipilih.'
    ]);
    exit;
}

if ($whatsapp === '') {
    echo json_encode([
        'success' => false,
        'error'   => 'Nomor WhatsApp harus diisi.'
    ]);
    exit;
}

// Normalisasi nomor WA (opsional, biar konsisten)
$wa = preg_replace('/\D+/', '', $whatsapp); // hanya angka
if ($wa !== '' && strpos($wa, '0') === 0) {
    // 08xxxx -> 628xxxx
    $wa = '62' . substr($wa, 1);
}

// ===== Parsing format sesi dari string "10:00-12:00, 12:00-14:00" =====
$parts = array_filter(array_map('trim', explode(',', $sessionsRaw)));
if (empty($parts)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Format sesi tidak valid.'
    ]);
    exit;
}

// Maksimal 3 sesi
if (count($parts) > 3) {
    echo json_encode([
        'success' => false,
        'error'   => 'Maksimal 3 sesi per reservasi.'
    ]);
    exit;
}

$sessions = [];
foreach ($parts as $p) {
    // Format harus "HH:MM-HH:MM"
    if (!preg_match('/^([0-2]\d):([0-5]\d)-([0-2]\d):([0-5]\d)$/', $p, $m)) {
        echo json_encode([
            'success' => false,
            'error'   => 'Format sesi tidak valid.'
        ]);
        exit;
    }
    $sh = (int)$m[1];
    $sm = (int)$m[2];
    $eh = (int)$m[3];
    $em = (int)$m[4];

    $start = $sh * 60 + $sm;
    $end   = $eh * 60 + $em;

    if ($end <= $start) {
        echo json_encode([
            'success' => false,
            'error'   => 'Rentang sesi tidak valid.'
        ]);
        exit;
    }

    // Tiap sesi harus 2 jam = 120 menit
    if (($end - $start) !== 120) {
        echo json_encode([
            'success' => false,
            'error'   => 'Setiap sesi harus berdurasi 2 jam.'
        ]);
        exit;
    }

    $sessions[] = ['start' => $start, 'end' => $end];
}

// ===== Validasi batas jam (10:00 - 24:00) =====
$minStart = min(array_column($sessions, 'start'));
$maxEnd   = max(array_column($sessions, 'end'));

if ($minStart < 10 * 60) {
    echo json_encode([
        'success' => false,
        'error'   => 'Jam mulai minimal pukul 10:00.'
    ]);
    exit;
}
if ($maxEnd > 24 * 60) {
    echo json_encode([
        'success' => false,
        'error'   => 'Jam berakhir maksimal pukul 24:00.'
    ]);
    exit;
}

// Hitung total durasi dari semua sesi (2 jam per sesi)
$totalMinutes = 0;
foreach ($sessions as $s) {
    $totalMinutes += ($s['end'] - $s['start']);
}
$duration = (int)round($totalMinutes / 60); // 2 sesi = 4 jam, 3 sesi = 6 jam

// siapa yang melakukan aksi (kalau nanti pakai session admin/user bisa diganti)
$action_by = 'user'; // atau $_SESSION['username'] ?? 'user';

try {

    // === Mulai transaksi: reservations + reservations_history ===
    $pdo->beginTransaction();

    // 1x INSERT untuk ruangan + buku + WA
    $sql = "
        INSERT INTO reservations
            (user_id, book_id, notes, room, people, date, time, duration, whatsapp, created_at)
        VALUES
            (:user_id, :book_id, :notes, :room, :people, :date, :time, :duration, :whatsapp, NOW())
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id',  $user_id,     PDO::PARAM_STR);
    $stmt->bindParam(':book_id',  $book_id,     PDO::PARAM_INT);
    $stmt->bindParam(':notes',    $notes,       PDO::PARAM_STR);
    $stmt->bindParam(':room',     $room,        PDO::PARAM_STR);
    $stmt->bindParam(':people',   $people,      PDO::PARAM_INT);
    $stmt->bindParam(':date',     $date,        PDO::PARAM_STR);
    $stmt->bindParam(':time',     $sessionsRaw, PDO::PARAM_STR); // simpan string sesi mentah
    $stmt->bindParam(':duration', $duration,    PDO::PARAM_INT);
    $stmt->bindParam(':whatsapp', $wa,          PDO::PARAM_STR);

    $stmt->execute();

    // ambil id reservation yang baru
    $reservation_id = $pdo->lastInsertId();

    // === INSERT ke reservations_history ===
    $sqlHist = "
        INSERT INTO reservations_history
            (reservation_id, user_id, whatsapp, book_id, notes, room, people, date, time, duration, created_at, action, action_by)
        VALUES
            (:reservation_id, :user_id, :whatsapp, :book_id, :notes, :room, :people, :date, :time, :duration, NOW(), :action, :action_by)
    ";

    $stmtHist = $pdo->prepare($sqlHist);
    $action   = 'created';

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

    // semua OK
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi ruangan + buku berhasil disimpan dan history tercatat!'
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'error'   => 'Database error: ' . $e->getMessage()
    ]);
}
